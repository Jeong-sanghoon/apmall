<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSessionAjax($url = '/_admin/');
	
	//=====================================================
	//== 도움말 - Get Parameters
	//=====================================================
	$arr_orderid = $cFnc->getReq('arr_orderid', '');
	$arr_itemid = $cFnc->getReq('arr_itemid', '');
	$barcode_type = $cFnc->getReq('barcode_type', '');
	
	$arr_deliveryid = $cFnc->getReq('arr_deliveryid', '');
	$barcode_step = $cFnc->getReq('barcode_step', '');

	// =====================================================
	// Set Variables
	// =====================================================
	$cPdo = new cPdo($ARR_DB_INFO);
	
	$strUpPath = UPLOAD_DIR ."/barcode";
	$strUpPathLoc = date('Ym');
	$strUpPathFull = $strUpPath ."/". $strUpPathLoc;
	
	$rtn = array();
	
	// =====================================================
	// Start Tran
	// =====================================================
	/* 바코드생성 */
	$cPdo->tran();
	
	try{
		if($barcode_step == 'enter'){
			// 폴더생성
			if(!is_dir($strUpPathFull)){
				mkdir($strUpPathFull);
				chmod($strUpPathFull, 0777);
			}
			
			foreach($arr_orderid as $i=>$orderid){
				$itemid = $arr_itemid[$i];
				
				$arParam = array();
				array_push($arParam, $itemid);
				$qry = "
					SELECT BARCODE_39_URL, BARCODE_128_URL FROM TB_ORDER_ITEM WHERE ITEMID = ?
				";
				$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
				$result = $cPdo->execQuery('data', $qry, $arParam);
				if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
				
				if($barcode_type == 'code39'){
					$barcodefile = $result['data']['BARCODE_39_URL'];
				}
				else{
					$barcodefile = $result['data']['BARCODE_128_URL'];
				}
				
				if($barcodefile == ''){
					$barcodefile = MAKE_BARCODE($itemid, $strUpPathFull, $barcode_type);
					
					$barcodefile = $strUpPathLoc .'/'. $barcodefile;
					
					$arParam = array();
					
					if($barcode_type == 'code39'){
						$qryAdd = " BARCODE_39_URL = ?";
					}
					else{
						$qryAdd = " BARCODE_128_URL = ?";
					}
					
					array_push($arParam, $barcodefile);
					array_push($arParam, $itemid);
					$qry = "
						UPDATE TB_ORDER_ITEM SET
						". $qryAdd ."
						WHERE ITEMID = ?
					";
					$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
					$result = $cPdo->execQuery('update', $qry, $arParam);
					if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
				}
				
				unset($result['data']);
				
				$rtn['data'][$i]['orderid'] = $orderid;
				$rtn['data'][$i]['itemid'] = $itemid;
				$rtn['data'][$i]['barcodefile'] = $barcodefile;
			}
		}
		else if($barcode_step == 'out'){
			// 폴더생성
			if(!is_dir($strUpPathFull)){
				mkdir($strUpPathFull);
				chmod($strUpPathFull, 0777);
			}
			
			foreach($arr_deliveryid as $i=>$deliveryid){
				$arParam = array();
				array_push($arParam, $deliveryid);
				$qry = "
					SELECT BARCODE_39_URL, BARCODE_128_URL, DELIVERY_SEQ FROM TB_DELIVERY WHERE DELIVERYID = ?
				";
				$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
				$result = $cPdo->execQuery('data', $qry, $arParam);
				if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
				
				if($barcode_type == 'code39'){
					$barcodefile = $result['data']['BARCODE_39_URL'];
				}
				else{
					$barcodefile = $result['data']['BARCODE_128_URL'];
				}
				
				$delivery_seq = $result['data']['DELIVERY_SEQ'];
				
				if($barcodefile == ''){
					$barcodefile = MAKE_BARCODE($deliveryid, $strUpPathFull, $barcode_type);
					
					$barcodefile = $strUpPathLoc .'/'. $barcodefile;
					
					$arParam = array();
					
					if($barcode_type == 'code39'){
						$qryAdd = " BARCODE_39_URL = ?";
					}
					else{
						$qryAdd = " BARCODE_128_URL = ?";
					}
					
					array_push($arParam, $barcodefile);
					array_push($arParam, $deliveryid);
					$qry = "
						UPDATE TB_DELIVERY SET
						". $qryAdd ."
						WHERE DELIVERYID = ?
					";
					$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
					$result = $cPdo->execQuery('update', $qry, $arParam);
					if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
				}
				
				$arParam = array();
				array_push($arParam, $delivery_seq);
				$qry = "
					SELECT C.ORDERID
					FROM TB_DELIVERY_ITEM A
					INNER JOIN TB_ORDER_ITEM B ON B.ITEM_SEQ = A.ITEM_SEQ
					INNER JOIN TB_ORDER C ON C.ORDER_SEQ = B.ORDER_SEQ
					WHERE A.`DELIVERY_SEQ` = ?
					GROUP BY C.ORDERID
				";
				$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
				$result = $cPdo->execQuery('list', $qry, $arParam);
				if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
				$rsOrder = $result['data'];
				//echo json_encode($rsOrder);exit;
				
				$arParam = array();
				array_push($arParam, $delivery_seq);
				$qry = "
					SELECT C.ORDERID, B.`ITEMID`
					FROM TB_DELIVERY_ITEM A
					INNER JOIN TB_ORDER_ITEM B ON B.ITEM_SEQ = A.ITEM_SEQ
					INNER JOIN TB_ORDER C ON C.ORDER_SEQ = B.ORDER_SEQ
					WHERE A.`DELIVERY_SEQ` = ?
				";
				$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
				$result = $cPdo->execQuery('list', $qry, $arParam);
				if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
				$rsItem = $result['data'];
				
				unset($result['data']);
				
				$rtn['data'][$i]['deliveryid'] = $deliveryid;
				$rtn['data'][$i]['barcodefile'] = $barcodefile;
				
				foreach($rsOrder as $j=>$dsOrder){
					$idx = 0;
					$rtn['data'][$i]['order'][$j]['orderid'] = $dsOrder['ORDERID'];
					
					foreach($rsItem as $k=>$dsItem){
						if($dsOrder['ORDERID'] == $dsItem['ORDERID']){
							$rtn['data'][$i]['order'][$j]['item'][$idx] = $dsItem['ITEMID'];
							$idx++;
						}
					}
				}
			}
		}
		
		$rtn['status'] = 1;
	}
	catch(Exception $e){
		$cPdo->rollback();
		$rtn['status'] = 0;
		$rtn['msg'] = $e->getMessage();
		if($e->getCode() != '')	$rtn['msg'] .= ' // '. $e->getCode();
		$rtn['url'] = "";
	}
	
	$cPdo->commit();
	$cPdo->close();
	
	echo json_encode($rtn);
	exit;
?>
