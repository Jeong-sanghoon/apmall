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
	$user_seq  = $cFnc->getReq('user_seq', '');
	$use_yn = $cFnc->getReq('use_yn', '');
	$user_id = $cFnc->getReq('user_id', '');
	$user_nm = $cFnc->getReq('user_nm', '');
	$email = $cFnc->getReq('email', '');
	$order_cont = $cFnc->getReq('order_cont', 'USER_SEQ');
	$order_asc = $cFnc->getReq('order_asc', 'DESC');
	$search_list1 = $cFnc->getReq('search_list1', '');
	

	//=====================================================
	//== 도움말 - Set Variables
	//=====================================================
	$cPdo = new cPdo($ARR_DB_INFO, true);
	
	// =====================================================
	// Start Tran
	// =====================================================
	$qryWhere = "";
	$qryOrder = "";
	
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
		$qryWhere .= " AND USERID like ?";
		array_push($arParam, '%'. $user_id .'%');		
	}

	if($use_yn != ''){
		$qryWhere .= " AND A.USE_YN = ?";
		array_push($arParam, $use_yn);
	}

	$qry = "
		SELECT USER_ID, USER_SEQ, USER_NM, TEL, REG_DT, MOD_DT, USE_YN, LAST_ACC_DT, CODE_SEQ, JOIN_TP, SNS_KEY, ADDR 
		, DATE_FORMAT(REG_DT, '%Y-%m-%d') AS REG_DT_STR
		, DATE_FORMAT(MOD_DT, '%Y-%m-%d') AS MOD_DT_STR
		FROM TB_USER				
		". $qryWhere ."
		". $qryOrder ."
	";
	$result = $cPdo->execQuery('list', $qry, $arParam);
	$rsList = $result['data'];
	//$cFnc->echoQry($qry, $arParam);
	
	
	$objPHPExcel = new PHPExcel();
	$sheet = $objPHPExcel->getActiveSheet();
	
	// 셀넓이
	foreach(range('A', 'F') as $cID){
		$objPHPExcel->getActiveSheet()->getColumnDimension($cID)->setWidth(15);
	}
	
	// 글꼴
	$sheet->getDefaultStyle()->getFont()->setName('맑은 고딕');
	$objPHPExcel->setActiveSheetIndex(0);
	
	// 제목
	$sheet->setCellValue('A1', '아이디');	
	$sheet->setCellValue('B1', '가입유형');
	$sheet->setCellValue('C1', '이름');
	$sheet->setCellValue('D1', '연락처');
	$sheet->setCellValue('E1', '등록일');	
	$sheet->setCellValue('F1', '사용여부');	
	
	
	foreach($rsList as $i=>$ds){
		$row = $i + 2;
		
		$sheet->setCellValue('A'.$row, $ds['USER_ID']);
		$sheet->setCellValue('B'.$row, $ds['JOIN_TP']);
		$sheet->setCellValue('C'.$row, $ds['USER_NM']);
		$sheet->setCellValue('D'.$row, $ds['TEL']);		
		$sheet->setCellValue('E'.$row, $ds['REG_DT']);
		$sheet->setCellValue('F'.$row, CODE_USE_YN($ds['USE_YN']));
	}
	
	//다운
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename=member_list_'. date('YmdHis') .'.xlsx');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
?>