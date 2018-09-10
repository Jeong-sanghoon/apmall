<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_lib/PHPExcel.php";

	chkSession($url = '/_admin/');

	// ===================================================
	// get parameter
	// ===================================================
	$cal_1 = $cFnc->getReq('cal_1', date('Y-m-d', strtotime('-3 month')));
	$cal_2 = $cFnc->getReq('cal_2', date('Y-m-d'));
	$orderid = $cFnc->getReq('orderid', '');
	$name = $cFnc->getReq('name', '');
	$order_cont = $cFnc->getReq('order_cont', 'B.ITEM_SEQ');
	$order_asc = $cFnc->getReq('order_asc', 'DESC');
	$search_list1 = $cFnc->getReq('search_list1', '');

	//=====================================================
	//== 도움말 - Set Variables
	//=====================================================
	$cPdo = new cPdo($ARR_DB_INFO, true);
	
	//=====================================================
	//== 도움말 - Start Tran
	//=====================================================
	$arParam = Array();
	$qryOrder = "ORDER BY ". $order_cont ." ". $order_asc;
	$qryWhere = "
		WHERE A.REG_DT BETWEEN ? AND ?
		AND B.STATUS = 'B'
		AND A.CANCEL_YN <> 'Y'
	";
	array_push($arParam, $cal_1 .' 00:00:00');
	array_push($arParam, $cal_2 .' 23:59:59');


	if($orderid != ''){
		$qryWhere .= " AND A.ORDERID like ?";
		array_push($arParam, '%'. $orderid .'%');		
	}
	
	if($name != ''){
		$qryWhere .= " AND A.NAME like ?";
		array_push($arParam, '%'. $name .'%');		
	}
	
	$qry = "
		SELECT A.ORDER_SEQ, A.ORDERID, A.NAME, A.PRICE_VND AS PRICE_VND_ORD, A.DEPOSITFEE_VND AS DEPOSITFEE_VND_ORD
		, B.ITEM_SEQ, B.ITEMID, B.`PRODUCTNAME`, B.REG_DT, B.QTY, B.PRICE, B.SUMPRICE, B.DEPOSITFEE, B.DEPOSIT_DT, B.`STATUS`
		, B.READY_DT, B.WEARRD_DT, B.WEARSC_DT, B.GOODSRD_DT, B.GOODSSC_DT, B.GOODSCF_DT, B.OPTFIELD, B.OPTVALUE
		, B.PRICE_VND, B.SUMPRICE_VND, B.DEPOSITFEE_VND
		FROM TB_ORDER A
		INNER JOIN TB_ORDER_ITEM B ON B.ORDER_SEQ = A.`ORDER_SEQ`
		". $qryWhere ."
		". $qryOrder ."
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('list', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$rsList = $result['data'];
	//echo json_encode($rsList);exit;
	
	
	$objPHPExcel = new PHPExcel();
	$sheet = $objPHPExcel->getActiveSheet();
	
	// 셀넓이
	foreach(range('A', 'K') as $cID){
		$objPHPExcel->getActiveSheet()->getColumnDimension($cID)->setWidth(15);
	}
	
	// 글꼴
	$sheet->getDefaultStyle()->getFont()->setName('맑은 고딕');
	$objPHPExcel->setActiveSheetIndex(0);
	
	// 제목
	$sheet->setCellValue('A1', '주문번호');
	$sheet->setCellValue('B1', '아이템번호');
	$sheet->setCellValue('C1', '주문상품');
	$sheet->setCellValue('D1', '옵션필드(값)');
	$sheet->setCellValue('E1', '등록일');
	$sheet->setCellValue('F1', '주문자');
	$sheet->setCellValue('G1', '수량');
	$sheet->setCellValue('H1', '합계금액');
	$sheet->setCellValue('I1', '디파짓피');
	$sheet->setCellValue('J1', '디파짓피(VND)');
	$sheet->setCellValue('K1', '결제확인일');
	
	
	foreach($rsList as $i=>$ds){
		$row = $i + 2;
		
		$objPHPExcel->getActiveSheet()->getStyle('D'.$row)->getAlignment()->setWrapText(true);
		$objPHPExcel->getActiveSheet()->getStyle('E'.$row)->getAlignment()->setWrapText(true);
		$objPHPExcel->getActiveSheet()->getStyle('K'.$row)->getAlignment()->setWrapText(true);
		$ds['REG_DT_STR'] = str_replace(' ', chr(10), $ds['REG_DT']);
		$ds['DEPOSIT_DT_STR'] = str_replace(' ', chr(10), $ds['DEPOSIT_DT']);
		
		$sheet->setCellValue('A'.$row, $ds['ORDERID']);
		$sheet->setCellValue('B'.$row, $ds['ITEMID']);
		$sheet->setCellValue('C'.$row, $ds['PRODUCTNAME']);
		$sheet->setCellValue('D'.$row, $ds['OPTFIELD'] . chr(10) .'('. $ds['OPTVALUE'] .')');
		$sheet->setCellValue('E'.$row, $ds['REG_DT_STR']);
		$sheet->setCellValue('F'.$row, $ds['NAME']);
		$sheet->setCellValue('G'.$row, number_format($ds['QTY']));
		$sheet->setCellValue('H'.$row, number_format($ds['SUMPRICE']));
		$sheet->setCellValue('I'.$row, number_format($ds['DEPOSITFEE']));
		$sheet->setCellValue('J'.$row, number_format($ds['DEPOSITFEE_VND']));
		$sheet->setCellValue('K'.$row, $ds['DEPOSIT_DT_STR']);
	}
	
	//다운
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename=order_pay_'. date('YmdHis') .'.xlsx');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
?>