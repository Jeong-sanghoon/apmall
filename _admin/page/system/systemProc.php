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
	$system_cd	= $cFnc->getReq('system_cd');
	
	$SYSTEM_CD	= $cFnc->getReq('SYSTEM_CD');
	$SYSTEM_NM	= $cFnc->getReq('SYSTEM_NM');
	$RATE		= $cFnc->getReq('RATE');	
	$USE_YN		= $cFnc->getReq('USE_YN');
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
	
	
	// =====================================================
	// Start Tran
	// =====================================================
	try{
		if($pageaction == 'INSERT'){
			$result['msg'] = "입력이 완료되었습니다";
			$result['url'] = "systemList.php";
			
			$arParam = array();
			array_push($arParam, $SYSTEM_CD);
			array_push($arParam, $SYSTEM_NM);
			array_push($arParam, $USE_YN);
			array_push($arParam, $RATE);			
			$qry = "
				INSERT INTO TB_SYSTEM (SYSTEM_CD, SYSTEM_NM, USE_YN, RATE, REG_DT)
				VALUES (
					?
					, ?
					, ?
					, ?					
					, now()
				)
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result['status'] = $cPdo->execQuery('insert', $qry, $arParam);
			if(!$result['status'])	throw new Exception('입력중 오류가 발생했습니다');
		}
		else if($pageaction == 'UPDATE'){
			if($system_cd == '') throw new Exception('잘못된 접근입니다');
			
			$result['msg'] = "수정이 완료되었습니다";
			$result['url'] = "systemView.php";
			
			$arParam = array();
			array_push($arParam, $SYSTEM_NM);
			array_push($arParam, $RATE);			
			array_push($arParam, $USE_YN);			
			

			array_push($arParam, $system_cd);
			$qry = "
				UPDATE TB_SYSTEM SET
				SYSTEM_NM = ?
				, RATE = ?
				, USE_YN = ?
				, MOD_DT = now()				
				WHERE SYSTEM_CD = ?
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result['status'] = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception('수정중 오류가 발생했습니다');
		}
		else if($pageaction == 'DELETE'){
			if($system_cd == '') throw new Exception('잘못된 접근입니다');
			
			$result['msg'] = "삭제되었습니다";
			$result['url'] = "systemList.php";
			
			$arParam = array();
			array_push($arParam, "N");
			array_push($arParam, $system_cd);
			$qry = "UPDATE TB_SYSTEM SET USE_YN = ?, MOD_DT = NOW() WHERE SYSTEM_CD = ?";
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
