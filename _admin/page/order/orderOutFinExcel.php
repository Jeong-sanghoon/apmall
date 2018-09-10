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
		AND C.STATUS = 'G' AND A.GOODSCF_YN <> 'Y'
	";
	array_push($arParam, $cal_1 .' 00:00:00');
	array_push($arParam, $cal_2 .' 23:59:59');


	if($deliveryid != ''){
		$qryWhere .= " AND A.DELIVERYID like ?";
		array_push($arParam, '%'. $orderid .'%');
	}
	
	$qry = "
		SELECT A.`DELIVERY_SEQ`, A.`DELIVERYID`, A.REG_DT, A.WEIGHT, A.W_PRICE, A.MEMO, A.W_PRICE_P, A.W_PRICE_P_VND, A.COD, A.COD_VND
		, COUNT(B.`DELIVERY_ITEM_SEQ`) AS CNT, SUM(C.`SUMPRICE`) AS SUMPRICE, MAX(C.GOODSSC_DT) AS GOODSSC_DT
		, SUM(C.SUMPRICE_VND) AS SUMPRICE_VND, SUM(C.DEPOSITFEE) AS DEPOSITFEE, SUM(C.DEPOSITFEE_VND) AS DEPOSITFEE_VND
		FROM TB_DELIVERY A
		INNER JOIN TB_DELIVERY_ITEM B ON B.`DELIVERY_SEQ` = A.`DELIVERY_SEQ`
		INNER JOIN TB_ORDER_ITEM C ON C.ITEM_SEQ = B.`ITEM_SEQ`
		". $qryWhere ."
		GROUP BY A.`DELIVERY_SEQ`, A.`DELIVERYID`, A.REG_DT, A.WEIGHT, A.W_PRICE, A.MEMO, A.W_PRICE_P, A.W_PRICE_P_VND, A.COD, A.COD_VND
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
	foreach(range('A', 'Z') as $cID){
		$sheet->getColumnDimension($cID)->setWidth(15);
	}
	
	// 글꼴
	$sheet->getDefaultStyle()->getFont()->setName('맑은 고딕');
	$objPHPExcel->setActiveSheetIndex(0);
	
	// 제목
	$sheet->setCellValue('A1', '출고번호');
	$sheet->setCellValue('B1', '출고완료일');
	$sheet->setCellValue('C1', '아이템개수');
	$sheet->setCellValue('D1', '무게');
	$sheet->setCellValue('E1', '배송사운송비');
	$sheet->setCellValue('F1', '고객운송비');
	$sheet->setCellValue('G1', '예상COD');
	
	
	foreach($rsList as $i=>$ds){
		$row = $i + 2;
		
		$sheet->getStyle('B'.$row)->getAlignment()->setWrapText(true);
		$sheet->getStyle('F'.$row)->getAlignment()->setWrapText(true);
		$sheet->getStyle('G'.$row)->getAlignment()->setWrapText(true);
		
		$sheet->getStyle('B'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle('C'.$row . ':G'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		
		$ds['GOODSSC_DT_STR'] = str_replace(' ', chr(10), $ds['GOODSSC_DT']);
		
		$sheet->setCellValue('A'.$row, $ds['DELIVERYID']);
		$sheet->setCellValue('B'.$row, $ds['GOODSSC_DT_STR']);
		$sheet->setCellValue('C'.$row, number_format($ds['CNT']));
		$sheet->setCellValue('D'.$row, number_format($ds['WEIGHT'], 2, ',', ','));
		$sheet->setCellValue('E'.$row, number_format($ds['W_PRICE']));
		$sheet->setCellValue('F'.$row, number_format($ds['W_PRICE_P']) .'원'. chr(10) .'('. number_format($ds['W_PRICE_P_VND']) .'₫)');
		$sheet->setCellValue('G'.$row, number_format($ds['COD']) .'원'. chr(10) .'('. number_format($ds['COD_VND']) .'₫)');
	}
	
	//다운
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename=order_out_fin_'. date('YmdHis') .'.xlsx');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
?>