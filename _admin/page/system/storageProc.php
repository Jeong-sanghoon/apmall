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
	$storage_seq= $cFnc->getReq('storage_seq');
	
	$STORAGE_SEQ 	= $cFnc->getReq("STORAGE_SEQ");
	$CATEGORY 		= $cFnc->getReq("CATEGORY");
	$CODE_SEQ 		= $cFnc->getReq("CODE_SEQ");
	$STORAGE_NM 	= $cFnc->getReq("STORAGE_NM");
	$ADDR 			= $cFnc->getReq("ADDR");
	$MANAGER_NM 	= $cFnc->getReq("MANAGER_NM");
	$TEL 			= $cFnc->getReq("TEL");		
	$USE_YN 		= $cFnc->getReq("USE_YN");
	$POST_NO 		= $cFnc->getReq("POST_NO");
	$ADDR_DETAIL 	= $cFnc->getReq("ADDR_DETAIL");
	$EMAIL 			= $cFnc->getReq("EMAIL");


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
			$result['url'] = "storageList.php";
			
			$arParam = array();						
			array_push( $arParam, $CATEGORY);
			array_push( $arParam, $CODE_SEQ);
			array_push( $arParam, $STORAGE_NM);
			array_push( $arParam, $ADDR);
			array_push( $arParam, $MANAGER_NM);
			array_push( $arParam, $TEL);			
			array_push( $arParam, $POST_NO);
			array_push( $arParam, $ADDR_DETAIL);
			array_push( $arParam, $EMAIL);
			
			$qry = "
				INSERT INTO TB_STORAGE ( CATEGORY, CODE_SEQ, STORAGE_NM, ADDR, MANAGER_NM, TEL, REG_DT, MOD_DT, USE_YN, POST_NO, ADDR_DETAIL, EMAIL )
				VALUES (
				?				
				,?
				,?
				,?
				,?
				,?
				,now()
				,now()
				,'Y'
				,?
				,?
				,?
				)				
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result['status'] = $cPdo->execQuery('insert', $qry, $arParam);
			if(!$result['status'])	throw new Exception('입력중 오류가 발생했습니다');
		}
		else if($pageaction == 'UPDATE'){
			if($storage_seq == '') throw new Exception('잘못된 접근입니다');
			
			$result['msg'] = "수정이 완료되었습니다";
			$result['url'] = "storageView.php";
			
			$arParam = array();			
			array_push( $arParam, $CATEGORY);
			array_push( $arParam, $CODE_SEQ);
			array_push( $arParam, $STORAGE_NM);
			array_push( $arParam, $ADDR);
			array_push( $arParam, $MANAGER_NM);
			array_push( $arParam, $TEL);						
			array_push( $arParam, $USE_YN);
			array_push( $arParam, $POST_NO);
			array_push( $arParam, $ADDR_DETAIL);
			array_push( $arParam, $EMAIL);

			array_push($arParam, $storage_seq);
			$qry = "
				UPDATE TB_STORAGE SET 				
				CATEGORY = ?
				, CODE_SEQ = ?
				, STORAGE_NM = ?
				, ADDR = ?
				, MANAGER_NM = ?
				, TEL = ?				
				, MOD_DT = now()
				, USE_YN = ?
				, POST_NO = ?
				, ADDR_DETAIL = ?
				, EMAIL = ?
				WHERE STORAGE_SEQ = ?
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result['status'] = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception('수정중 오류가 발생했습니다');
		}
		else if($pageaction == 'DELETE'){			
			if($storage_seq == '') throw new Exception('잘못된 접근입니다');
			
			$result['msg'] = "삭제되었습니다";
			$result['url'] = "storageList.php";
			
			$arParam = array();			
			array_push($arParam, $storage_seq);
			$qry = "DELETE FROM TB_STORAGE WHERE STORAGE_SEQ = ?";
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
