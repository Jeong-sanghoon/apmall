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
	$user_seq	= $cFnc->getReq('user_seq');
	
	$USER_ID 	= $cFnc->getReq("USER_ID");
	$USER_SEQ 	= $cFnc->getReq("USER_SEQ");
	$USER_NM 	= $cFnc->getReq("USER_NM");
	$TEL 		= $cFnc->getReq("TEL");
	$USE_YN 	= $cFnc->getReq("USE_YN");	
	$CODE_SEQ 	= $cFnc->getReq("CODE_SEQ");
	$JOIN_TP 	= $cFnc->getReq("JOIN_TP");
	$ADDR 		= $cFnc->getReq("ADDR");
	$PWD 		= $cFnc->getReq("PWD");
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
			array_push( $arParam, $USER_ID);			
			array_push( $arParam, $USER_NM);
			array_push( $arParam, $TEL);
			array_push( $arParam, $USE_YN);
			array_push( $arParam, $JOIN_TP);
			array_push( $arParam, $ADDR);	
			array_push( $arParam, $PWD);
			$qry = "
				INSERT INTO TB_USER ( USER_ID, USER_NM, TEL, REG_DT, MOD_DT, USE_YN, JOIN_TP, ADDR, PWD )
				VALUES (
				?				
				,?
				,?
				,now()
				,now()
				,?
				,?
				,?
				,PASSWORD(?)
				)

			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('insert', $qry, $arParam);
			if(!$result['status'])	throw new Exception('입력중 오류가 발생했습니다');
			
			$result['msg'] = "입력이 완료되었습니다";
			$result['url'] = "memberList.php";
		}
		else if($pageaction == 'UPDATE'){
			if($user_seq == '') throw new Exception('잘못된 접근입니다');
			
			$arParam = array();
			array_push( $arParam, $USER_NM);
			array_push( $arParam, $TEL);
			array_push( $arParam, $USE_YN);
			array_push( $arParam, $JOIN_TP);
			array_push( $arParam, $ADDR);			
			
			if($PWD != ''){
				$qryAdd .= ", PWD = password(?)";
				array_push($arParam, $PWD);
			}
			
			array_push($arParam, $user_seq);
			$qry = "
				UPDATE TB_USER SET 								
				USER_NM = ?
				, TEL = ?
				, MOD_DT = now()
				, USE_YN = ?				
				, JOIN_TP = ?				
				, ADDR = ?
				". $qryAdd ."
				WHERE USER_SEQ = ?
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception('수정중 오류가 발생했습니다');
			
			$result['msg'] = "수정이 완료되었습니다";
			$result['url'] = "memberView.php";
		}
		else if($pageaction == 'DELETE'){
			if($user_seq == '') throw new Exception('잘못된 접근입니다');
			
			$arParam = array();
			array_push($arParam, "N");
			array_push($arParam, $user_seq);
			$qry = "UPDATE TB_USER SET USE_YN = ?, MOD_DT = NOW() WHERE USER_SEQ = ?";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception('삭제중 오류가 발생했습니다');
			
			$result['msg'] = "삭제되었습니다";
			$result['url'] = "memberList.php";
		}
		else if($pageaction == 'IDSEARCH'){
			if($USER_ID == '') throw new Exception('잘못된 접근입니다');
			
			$arParam = array();
			array_push($arParam, $USER_ID);
			$qry = "SELECT COUNT(SEQ) AS CNT FROM TB_USER WHERE USER_ID = ?";
			$dsCnt = $cPdo->execQuery('data', $qry, $arParam);
			if($dsCnt['CNT'] > 0) throw new Exception('이미 존재하는 아이디 입니다');
			
			$result['msg'] = "사용가능합니다.";
			$result['status'] = 0;
			$result['url'] = "";
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
