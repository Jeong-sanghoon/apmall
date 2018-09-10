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
	$sum_order_seq = $cFnc->getReq('arr_order_seq');
	$sum_item_seq = $cFnc->getReq('arr_item_seq');
	$sum_delivery_seq = $cFnc->getReq('arr_delivery_seq');
	$status = $cFnc->getReq('status');
	
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
		
		
		if($pageaction == 'accept'){
			/* 주문접수되돌리기 */
			// 주문아이템이 모두 결제확인 상태일때만 취소가 되어야 함
			$arr_order_seq = explode(',', $sum_order_seq);
			
			foreach($arr_order_seq as $i=>$order_seq){
				$arParam = array();
				array_push( $arParam, $order_seq);
				$qry = "
					SELECT `STATUS`
					FROM TB_ORDER_ITEM
					WHERE ORDER_SEQ = ?
				";
				$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
				$result = $cPdo->execQuery('list', $qry, $arParam);
				if(!$result['status'])	throw new Exception($result['msg'], 1001);
				$rs = $result['data'];
				
				$cnt = 0;
				foreach($rs as $j=>$ds){
					if($ds['STATUS'] != 'B'){
						$cnt++;
					}
				}
				
				if($cnt > 0) throw new Exception("선택하신 주문번호에 해당하는 모든 상품이 결제확인 상태일때만 주문접수상태로 되돌릴수 있습니다.");
			}
			
			// 주문상태이력등록
			$arParam = array();
			array_push( $arParam, $sum_order_seq);
			$qry = "
				SELECT ITEM_SEQ, ITEM_ROWID, STATUS
				FROM TB_ORDER_ITEM
				WHERE ORDER_SEQ IN (". $sum_order_seq .")
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
			$qry = "
				UPDATE TB_ORDER_ITEM SET
				`STATUS` = ?
				, DEPOSIT_DT = NULL
				WHERE ORDER_SEQ IN (". $sum_order_seq .")
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			
			$result['msg'] = "상태변경이 완료되었습니다";
			$result['url'] = "";
		}
		else if($pageaction == 'pay' || $pageaction == 'goods_ready' || $pageaction == 'enter_ready'){
			// 결제확인, 상품준비, 입고대기 되돌리기
			// 주문상태이력등록
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
				
				// param : status, item_seq, item_rowid, old_status, reg_dt
				$result = SET_ORDER_STATUS_HIST($status, $item_seq, $rowid, $old_status, $now_dt);
				if(!$result['status']) throw new Exception($result['msg'], 1001);
			}
			
			// 상태변경
			$qryDate = '';
			if($pageaction == 'pay') $str_dt = 'READY_DT';
			else if($pageaction == 'goods_ready') $str_dt = 'WEARRD_DT';
			else if($pageaction == 'enter_ready') $str_dt = 'WEARSC_DT';
			
			$arParam = array();
			array_push( $arParam, $status);
			$qry = "
				UPDATE TB_ORDER_ITEM SET
				`STATUS` = ?
				, ". $str_dt ." = NULL
				WHERE ITEM_SEQ IN (". $sum_item_seq .")
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			
			// 상품준비 > 결제확인 되돌릴시 구매정보삭제
			if($pageaction == 'pay'){
				$arParam = array();
				$qry = "DELETE FROM TB_ORDER_PURCHASE WHERE ITEM_SEQ IN (". $sum_item_seq .")";
				$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
				$result = $cPdo->execQuery('update', $qry, $arParam);
				if(!$result['status'])	throw new Exception($result['msg'], 1001);
			}
			
			$result['msg'] = "상태변경이 완료되었습니다";
			$result['url'] = "";
		}
		else if($pageaction == 'enter_fin'){
			// 입고완료되돌리기
			// 아이템시퀀스조회
			$arParam = array();
			$qry = "
				SELECT ITEM_SEQ
				FROM TB_DELIVERY_ITEM
				WHERE DELIVERY_SEQ IN (". $sum_delivery_seq .")
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('list', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			$rsItem = $result['data'];
			
			// 출고정보삭제
			$arParam = array();
			$qry = "
				DELETE FROM TB_DELIVERY_ITEM WHERE DELIVERY_SEQ IN (". $sum_delivery_seq .")
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			
			$arParam = array();
			$qry = "
				DELETE FROM TB_DELIVERY WHERE DELIVERY_SEQ IN (". $sum_delivery_seq .")
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			
			
			// 주문상태이력등록
			foreach($rsItem as $i=>$dsItem){
				if($i > 0) $sum_item_seq .= ',';
				$sum_item_seq .= $dsItem['ITEM_SEQ'];
			}
			
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
				
				// param : status, item_seq, item_rowid, old_status, reg_dt
				$result = SET_ORDER_STATUS_HIST($status, $item_seq, $rowid, $old_status, $now_dt);
				if(!$result['status']) throw new Exception($result['msg'], 1001);
			}
			
			// 상태변경
			$arParam = array();
			array_push( $arParam, $status);
			$qry = "
				UPDATE TB_ORDER_ITEM SET
				`STATUS` = ?
				, GOODSRD_DT = NULL
				WHERE ITEM_SEQ IN (". $sum_item_seq .")
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			
			$result['msg'] = "상태변경이 완료되었습니다";
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
