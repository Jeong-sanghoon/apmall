<?
	// ===================================================
	// include
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSessionAjax($url = '/_admin/');
	
	// ===================================================
	// get parameter
	// ===================================================
	$pageaction 		= $cFnc->getReq('pageaction');		
	$manufacture_seq 	= $cFnc->getReq("manufacture_seq");
	
	$MANUFACTURE_SEQ 	= $cFnc->getReq("MANUFACTURE_SEQ");
	$MANUFACTURENAME 	= $cFnc->getReq("MANUFACTURENAME");
	$USERNAME 			= $cFnc->getReq("USERNAME");
	$TEL 				= $cFnc->getReq("TEL");
	$ADDR 				= $cFnc->getReq("ADDR");
	$USE_YN 			= $cFnc->getReq("USE_YN");
	$EMAIL 				= $cFnc->getReq("EMAIL");	
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
			$result['url'] = "manufactureList.php";
			
			$arParam = array();						
			array_push( $arParam, $MANUFACTURENAME);			
			array_push( $arParam, $USERNAME);
			array_push( $arParam, $TEL);
			array_push( $arParam, $ADDR);
			array_push( $arParam, $USE_YN);
			array_push( $arParam, $EMAIL);
			$qry = "
				INSERT INTO TB_MANUFACTURE (  MANUFACTURENAME, REG_DT, MOD_DT, USERNAME, TEL, ADDR, USE_YN, EMAIL )
				VALUES (
				?				
				,now()
				,now()
				,?
				,?
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
			if($manufacture_seq == '') throw new Exception('잘못된 접근입니다');
			
			$result['msg'] = "수정이 완료되었습니다";
			$result['url'] = "manufactureView.php";
			
			$arParam = array();
			array_push( $arParam, $MANUFACTURENAME);
			array_push( $arParam, $USERNAME);
			array_push( $arParam, $TEL);
			array_push( $arParam, $ADDR);
			array_push( $arParam, $USE_YN);
			array_push( $arParam, $EMAIL);

			array_push($arParam, $manufacture_seq);
			$qry = "
				UPDATE TB_MANUFACTURE SET 				
				MANUFACTURENAME = ?				
				, MOD_DT = now()
				, USERNAME = ?
				, TEL = ?
				, ADDR = ?
				, USE_YN = ?
				, EMAIL = ?
				WHERE MANUFACTURE_SEQ = ?
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result['status'] = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception('수정중 오류가 발생했습니다');
		}
		else if($pageaction == 'DELETE'){
			if($manufacture_seq == '') throw new Exception('잘못된 접근입니다');
			
			$result['msg'] = "삭제되었습니다";
			$result['url'] = "manufactureList.php";
			
			$arParam = array();
			array_push($arParam, "N");
			array_push($arParam, $manufacture_seq);
			$qry = "UPDATE TB_MANUFACTURE SET USE_YN = ?, MOD_DT = NOW() WHERE MANUFACTURE_SEQ = ?";
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
