<?
	// ===================================================
	// include
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSessionAjax($url = '/_admin/');
	
	// ===================================================
	// get parameter
	// ===================================================
	$pageaction 	= $cFnc->getReq('pageaction');
	$category_seq 	= $cFnc->getReq("category_seq");
	
	$CATEGORY_SEQ 	= $cFnc->getReq("CATEGORY_SEQ");
	$CATEGORY_NM 	= $cFnc->getReq("CATEGORY_NM");
	$USE_YN 		= $cFnc->getReq("USE_YN");
	$ORDER_NO 		= $cFnc->getReq("ORDER_NO");
	$hscode_seq		= $cFnc->getReq("hscode_seq");
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
			$result['url'] = "categoryList.php";
			
			$arParam = array();			
			array_push( $arParam, $CATEGORY_NM);
			array_push( $arParam, $USE_YN);
			array_push( $arParam, $ORDER_NO);
			array_push( $arParam, 0);
			array_push( $arParam, 1);
			array_push( $arParam, $hscode_seq);
			$qry = "
				INSERT INTO TB_CATEGORY ( CATEGORY_NM, USE_YN, ORDER_NO, REG_DT, MOD_DT, PARENT_SEQ, DEPTH, HSCODE_SEQ )
				VALUES (
				?
				,?
				,?
				,now()
				,now()				
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
			if($category_seq == '') throw new Exception('잘못된 접근입니다');
			
			$result['msg'] = "수정이 완료되었습니다";
			$result['url'] = "categoryView.php";
			
			$arParam = array();
			array_push( $arParam, $CATEGORY_NM);
			array_push( $arParam, $USE_YN);
			array_push( $arParam, $ORDER_NO);
			array_push( $arParam, $hscode_seq);

			array_push($arParam, $category_seq);
			$qry = "
				UPDATE TB_CATEGORY SET
				CATEGORY_NM = ?
				, USE_YN = ?
				, ORDER_NO = ?
				, HSCODE_SEQ = ?
				, MOD_DT = now()
				WHERE CATEGORY_SEQ = ?
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result['status'] = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception('수정중 오류가 발생했습니다');
		}
		else if($pageaction == 'DELETE'){
			if($category_seq == '') throw new Exception('잘못된 접근입니다');
			
			$result['msg'] = "삭제되었습니다";
			$result['url'] = "categoryList.php";
			
			$arParam = array();
			array_push($arParam, "N");
			array_push($arParam, $category_seq);
			$qry = "UPDATE TB_CATEGORY SET USE_YN = ?, MOD_DT = NOW() WHERE CATEGORY_SEQ = ?";
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
