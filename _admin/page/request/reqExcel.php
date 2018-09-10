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
	$user_id = $cFnc->getReq('user_id', '');
	$user_nm = $cFnc->getReq('user_nm', '');
	$email = $cFnc->getReq('email', '');
	$status = $cFnc->getReq('status', 'R');
	$order_cont = $cFnc->getReq('order_cont', 'USERMSTID');
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
	$qryWhere = "WHERE REG_DT BETWEEN ? AND ?";
	array_push($arParam, $cal_1 .' 00:00:00');
	array_push($arParam, $cal_2 .' 23:59:59');


	if($user_nm != ''){
		$qryWhere .= " AND USER_NM like ?";
		array_push($arParam, '%'. $user_nm .'%');		
	}

	if($user_id != ''){
		$qryWhere .= " AND USER_ID like ?";
		array_push($arParam, '%'. $user_id .'%');		
	}

	if($email != ''){
		$qryWhere .= " AND EMAIL like ?";
		array_push($arParam, '%'. $email .'%');		
	}
	
	if($status != ''){
		$qryWhere .= " AND STATUS = ?";
		array_push($arParam, $status);
	}
	
	$qry = "
		SELECT USERMSTID, USERORDID, REG_DT, `NAME`, EMAIL, TEL, DST_ADDR, MEMO, QTY, PRICESUM, DEPOSITFEE, MOD_DT, `STATUS`, USER_ID, USER_SEQ
		, (SELECT ITEM_NM FROM TB_ORDER_REQITEM WHERE USERMSTID = TB_ORDER_REQ.USERMSTID ORDER BY USERITEMID DESC LIMIT 1) AS ITEM_NM
		FROM TB_ORDER_REQ
		". $qryWhere ."
		". $qryOrder ."
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('list', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$rsList = $result['data'];
	
	
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
	$sheet->setCellValue('B1', '아이디');
	$sheet->setCellValue('C1', '이름');
	$sheet->setCellValue('D1', '이메일');
	$sheet->setCellValue('E1', '연락처');
	$sheet->setCellValue('F1', '수량');
	$sheet->setCellValue('G1', '구매품목');
	$sheet->setCellValue('H1', '금액');	
	$sheet->setCellValue('I1', '신청일');	
	$sheet->setCellValue('J1', '처리상태');	
	
	
	foreach($rsList as $i=>$ds){
		$row = $i + 2;
		
		$objPHPExcel->getActiveSheet()->getStyle('I'.$row)->getAlignment()->setWrapText(true);
		$ds['REG_DT_STR'] = str_replace(' ', chr(10), $ds['REG_DT']);
		
		$sheet->setCellValue('A'.$row, $ds['USERORDID']);
		$sheet->setCellValue('B'.$row, $ds['USER_ID']);
		$sheet->setCellValue('C'.$row, $ds['NAME']);
		$sheet->setCellValue('D'.$row, $ds['EMAIL']);		
		$sheet->setCellValue('E'.$row, $cFnc->MaskingTelNo($ds['TEL']));
		$sheet->setCellValue('F'.$row, number_format($ds['QTY']));
		$sheet->setCellValue('G'.$row, $ds['ITEM_NM']);
		$sheet->setCellValue('H'.$row, number_format($ds['PRICESUM']));
		$sheet->setCellValue('I'.$row, $ds['REG_DT_STR']);
		$sheet->setCellValue('J'.$row, CODE_REQ_STATUS($ds['STATUS']));
	}
	
	//다운
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename=req_list_'. date('YmdHis') .'.xlsx');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
?>