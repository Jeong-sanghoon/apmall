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
	$order_cont = $cFnc->getReq('order_cont', 'A.ORDER_SEQ');
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
		AND B.STATUS = 'A'
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
		SELECT A.ORDER_SEQ, A.ORDERID, A.REG_DT, A.NAME, A.QTY, A.PRICE, A.TEL, A.PRICE_VND, A.DEPOSITFEE_VND
		, B.`PRODUCTNAME`, B.`STATUS`
		FROM TB_ORDER A
		INNER JOIN TB_ORDER_ITEM B ON B.ORDER_SEQ = A.`ORDER_SEQ`
		". $qryWhere ."
		GROUP BY A.ORDER_SEQ, A.ORDERID, A.REG_DT, A.NAME, A.QTY, A.PRICE, A.TEL
		". $qryOrder ."
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('list', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$rsList = $result['data'];
	
	
	$objPHPExcel = new PHPExcel();
	$sheet = $objPHPExcel->getActiveSheet();
	
	// 셀넓이
	foreach(range('A', 'I') as $cID){
		$objPHPExcel->getActiveSheet()->getColumnDimension($cID)->setWidth(15);
	}
	
	// 글꼴
	$sheet->getDefaultStyle()->getFont()->setName('맑은 고딕');
	$objPHPExcel->setActiveSheetIndex(0);
	
	// 제목
	$sheet->setCellValue('A1', '주문번호');
	$sheet->setCellValue('B1', '주문상품');
	$sheet->setCellValue('C1', '등록일');
	$sheet->setCellValue('D1', '주문자');
	$sheet->setCellValue('E1', '연락처');
	$sheet->setCellValue('F1', '수량');
	$sheet->setCellValue('G1', '합계금액');
	$sheet->setCellValue('H1', '디파짓피');
	$sheet->setCellValue('I1', '디파짓피(VND)');
	
	
	foreach($rsList as $i=>$ds){
		$row = $i + 2;
		
		$objPHPExcel->getActiveSheet()->getStyle('C'.$row)->getAlignment()->setWrapText(true);
		$ds['REG_DT_STR'] = str_replace(' ', chr(10), $ds['REG_DT']);
		
		$sheet->setCellValue('A'.$row, $ds['ORDERID']);
		$sheet->setCellValue('B'.$row, $ds['PRODUCTNAME']);
		$sheet->setCellValue('C'.$row, $ds['REG_DT_STR']);
		$sheet->setCellValue('D'.$row, $ds['NAME']);		
		$sheet->setCellValue('E'.$row, $cFnc->MaskingTelNo($ds['TEL']));
		$sheet->setCellValue('F'.$row, number_format($ds['QTY']));
		$sheet->setCellValue('G'.$row, number_format($ds['PRICE']));
		$sheet->setCellValue('H'.$row, number_format($ds['DEPOSITFEE']));
		$sheet->setCellValue('I'.$row, number_format($ds['DEPOSITFEE_VND']));
	}
	
	//다운
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename=order_accept_'. date('YmdHis') .'.xlsx');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
?>