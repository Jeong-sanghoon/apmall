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
	$adm_seq  = $cFnc->getReq('adm_seq', '');
	$use_yn = $cFnc->getReq('use_yn', '');
	$adm_id = $cFnc->getReq('adm_id', '');
	$adm_nm = $cFnc->getReq('adm_nm', '');
	$email = $cFnc->getReq('email', '');
	$order_cont = $cFnc->getReq('order_cont', 'A.ADM_SEQ');
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
	$qryWhere = "WHERE A.REG_DT BETWEEN ? AND ?";
	array_push($arParam, $cal_1 .' 00:00:00');
	array_push($arParam, $cal_2 .' 23:59:59');


	if($adm_id != ''){
		$qryWhere .= " AND A.ADM_ID like ?";
		array_push($arParam, '%'. $adm_id .'%');		
	}

	if($adm_nm != ''){
		$qryWhere .= " AND A.ADM_NM like ?";
		array_push($arParam, '%'. $adm_nm .'%');		
	}

	if($email != ''){
		$qryWhere .= " AND A.EMAIL like ?";
		array_push($arParam, '%'. $email .'%');		
	}
	
	if($use_yn != ''){
		$qryWhere .= " AND A.USE_YN = ?";
		array_push($arParam, $use_yn);
	}
	
	$qry = "
		SELECT A.ADM_SEQ, A.ADM_ID, A.ADM_PW, A.REG_DT, A.MOD_DT, A.GRADE, A.SYSTEM_CD, A.ADM_NM, A.TEL, A.EMAIL, A.USE_YN
		, B.SYSTEM_NM
		, DATE_FORMAT(A.REG_DT, '%Y-%m-%d') AS REG_DT_STR
		, DATE_FORMAT(A.MOD_DT, '%Y-%m-%d') AS REG_DT_STR		
		, CASE WHEN A.USE_YN = 'Y' THEN '사용' ELSE '미사용' END AS USE_YN_STR
		, CASE WHEN A.GRADE = 'S' THEN '시스템관리자' WHEN A.GRADE = 'A' THEN '관리자' ELSE '일반관리자' END AS GRADE_STR
		FROM TB_ADM A
		INNER JOIN TB_SYSTEM B ON A.SYSTEM_CD = B.SYSTEM_CD
		". $qryWhere ."
		". $qryOrder ."
	";
	$result = $cPdo->execQuery('list', $qry, $arParam);
	$rsList = $result['data'];
	//$cFnc->echoQry($qry, $arParam);
	
	
	$objPHPExcel = new PHPExcel();
	$sheet = $objPHPExcel->getActiveSheet();
	
	// 셀넓이
	foreach(range('A', 'G') as $cID){
		$objPHPExcel->getActiveSheet()->getColumnDimension($cID)->setWidth(15);
	}
	
	// 글꼴
	$sheet->getDefaultStyle()->getFont()->setName('맑은 고딕');
	$objPHPExcel->setActiveSheetIndex(0);
	
	// 제목
	$sheet->setCellValue('A1', '아이디');
	$sheet->setCellValue('B1', '이름');
	$sheet->setCellValue('C1', '이메일');
	$sheet->setCellValue('D1', '연락처');
	$sheet->setCellValue('E1', '등록일');
	$sheet->setCellValue('F1', '등급');
	$sheet->setCellValue('G1', '사용여부');	
	
	
	foreach($rsList as $i=>$ds){
		$row = $i + 2;
		
		$sheet->setCellValue('A'.$row, $ds['ADM_ID']);
		$sheet->setCellValue('B'.$row, $ds['ADM_NM']);
		$sheet->setCellValue('C'.$row, $ds['EMAIL']);
		$sheet->setCellValue('D'.$row, $ds['TEL']);		
		$sheet->setCellValue('E'.$row, $ds['REG_DT']);
		$sheet->setCellValue('F'.$row, CODE_ADM_GRADE($ds['GRADE']));
		$sheet->setCellValue('G'.$row, CODE_USE_YN($ds['USE_YN']));
	}
	
	//다운
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename=admin_list_'. date('YmdHis') .'.xlsx');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
?>