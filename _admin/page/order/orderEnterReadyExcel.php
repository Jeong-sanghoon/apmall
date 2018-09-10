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
		AND B.STATUS = 'D'
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
		SELECT A.ORDER_SEQ, A.ORDERID, A.NAME
		, B.ITEM_SEQ, B.ITEMID, B.`PRODUCTNAME`, B.REG_DT, B.QTY, B.PRICE, B.SUMPRICE, B.DEPOSIT_DT, B.`STATUS`
		, B.READY_DT, B.WEARRD_DT, B.WEARSC_DT, B.GOODSRD_DT, B.GOODSSC_DT, B.GOODSCF_DT, B.OPTFIELD, B.OPTVALUE
		, B.BARCODE_39_URL, B.BARCODE_128_URL
		, SUM(C.P_QTY) AS P_QTY, SUM(C.P_PRICE) AS P_PRICE, SUM(C.P_DELIVERYFEE) AS P_DELIVERYFEE, SUM(C.P_PRICESUM) AS P_PRICESUM
		FROM TB_ORDER A
		INNER JOIN TB_ORDER_ITEM B ON B.ORDER_SEQ = A.`ORDER_SEQ`
		INNER JOIN TB_ORDER_PURCHASE C ON C.ITEM_SEQ = B.ITEM_SEQ
		". $qryWhere ."
		GROUP BY A.ORDER_SEQ, A.ORDERID, A.NAME
		, B.ITEM_SEQ, B.ITEMID, B.`PRODUCTNAME`, B.REG_DT, B.QTY, B.PRICE, B.SUMPRICE, B.DEPOSIT_DT, B.`STATUS`
		, B.READY_DT, B.WEARRD_DT, B.WEARSC_DT, B.GOODSRD_DT, B.GOODSSC_DT, B.GOODSCF_DT, B.OPTFIELD, B.OPTVALUE
		, B.BARCODE_39_URL, B.BARCODE_128_URL
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
	foreach(range('A', 'J') as $cID){
		$objPHPExcel->getActiveSheet()->getColumnDimension($cID)->setWidth(15);
	}
	
	// 글꼴
	$sheet->getDefaultStyle()->getFont()->setName('맑은 고딕');
	$objPHPExcel->setActiveSheetIndex(0);
	
	// 제목
	$sheet->setCellValue('A1', '주문번호');
	$sheet->setCellValue('B1', '아이템번호');
	$sheet->setCellValue('C1', '주문상품');
	$sheet->setCellValue('D1', '주문자');
	$sheet->setCellValue('E1', '구매수량');
	$sheet->setCellValue('F1', '합계');
	$sheet->setCellValue('G1', '등록일');
	$sheet->setCellValue('H1', '입고대기일');
	
	
	foreach($rsList as $i=>$ds){
		$row = $i + 2;
		
		$objPHPExcel->getActiveSheet()->getStyle('G'.$row)->getAlignment()->setWrapText(true);
		$objPHPExcel->getActiveSheet()->getStyle('H'.$row)->getAlignment()->setWrapText(true);
		$ds['REG_DT_STR'] = str_replace(' ', chr(10), $ds['REG_DT']);
		$ds['WEARRD_DT_STR'] = str_replace(' ', chr(10), $ds['WEARRD_DT']);
		
		$sheet->setCellValue('A'.$row, $ds['ORDERID']);
		$sheet->setCellValue('B'.$row, $ds['ITEMID']);
		$sheet->setCellValue('C'.$row, $ds['PRODUCTNAME']);
		$sheet->setCellValue('D'.$row, $ds['NAME']);
		$sheet->setCellValue('E'.$row, number_format($ds['P_QTY']));
		$sheet->setCellValue('F'.$row, number_format($ds['P_PRICESUM']));
		$sheet->setCellValue('G'.$row, $ds['REG_DT_STR']);
		$sheet->setCellValue('H'.$row, $ds['WEARRD_DT_STR']);
	}
	
	//다운
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename=order_enter_ready_'. date('YmdHis') .'.xlsx');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
?>