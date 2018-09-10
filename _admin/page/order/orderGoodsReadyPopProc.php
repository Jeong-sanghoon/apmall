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
	$p_linkurl = $cFnc->getReq('p_linkurl');
	$p_qty = $cFnc->getReq('p_qty');
	$p_price = $cFnc->getReq('p_price');
	$p_deliveryfee = $cFnc->getReq('p_deliveryfee');
	$p_discount = $cFnc->getReq('p_discount');
	$p_pricesum = $cFnc->getReq('p_pricesum');
	$p_optfield = $cFnc->getReq('p_optfield');
	$p_optvalue = $cFnc->getReq('p_optvalue');
	$p_status = $cFnc->getReq('p_status');
	$p_memo = $cFnc->getReq('p_memo');
	$item_seq = $cFnc->getReq('item_seq');
	$item_rowid = $cFnc->getReq('item_rowid');
	$purchase_seq = $cFnc->getReq('purchase_seq');
	$payment_tp = $cFnc->getReq('payment_tp');
	
	$sum_purchase_seq = $cFnc->getReq('sum_purchase_seq');
	$seq_050 = $cFnc->getReq('050_seq');
	$deliver_no = $cFnc->getReq('deliver_no');
	$approval_no = $cFnc->getReq('approval_no');
	
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
	$now_dt = date('Y-m-d H:i:s');
	
	// =====================================================
	// Start Tran
	// =====================================================
	try{
		$cPdo->tran();
		
		if($pageaction == 'insert'){
			$p_status = 'P';		// 구매중
			
			$arParam = array();
			array_push( $arParam, $p_linkurl);
			array_push( $arParam, $p_qty);
			array_push( $arParam, $p_price);
			array_push( $arParam, $p_deliveryfee);
			array_push( $arParam, $p_discount);
			array_push( $arParam, $p_pricesum);
			array_push( $arParam, $p_optfield);
			array_push( $arParam, $p_optvalue);
			array_push( $arParam, $p_memo);
			array_push( $arParam, $now_dt);
			array_push( $arParam, $item_seq);
			array_push( $arParam, $item_rowid);
			array_push( $arParam, $p_status);
			array_push( $arParam, $payment_tp);
			array_push( $arParam, $S_SEQ);
			$qry = "
				INSERT INTO `NS9`.`TB_ORDER_PURCHASE`(`P_LINKURL`,`P_QTY`,`P_PRICE`,`P_DELIVERYFEE`, P_DISCOUNT,`P_PRICESUM`,`P_OPTFIELD`,`P_OPTVALUE`,`P_MEMO`,`REG_DT`,`ITEM_SEQ`,`ITEM_ROWID`, P_STATUS, PAYMENT_TP, ADM_SEQ)
				VALUES 
				(
					?
					, ?
					, ?
					, ?
					, ?
					, ?
					, ?
					, ?
					, ?
					, ?
					, ?
					, ?
					, ?
					, ?
					, ?
				)
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('insert', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			$purchase_seq = $result['data']['insert_id'];
			
			
			// 주문상태이력 등록
			// param : status, purchase_seq, reg_dt, old_status
			$result = SET_ORDER_PURCHASE_STATUS_HIST($p_status, $purchase_seq, $now_dt);
			if(!$result['status']) throw new Exception($result['msg'], 1001);
			
			
			// 050번호 할당
			$arParam = array();
			array_push( $arParam, $purchase_seq);
			array_push( $arParam, $seq_050);
			$qry = "
				UPDATE `TB_ORDER_PURCHASE_050` SET
				PURCHASE_SEQ = ?
				WHERE SEQ = ?
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			
			
			$result['msg'] = "구매정보를 입력하였습니다";
			$result['url'] = "";
		}
		else if($pageaction == 'update'){
			$arParam = array();
			array_push( $arParam, $p_linkurl);
			array_push( $arParam, $p_qty);
			array_push( $arParam, $p_price);
			array_push( $arParam, $p_deliveryfee);
			array_push( $arParam, $p_discount);
			array_push( $arParam, $p_pricesum);
			array_push( $arParam, $p_optfield);
			array_push( $arParam, $p_optvalue);
			array_push( $arParam, $p_memo);
			array_push( $arParam, $now_dt);
			array_push( $arParam, $deliver_no);
			array_push( $arParam, $payment_tp);
			array_push( $arParam, $approval_no);
			array_push( $arParam, $S_SEQ);
			array_push( $arParam, $purchase_seq);
			$qry = "
				UPDATE TB_ORDER_PURCHASE SET
				P_LINKURL = ?
				, P_QTY = ?
				, P_PRICE = ?
				, P_DELIVERYFEE = ?
				, P_DISCOUNT = ?
				, P_PRICESUM = ?
				, P_OPTFIELD = ?
				, P_OPTVALUE = ?
				, P_MEMO = ?
				, MOD_DT = ?
				, DELIVER_NO = ?
				, PAYMENT_TP = ?
				, APPROVAL_NO = ?
				, ADM_SEQ = ?
				WHERE PURCHASE_SEQ = ?
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			
			$result['msg'] = "구매정보를 수정하였습니다";
			$result['url'] = "";
		}
		else if($pageaction == 'buy'){
			$p_status = 'R';		// 입고대기
			
			// 주문상태이력 등록
			$arParam = array();
			$qry = "
				SELECT PURCHASE_SEQ, P_STATUS
				FROM TB_ORDER_PURCHASE
				WHERE PURCHASE_SEQ IN (". $sum_purchase_seq .")
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('list', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			$rs = $result['data'];
			
			foreach($rs as $i=>$ds){
				// param : status, purchase_seq, reg_dt, old_status
				$result = SET_ORDER_PURCHASE_STATUS_HIST($p_status, $ds['PURCHASE_SEQ'], $now_dt, $ds['P_STATUS']);
				if(!$result['status']) throw new Exception($result['msg'], 1001);
			}
			
			// 상태업데이트
			$arParam = array();
			array_push( $arParam, $p_status);
			array_push( $arParam, $now_dt);
			array_push( $arParam, $S_SEQ);
			$qry = "
				UPDATE TB_ORDER_PURCHASE SET
				P_STATUS = ?
				, MOD_DT = ?
				, ADM_SEQ = ?
				WHERE PURCHASE_SEQ IN (". $sum_purchase_seq .")
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			
			
			$result['msg'] = "구매완료 되었습니다";
			$result['url'] = "";
		}
		else if($pageaction == 'delete'){
			$arParam = array();
			array_push( $arParam, $now_dt);
			array_push( $arParam, $S_SEQ);
			$qry = "
				UPDATE TB_ORDER_PURCHASE SET
				DEL_YN = 'Y'
				, MOD_DT = ?
				, ADM_SEQ = ?
				WHERE PURCHASE_SEQ IN (". $sum_purchase_seq .")
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			
			// 050번호 
			$arParam = array();
			$qry = "
				UPDATE `TB_ORDER_PURCHASE_050` SET
				PURCHASE_SEQ = NULL
				WHERE PURCHASE_SEQ IN (". $sum_purchase_seq .")
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			
			$result['msg'] = "구매정보를 삭제하였습니다";
			$result['url'] = "";
		}
		
		
		$cPdo->commit();
	}
	catch(Exception $e){
		$cPdo->rollback();
		$result['status'] = 0;
		$result['msg'] = $e->getMessage();
		if($e->getCode() != '')	$result['msg'] .= ' // '. $e->getCode();
		$result['url'] = "";
	}
	
	echo json_encode($result);
	$cPdo->close();
?>
