<?
	// ===================================================
	// include
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSessionAjax($url = '/_admin/');
	
	// ===================================================
	// get parameter
	// ===================================================
	$pageaction = $cFnc->getReq('pageaction');
	$hscode_seq	= $cFnc->getReq('hscode_seq');
	
	$hscode_nm	= $cFnc->getReq('hscode_nm');
	$hscode_value = $cFnc->getReq('hscode_value');
	$use_yn = $cFnc->getReq('use_yn');
	//$cFnc->echoArr($_REQUEST); exit;
	
	// =====================================================
	// Set Variables
	// =====================================================
	$result = array();
	$result['status'] = 1;
	$result['msg'] = "";
	$result['url'] = "";
	
	/*
	$strUpPath = UPLOAD_DIR ."/board";
	$strUpPathLoc = date('Ym');
	$strUpPathFull = $strUpPath ."/". $strUpPathLoc;
	$arrThumb = array();		// 썸네일 이미지 생성 array(가로크기, 세로크기)
	$bExtCheck = false;			// 파일 확장자 체크 여부
	
	
	$oFile = new cFile($strUpPathFull, $arrThumb, $bExtCheck, 0, 0);
	*/

	$cPdo = new cPdo($ARR_DB_INFO);
	$now_dt = date('Y-m-d H:i:s');
	
	// =====================================================
	// Start Tran
	// =====================================================
	try{
		if($pageaction == 'INSERT'){
			$result['msg'] = "입력이 완료되었습니다";
			$result['url'] = "hscodeList.php";
			
			$arParam = array();
			array_push($arParam, $hscode_nm);
			array_push($arParam, $hscode_value);
			array_push($arParam, $use_yn);
			array_push($arParam, $now_dt);
			$qry = "
				INSERT INTO TB_HSCODE (HSCODE_NM, HSCODE_VALUE, USE_YN, REG_DT)
				VALUES (
					?
					, ?
					, ?					
					, ?
				)
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result['status'] = $cPdo->execQuery('insert', $qry, $arParam);
			if(!$result['status'])	throw new Exception('입력중 오류가 발생했습니다');
		}
		else if($pageaction == 'UPDATE'){
			if($hscode_seq == '') throw new Exception('잘못된 접근입니다');
			
			$result['msg'] = "수정이 완료되었습니다";
			$result['url'] = "hscodeView.php";
			
			$arParam = array();
			array_push($arParam, $hscode_nm);
			array_push($arParam, $hscode_value);
			array_push($arParam, $use_yn);
			array_push($arParam, $now_dt);
			array_push($arParam, $hscode_seq);
			$qry = "
				UPDATE TB_HSCODE SET
				HSCODE_NM = ?
				, HSCODE_VALUE = ?
				, USE_YN = ?
				, MOD_DT = ?
				WHERE HSCODE_SEQ = ?
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result['status'] = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception('수정중 오류가 발생했습니다');
		}
		else if($pageaction == 'DELETE'){
			if($hscode_seq == '') throw new Exception('잘못된 접근입니다');
			
			$result['msg'] = "삭제되었습니다";
			$result['url'] = "hscodeList.php";
			
			$arParam = array();
			$qry = "DELETE FROM TB_HSCODE WHERE HSCODE_SEQ IN (". $hscode_seq .")";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result['status'] = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception('삭제중 오류가 발생했습니다');
		}
	}
	catch(Exception $e){
		$result['status'] = 0;
		$result['msg'] = $e->getMessage();
		if($e->getCode() != '')	$result['msg'] .= ' // '. $e->getCode();
		$result['url'] = "";
	}
	
	echo json_encode($result);
	$cPdo->close();
?>
