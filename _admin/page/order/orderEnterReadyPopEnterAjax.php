<?
	// ===================================================
	// include
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSessionAjax($url = '/_admin/');
	
	// ===================================================
	// get parameter
	// ===================================================
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
		
		$arParam = array();
		array_push( $arParam, $barcodeid);
		array_push( $arParam, $barcodeid);
		$qry = "
			SELECT B.ITEM_SEQ, B.ITEM_ROWID, B.ITEMID, B.PRODUCTNAME
			, A.PURCHASE_SEQ, A.P_LINKURL, A.P_QTY, A.P_PRICE, A.P_DELIVERYFEE, A.P_PRICESUM, A.P_OPTFIELD, A.`P_OPTVALUE`, A.`P_MEMO`, A.P_STATUS, A.REG_DT, A.MOD_DT, A.P_DISCOUNT, A.DELIVER_NO, A.PAYMENT_TP, A.APPROVAL_NO
			, C.`050_NO`, C.`SEQ` AS 050_SEQ
			FROM TB_ORDER_PURCHASE A
			INNER JOIN TB_ORDER_ITEM B ON B.ITEM_SEQ = A.ITEM_SEQ
			LEFT OUTER JOIN TB_ORDER_PURCHASE_050 C ON C.`PURCHASE_SEQ` = A.`PURCHASE_SEQ`
			WHERE A.DELIVER_NO = ? OR C.050_NO = ?
		";
		$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
		$result = $cPdo->execQuery('data', $qry, $arParam);
		if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
		$ds = $result['data'];
		
		if(!$ds) throw new Exception("유효한 운송장번호/050번호가 아닙니다");
		
		$result['data']['REG_DT_STR'] = str_replace(' ', '<br>', $ds['REG_DT']);
		
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