<?
	// ===================================================
	// include
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSessionAjax($url = '/_admin/');
	
	// ===================================================
	// get parameter
	// ===================================================
	$type = $cFnc->getReq('type', '');
	$barcodeid = $cFnc->getReq('barcodeid', '');
	
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
		$cPdo->tran();
		
		if($type == 'enter'){
			// 입고
			$arParam = array();
			array_push( $arParam, $barcodeid);
			$qry = "
				SELECT A.ITEM_SEQ, A.ITEMID AS ID, A.LINKURL, A.PRODUCTNAME, A.INVOICENAME, A.OPTFIELD, A.OPTVALUE, A.QTY, A.PRICE, A.SUMPRICE, A.STATUS, A.WEARRD_DT, B.NAME
				FROM TB_ORDER_ITEM A
				INNER JOIN TB_ORDER B ON B.ORDER_SEQ = A.ORDER_SEQ
				WHERE A.ITEMID = ?
				AND A.STATUS = 'D'
				AND B.CANCEL_YN <> 'Y'
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('data', $qry, $arParam);
			if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
			$ds = $result['data'];
			
			if(!$ds)  throw new Exception("유효한 아이템ID가 아닙니다");
			
			$result['data']['STATUS_STR'] = CODE_ORDER_STATUS($ds['STATUS']);
			$result['data']['WEARRD_DT_STR'] = str_replace(' ', '<br>', $ds['WEARRD_DT']);
		}
		else if($type == 'out'){
			// 출고
			$arParam = array();
			array_push( $arParam, $barcodeid);
			$qry = "
				SELECT A.DELIVERY_SEQ, A.DELIVERYID AS ID, A.`W_PRICE`, A.WEIGHT, C.GOODSRD_DT, SUM(C.QTY) AS QTY, SUM(C.PRICE) AS PRICE, SUM(C.SUMPRICE) AS SUMPRICE, C.STATUS
				FROM TB_DELIVERY A
				INNER JOIN TB_DELIVERY_ITEM B ON B.`DELIVERY_SEQ` = A.`DELIVERY_SEQ`
				INNER JOIN TB_ORDER_ITEM C ON C.ITEM_SEQ = B.ITEM_SEQ
				INNER JOIN TB_ORDER D ON D.ORDER_SEQ = C.ORDER_SEQ
				WHERE A.`DELIVERYID` = ?
				AND D.CANCEL_YN <> 'Y'
				AND C.STATUS = 'F'
				GROUP BY C.GOODSRD_DT, C.STATUS
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('data', $qry, $arParam);
			if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
			$ds = $result['data'];
			
			if(!$ds)  throw new Exception("유효한 출고ID가 아닙니다");
			
			$result['data']['STATUS_STR'] = CODE_ORDER_STATUS($ds['STATUS']);
			$result['data']['GOODSRD_DT_STR'] = str_replace(' ', '<br>', $ds['GOODSRD_DT']);
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