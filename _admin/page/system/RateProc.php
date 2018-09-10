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
	$rate_seq	= $cFnc->getReq('rate_seq');
	
	$RATE_SEQ 	= $cFnc->getReq("RATE_SEQ");
	$KRW 		= $cFnc->getReq("KRW");
	$FCR 		= $cFnc->getReq("FCR");
	$REG_DT 	= $cFnc->getReq("REG_DT");
	$COUNTRY_NM = $cFnc->getReq("COUNTRY_NM");
	$ADM_SEQ 	= $cFnc->getReq("ADM_SEQ");

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
			$arParam = array();			
			array_push( $arParam, $KRW);
			array_push( $arParam, $FCR);			
			array_push( $arParam, $COUNTRY_NM);
			array_push( $arParam, $S_SEQ);
			
			$qry = "
				INSERT INTO TB_RATE ( KRW, FCR, REG_DT, COUNTRY_NM, ADM_SEQ )
				VALUES (
				?
				,?
				,now()
				,?
				,?				
				)
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('insert', $qry, $arParam);
			if(!$result['status'])	throw new Exception('입력중 오류가 발생했습니다');
			
			$result['msg'] = "입력이 완료되었습니다";
			$result['url'] = "rateList.php";
		}
		else if($pageaction == 'UPDATE'){
			if($rate_seq == '') throw new Exception('잘못된 접근입니다');
			
			$arParam = array();
			array_push( $arParam, $KRW);
			array_push( $arParam, $FCR);			
			array_push( $arParam, $COUNTRY_NM);
			array_push( $arParam, $S_SEQ);

			array_push($arParam, $rate_seq);
			$qry = "
				UPDATE TB_RATE SET
				KRW = ?
				, FCR = ?				
				, COUNTRY_NM = ?
				, ADM_SEQ = ?
				WHERE RATE_SEQ = ?
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception('수정중 오류가 발생했습니다');
			
			$result['msg'] = "수정이 완료되었습니다";
			$result['url'] = "rateView.php";
		}
		else if($pageaction == 'DELETE'){
			if($rate_seq == '') throw new Exception('잘못된 접근입니다');
			
			$arParam = array();
			array_push($arParam, $rate_seq);
			$qry = "DELETE FROM TB_RATE WHERE RATE_SEQ = ?";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception('삭제중 오류가 발생했습니다');
			
			$result['msg'] = "삭제되었습니다";
			$result['url'] = "rateList.php";
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
