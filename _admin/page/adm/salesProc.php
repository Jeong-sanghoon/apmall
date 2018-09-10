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
	$sales_seq 	= $cFnc->getReq("sales_seq");
	
	$SALES_SEQ 	= $cFnc->getReq("SALES_SEQ");
	$SALESNAME 	= $cFnc->getReq("SALESNAME");
	$PER 		= $cFnc->getReq("PER");
	$USE_YN 	= $cFnc->getReq("USE_YN");
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
			$result['url'] = "salesList.php";
			
			$arParam = array();
			array_push( $arParam, $SALESNAME);
			array_push( $arParam, $PER);
			array_push( $arParam, $USE_YN);			
			$qry = "
				INSERT INTO TB_SALES ( SALESNAME, PER, REG_DT, MOD_DT, USE_YN )
				VALUES (
				?				
				,?
				,now()
				,now()
				,?
				)
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result['status'] = $cPdo->execQuery('insert', $qry, $arParam);
			if(!$result['status'])	throw new Exception('입력중 오류가 발생했습니다');
		}
		else if($pageaction == 'UPDATE'){
			if($sales_seq == '') throw new Exception('잘못된 접근입니다');
			
			$result['msg'] = "수정이 완료되었습니다";
			$result['url'] = "salesView.php";
			
			$arParam = array();
			array_push( $arParam, $SALESNAME);
			array_push( $arParam, $PER);
			array_push( $arParam, $USE_YN);			

			array_push($arParam, $sales_seq);
			$qry = "
				UPDATE TB_SALES SET 
				SALESNAME = ?
				, PER = ?				
				, MOD_DT = now()
				, USE_YN = ?
				WHERE SALES_SEQ = ?
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result['status'] = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception('수정중 오류가 발생했습니다');
		}
		else if($pageaction == 'DELETE'){
			if($sales_seq == '') throw new Exception('잘못된 접근입니다');
			
			$result['msg'] = "삭제되었습니다";
			$result['url'] = "salesList.php";
			
			$arParam = array();
			array_push($arParam, "N");
			array_push($arParam, $sales_seq);
			$qry = "UPDATE TB_SALES SET USE_YN = ?, MOD_DT = NOW() WHERE SALES_SEQ = ?";
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
