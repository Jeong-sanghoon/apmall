<?
	// ===================================================
	// include
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSessionAjax($url = '/_admin/');
	
	// ===================================================
	// get parameter
	// ===================================================
	// 공통
	$pageaction = $cFnc->getReq('pageaction');
	$arr_order_seq = $cFnc->getReq('arr_order_seq');
	$arr_item_seq = $cFnc->getReq('arr_item_seq');
	$arr_delivery_seq = $cFnc->getReq('arr_delivery_seq');
	$status = $cFnc->getReq('status');
	
	// 출고요청
	$sum_item_seq = $cFnc->getReq('sum_item_seq');
	$deliveryid = $cFnc->getReq('deliveryid');
	$weight = $cFnc->getReq('weight');
	$w_price = $cFnc->getReq('w_price');
	$w_price_p = $cFnc->getReq('w_price_p');
	$w_price_p_vnd = $cFnc->getReq('w_price_p_vnd');
	$cod = $cFnc->getReq('cod');
	$cod_vnd = $cFnc->getReq('cod_vnd');
	$memo = $cFnc->getReq('memo');
	
	// 취소처리
	$cancel_memo = $cFnc->getReq('cancel_memo');
	$order_seq = $cFnc->getReq('order_seq');
	
	// 실제운송정보
	$arr_real_weight = $cFnc->getReq('arr_real_weight');
	$arr_real_w_price = $cFnc->getReq('arr_real_w_price');
	$arr_hbl_cd = $cFnc->getReq('arr_hbl_cd');
	
	// 결제승인정보
	$arr_purchase_seq = $cFnc->getReq('arr_purchase_seq');
	$arr_approval_dt = $cFnc->getReq('arr_approval_dt');
	$arr_approval_no = $cFnc->getReq('arr_approval_no');
	
	//echo json_encode($sum_item_seq);exit;
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
		
		
		if($pageaction == 'pay'){
			/* 결제확인 */
			// 주문상태이력 등록
			$arParam = array();
			$qry = "
				SELECT ITEM_SEQ, ITEM_ROWID, STATUS
				FROM TB_ORDER_ITEM
				WHERE ORDER_SEQ IN (". $arr_order_seq .")
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('list', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			$rs = $result['data'];
			
			foreach($rs as $i=>$ds){
				$item_seq = $ds['ITEM_SEQ'];
				$rowid = $ds['ITEM_ROWID'];
				$old_status = $ds['STATUS'];
				
				// param : status, item_seq, item_rowid, old_status, reg_dt
				$result = SET_ORDER_STATUS_HIST($status, $item_seq, $rowid, $old_status, $now_dt);
				if(!$result['status']) throw new Exception($result['msg'], 1001);
			}
			
			// 상태변경
			$arParam = array();
			array_push( $arParam, $status);
			array_push( $arParam, $now_dt);
			$qry = "
				UPDATE TB_ORDER_ITEM SET
				`STATUS` = ?
				, DEPOSIT_DT = ?
				WHERE ORDER_SEQ IN (". $arr_order_seq .")
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			
			
			$result['msg'] = "상태변경이 완료되었습니다";
			$result['url'] = "";
		}
		else if($pageaction == 'goods_ready' || $pageaction == 'enter_ready' || $pageaction == 'enter_fin'){
			/* 상품준비, 입고대기, 입고완료 */
			// 주문상태이력 등록
			$arParam = array();
			$qry = "
				SELECT ITEM_SEQ, ITEM_ROWID, STATUS
				FROM TB_ORDER_ITEM
				WHERE ITEM_SEQ IN (". $arr_item_seq .")
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('list', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			$rs = $result['data'];
			
			foreach($rs as $i=>$ds){
				$item_seq = $ds['ITEM_SEQ'];
				$rowid = $ds['ITEM_ROWID'];
				$old_status = $ds['STATUS'];
				
				// param : status, item_seq, item_rowid, old_status, reg_dt
				$result = SET_ORDER_STATUS_HIST($status, $item_seq, $rowid, $old_status, $now_dt);
				if(!$result['status']) throw new Exception($result['msg'], 1001);
			}
			
			// 상태변경
			$qryDate = '';
			if($pageaction == 'goods_ready') $str_dt = 'READY_DT';
			else if($pageaction == 'enter_ready') $str_dt = 'WEARRD_DT';
			else if($pageaction == 'enter_fin') $str_dt = 'WEARSC_DT';
			
			$arParam = array();
			array_push( $arParam, $status);
			array_push( $arParam, $now_dt);
			$qry = "
				UPDATE TB_ORDER_ITEM SET
				`STATUS` = ?
				, ". $str_dt ." = ?
				WHERE ITEM_SEQ IN (". $arr_item_seq .")
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			
			
			$result['msg'] = "상태변경이 완료되었습니다";
			$result['url'] = "";
		}
		else if($pageaction == 'out_req'){
			/* 출고요청 */
			$arr_item_seq = array();
			$arr_item_rowid = array();
			
			// 주문상태이력 등록
			$arParam = array();
			$qry = "
				SELECT ITEM_SEQ, ITEM_ROWID, STATUS
				FROM TB_ORDER_ITEM
				WHERE ITEM_SEQ IN (". $sum_item_seq .")
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('list', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			$rs = $result['data'];
			
			foreach($rs as $i=>$ds){
				$item_seq = $ds['ITEM_SEQ'];
				$rowid = $ds['ITEM_ROWID'];
				$old_status = $ds['STATUS'];
				
				array_push($arr_item_seq, $item_seq);
				array_push($arr_item_rowid, $rowid);
				
				// param : status, item_seq, item_rowid, old_status, reg_dt
				$result = SET_ORDER_STATUS_HIST($status, $item_seq, $rowid, $old_status, $now_dt);
				if(!$result['status']) throw new Exception($result['msg'], 1001);
			}
			
			// 상태변경
			$arParam = array();
			array_push( $arParam, $status);
			array_push( $arParam, $now_dt);
			$qry = "
				UPDATE TB_ORDER_ITEM SET
				`STATUS` = ?
				, GOODSRD_DT = ?
				WHERE ITEM_SEQ IN (". $sum_item_seq .")
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			
			
			// 출고마스터데이터 등록
			$arParam = array();
			array_push( $arParam, $deliveryid);
			array_push( $arParam, $now_dt);
			array_push( $arParam, $weight);
			array_push( $arParam, $w_price);
			array_push( $arParam, $memo);
			array_push( $arParam, $w_price_p);
			array_push( $arParam, $w_price_p_vnd);
			array_push( $arParam, $cod);
			array_push( $arParam, $cod_vnd);
			$qry = "
				INSERT INTO TB_DELIVERY (DELIVERYID, REG_DT, WEIGHT, W_PRICE, MEMO, W_PRICE_P, W_PRICE_P_VND, COD, COD_VND)
				VALUES(
					?
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
			$delivery_seq = $result['data']['insert_id'];
			
			
			$rowid = 0;
			$arParam = array();
			$qry = "INSERT INTO TB_DELIVERY_ITEM (DELIVERY_ROWID, REG_DT, ITEM_SEQ, DELIVERY_SEQ, ITEM_ROWID) VALUES ";
			
			foreach($arr_item_seq as $i=>$item_seq){
				if($i > 0) $qry .= ',';
				
				$rowid++;
				array_push($arParam, $rowid);
				array_push($arParam, $now_dt);
				array_push($arParam, $item_seq);
				array_push($arParam, $delivery_seq);
				array_push($arParam, $arr_item_rowid[$i]);
				$qry .= "(?, ?, ?, ?, ?)";
			}
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			
			
			$result['msg'] = "상태변경이 완료되었습니다";
			$result['url'] = "";
		}
		else if($pageaction == 'out_fin'){
			/* 출고완료 */
			// 아이템시퀀스조회
			$arParam = array();
			$qry = "
				SELECT ITEM_SEQ
				FROM TB_DELIVERY_ITEM
				WHERE DELIVERY_SEQ IN (". $arr_delivery_seq .")
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('list', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			$rsItem = $result['data'];
			
			foreach($rsItem as $i=>$dsItem){
				if($i > 0) $arr_item_seq .= ',';
				$arr_item_seq .= $dsItem['ITEM_SEQ'];
			}
			
			// 주문상태이력 등록
			$arParam = array();
			$qry = "
				SELECT ITEM_SEQ, ITEM_ROWID, STATUS
				FROM TB_ORDER_ITEM
				WHERE ITEM_SEQ IN (". $arr_item_seq .")
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('list', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			$rs = $result['data'];
			
			foreach($rs as $i=>$ds){
				$item_seq = $ds['ITEM_SEQ'];
				$rowid = $ds['ITEM_ROWID'];
				$old_status = $ds['STATUS'];
				
				// param : status, item_seq, item_rowid, old_status, reg_dt
				$result = SET_ORDER_STATUS_HIST($status, $item_seq, $rowid, $old_status, $now_dt);
				if(!$result['status']) throw new Exception($result['msg'], 1001);
			}
			
			// 상태변경
			$arParam = array();
			array_push( $arParam, $status);
			array_push( $arParam, $now_dt);
			$qry = "
				UPDATE TB_ORDER_ITEM SET
				`STATUS` = ?
				, GOODSSC_DT = ?
				WHERE ITEM_SEQ IN (". $arr_item_seq .")
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			
			
			$result['msg'] = "상태변경이 완료되었습니다";
			$result['url'] = "";
		}
		else if($pageaction == 'OK'){
			/* 구매확인완료 */
			// 아이템시퀀스조회
			$arParam = array();
			$qry = "
				SELECT ITEM_SEQ
				FROM TB_DELIVERY_ITEM
				WHERE DELIVERY_SEQ IN (". $arr_delivery_seq .")
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('list', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			$rsItem = $result['data'];
			
			foreach($rsItem as $i=>$dsItem){
				if($i > 0) $arr_item_seq .= ',';
				$arr_item_seq .= $dsItem['ITEM_SEQ'];
			}
			
			// 주문테이블 구매확인완료여부 설정
			$arParam = array();
			array_push( $arParam, 'Y');
			array_push( $arParam, $now_dt);
			$qry = "
				UPDATE TB_DELIVERY SET
				GOODSCF_YN = ?
				, MOD_DT = ?
				WHERE DELIVERY_SEQ IN (". $arr_delivery_seq .")
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			
			
			// 아이템테이블 구매확인완료여부 설정
			$arParam = array();
			array_push( $arParam, 'Y');
			array_push( $arParam, $now_dt);
			$qry = "
				UPDATE TB_ORDER_ITEM SET
				GOODSCF_YN = ?
				, GOODSCF_DT = ?
				WHERE ITEM_SEQ IN (". $arr_item_seq .")
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			
			
			$result['msg'] = "구매확인이 완료되었습니다";
			$result['url'] = "";
		}
		else if($pageaction == 'cancel'){
			// 주문취소
			$arParam = array();
			array_push( $arParam, $cancel_memo);
			array_push( $arParam, $now_dt);
			array_push( $arParam, $order_seq);
			$qry = "
				UPDATE TB_ORDER SET
				CANCEL_YN = 'Y'
				, CANCEL_MEMO = ?
				, CANCEL_DT = ?
				WHERE ORDER_SEQ = ?
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			
			
			$result['msg'] = "취소처리가 완료되었습니다";
			$result['url'] = "";
		}
		else if($pageaction == 'real_delivery'){
			// 실제운송정보저장
			$str_delivery_seq = $arr_delivery_seq;
			$arr_delivery_seq = explode(',', $arr_delivery_seq);
			$arr_real_weight = explode(',', $arr_real_weight);
			$arr_real_w_price = explode(',', $arr_real_w_price);
			$arr_hbl_cd = explode(',', $arr_hbl_cd);
			
			$arParam = array();
			
			$qry_add1 = 'CASE';
			$qry_add2 = 'CASE';
			$qry_add3 = 'CASE';
			
			foreach($arr_delivery_seq as $i=>$delivery_seq){
				$real_weight = $arr_real_weight[$i];
				$real_w_price = $arr_real_w_price[$i];
				$hbl_cd = $arr_hbl_cd[$i];
				
				if($real_weight != '') $qry_add1 .= " WHEN DELIVERY_SEQ = ". $delivery_seq ." THEN '". $real_weight ."'";
				if($real_w_price != '') $qry_add2 .= " WHEN DELIVERY_SEQ = ". $delivery_seq ." THEN ". $real_w_price;
				if($hbl_cd != '') $qry_add3 .= " WHEN DELIVERY_SEQ = ". $delivery_seq ." THEN '". $hbl_cd ."'";
			}
			
			$qry_add1 = $qry_add1 ." END";
			$qry_add2 = $qry_add2 ." END";
			$qry_add3 = $qry_add3 ." END";
			
			$qry = "
				UPDATE TB_DELIVERY SET
				REAL_WEIGHT = (". $qry_add1 .")
				, REAL_W_PRICE = (". $qry_add2 .")
				, HBL_CD = (". $qry_add3 .")
				WHERE DELIVERY_SEQ IN (". $str_delivery_seq .")
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			
			
			$result['msg'] = "실제운송정보 저장이 완료되었습니다";
			$result['url'] = "";
		}
		else if($pageaction == 'approval'){
			// 결제승인정보저장
			$str_purchase_seq = $arr_purchase_seq;
			$arr_purchase_seq = explode(',', $arr_purchase_seq);
			$arr_approval_dt = explode(',', $arr_approval_dt);
			$arr_approval_no = explode(',', $arr_approval_no);
			
			$arParam = array();
			
			$qry_add1 = 'CASE';
			$qry_add2 = 'CASE';
			$qry_add3 = 'CASE';
			
			foreach($arr_purchase_seq as $i=>$purchase_seq){
				$approval_dt = $arr_approval_dt[$i];
				$approval_no = $arr_approval_no[$i];
				
				if($approval_dt != '') $qry_add1 .= " WHEN PURCHASE_SEQ = ". $purchase_seq ." THEN '". $approval_dt ."'";
				if($approval_no != '') $qry_add2 .= " WHEN PURCHASE_SEQ = ". $purchase_seq ." THEN ". $approval_no;
			}
			
			$qry_add1 = $qry_add1 ." END";
			$qry_add2 = $qry_add2 ." END";
			$qry_add3 = $qry_add3 ." END";
			
			$qry = "
				UPDATE TB_ORDER_PURCHASE SET
				APPROVAL_DT = (". $qry_add1 .")
				, APPROVAL_NO = (". $qry_add2 .")
				WHERE PURCHASE_SEQ IN (". $str_purchase_seq .")
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			
			
			$result['msg'] = "결제승인정보 저장이 완료되었습니다";
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
