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
	$seq		= $cFnc->getReq('seq');
	
	$ADM_ID			= $cFnc->getReq('ADM_ID');
	$ADM_PW			= $cFnc->getReq('ADM_PW');
	$ADM_NM			= $cFnc->getReq('ADM_NM');
	$SYSADMIN_YN	= $cFnc->getReq('SYSADMIN_YN');
	$USE_YN			= $cFnc->getReq('USE_YN');
	//$cFnc->echoArr($_REQUEST); exit;
	
	// =====================================================
	// Set Variables
	// =====================================================
	$result = array();
	$result['status'] = 1;
	$result['msg'] = "";
	$result['url'] = "";
	
	$strUpPath = UPLOAD_DIR ."/board";
	$strUpPathLoc = date('Ym');
	$strUpPathFull = $strUpPath ."/". $strUpPathLoc;
	$arrThumb = array();		// 썸네일 이미지 생성 array(가로크기, 세로크기)
	$bExtCheck = false;			// 파일 확장자 체크 여부
	
	$cPdo = new cPdo($ARR_DB_INFO);
	$oFile = new cFile($strUpPathFull, $arrThumb, $bExtCheck, 0, 0);
	
	$now_dt = date('Y-m-d H:i:s');
	
	// =====================================================
	// Start Tran
	// =====================================================
	try{
		if($pageaction == 'INSERT'){
			$result['msg'] = "입력이 완료되었습니다";
			$result['url'] = "adminList.php";
			
			$arParam = array();
			array_push($arParam, $ADM_ID);
			array_push($arParam, $ADM_PW);
			array_push($arParam, $ADM_NM);
			array_push($arParam, $SYSADMIN_YN);
			array_push($arParam, $USE_YN);
			array_push($arParam, $now_dt);
			$qry = "
				INSERT INTO TMP_ADM_BACK (ADM_ID, ADM_PW, ADM_NM, SYSADMIN_YN, USE_YN, REG_DT)
				VALUES (
					?
					, ?
					, ?
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
			if($seq == '') throw new Exception('잘못된 접근입니다');
			
			$result['msg'] = "수정이 완료되었습니다";
			$result['url'] = "adminView.php";
			
			$arParam = array();
			array_push($arParam, $ADM_NM);
			array_push($arParam, $SYSADMIN_YN);
			array_push($arParam, $USE_YN);
			array_push($arParam, $now_dt);
			
			if($ADM_PW != ''){
				$qryAdd .= ", ADM_PW = ?";
				array_push($arParam, $ADM_PW);
			}
			
			array_push($arParam, $seq);
			$qry = "
				UPDATE TMP_ADM_BACK SET
				ADM_NM
				, SYSADMIN_YN
				, USE_YN
				, MOD_DT
				". $qryAdd ."
				WHERE SEQ = ?
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result['status'] = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception('수정중 오류가 발생했습니다');
		}
		else if($pageaction == 'DELETE'){
			if($seq == '') throw new Exception('잘못된 접근입니다');
			
			$result['msg'] = "삭제되었습니다";
			$result['url'] = "adminList.php";
			
			$arParam = array();
			array_push($arParam, $seq);
			$qry = "DELETE FROM TMP_ADM_BACK WHERE SEQ = ?";
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
