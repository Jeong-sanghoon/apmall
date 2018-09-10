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
	$deposit_seq	= $cFnc->getReq('deposit_seq');
	
	$DEPOSIT_SEQ 	= $cFnc->getReq("DEPOSIT_SEQ");
	$RATE 			= $cFnc->getReq("RATE");
	$rate 			= $cFnc->getReq("rate");
	$REG_DT 		= $cFnc->getReq("REG_DT");
	$ADM_SEQ 		= $cFnc->getReq("ADM_SEQ");
	$USE_YN 		= $cFnc->getReq("USE_YN");

	//$cFnc->echoArr($_REQUEST); exit;
	
	// =====================================================
	// Set Variables
	// =====================================================
	$result = array();
	$result['status'] = 1;
	$result['msg'] = "";
	$result['url'] = "";
	

	$cPdo = new cPdo($ARR_DB_INFO);	
	
	// =====================================================
	// Start Tran
	// =====================================================
	try{
		if($pageaction == 'INSERT'){
			$result['msg'] = "입력이 완료되었습니다";
			$result['url'] = "depositList.php";
			
			$arParam = array();			
			array_push( $arParam, $rate);			
			array_push( $arParam, $S_SEQ);			
			$qry = "
				INSERT INTO TB_DEPOSIT ( RATE, REG_DT, ADM_SEQ, USE_YN )
				VALUES (				
				?
				,now()
				,?
				,'N'
				)
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result['status'] = $cPdo->execQuery('insert', $qry, $arParam);
			if(!$result['status'])	throw new Exception('입력중 오류가 발생했습니다');
		}
		else if($pageaction == 'UPDATE'){
			if($deposit_seq == '') throw new Exception('잘못된 접근입니다');
			
			$result['msg'] = "즉시적용이 완료되었습니다";
			$result['url'] = "depositList.php";
			
			// 강제 업데이트
			$arParam = array();
			$qry = "
				UPDATE TB_DEPOSIT SET USE_YN = 'N'
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$cPdo->execQuery('update', $qry, $arParam);


			$arParam = array();
			array_push( $arParam, $S_SEQ);			
			
			array_push($arParam, $deposit_seq);
			$qry = "
				UPDATE TB_DEPOSIT SET				
				USE_YN = 'Y'
				, ADM_SEQ = ?
				, MOD_DT = now()
				WHERE DEPOSIT_SEQ = ?
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result['status'] = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception('수정중 오류가 발생했습니다');
		}
		else if($pageaction == 'DELETE'){
			/*
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
			*/
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
