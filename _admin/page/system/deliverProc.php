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
	$cost_seq	= $cFnc->getReq('cost_seq');
	
	$COST_SEQ 	= $cFnc->getReq("COST_SEQ");
	$COST 		= $cFnc->getReq("COST");		
	$COST_NM 	= $cFnc->getReq("COST_NM");
	$USE_YN 	= $cFnc->getReq("USE_YN");
	$COST_UP 	= $cFnc->getReq("COST_UP");	
	$COST_TYPE 	= $cFnc->getReq("COST_TYPE");

	$cost_up 	= $cFnc->getReq("cost_up");
	$cost 		= $cFnc->getReq("cost");
	$cost_type 	= $cFnc->getReq("cost_type");
	$obj_tp 	= $cFnc->getReq("obj_tp");
	
	//$cFnc->echoArr($_REQUEST); exit;
	
	// =====================================================
	// Set Variables
	// =====================================================
	$result = array();
	$result['status'] = 1;
	$result['msg'] = "";
	$result['url'] = "";
	
	$COST_NM = CODE_DELIVERY_COST_TYPE($COST_TYPE);

	$cPdo = new cPdo($ARR_DB_INFO);	
	
	// =====================================================
	// Start Tran
	// =====================================================
	try{
		if($pageaction == 'INSERT'){
			$result['msg'] = "입력이 완료되었습니다";
			$result['url'] = "deliverList.php";
			
			$arParam = array();			
			array_push( $arParam, $cost);
			array_push( $arParam, $S_SEQ);
			array_push( $arParam, $COST_NM);
			array_push( $arParam, $cost_up);
			array_push( $arParam, $cost_type);
			array_push( $arParam, $obj_tp);

			$qry = "
				INSERT INTO TB_DELIVER_COST ( COST, REG_DT, ADM_SEQ, COST_NM, USE_YN, COST_UP, COST_TYPE, OBJ_TP )
				VALUES (				
				?
				,now()
				,?
				,?
				,'N'
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
			if($cost_seq == '') throw new Exception('잘못된 접근입니다');
			
			$result['msg'] = "배송비 설정 적용이 완료되었습니다";
			$result['url'] = "deliverList.php";
			
			// 강제 업데이트
			$arParam = array();
			array_push($arParam, $cost_type);
			array_push($arParam, $obj_tp);
			$qry = "
				UPDATE TB_DELIVER_COST 
				SET USE_YN = 'N'
				WHERE COST_TYPE = ?
				AND OBJ_TP = ?
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$cPdo->execQuery('update', $qry, $arParam);


			$arParam = array();
			array_push( $arParam, $S_SEQ);						
			array_push($arParam, $cost_seq);
			$qry = "
				UPDATE TB_DELIVER_COST SET				
				USE_YN = 'Y'
				, ADM_SEQ = ?
				, MOD_DT = now()
				WHERE COST_SEQ = ?
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
