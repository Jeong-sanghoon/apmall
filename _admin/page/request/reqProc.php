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
	
	$usermstid = $cFnc->getReq('usermstid');
	$user_seq	= $cFnc->getReq('user_seq');
	$orderid	= $cFnc->getReq('orderid');
	$name	= $cFnc->getReq('name');
	$tel	= $cFnc->getReq('tel');
	$email	= $cFnc->getReq('email');
	$ord_dt	= $cFnc->getReq('ord_dt');
	$pricesum	= $cFnc->getReq('pricesum');
	$price_b	= $cFnc->getReq('price_b');
	$price_f	= $cFnc->getReq('price_f');
	$depositfeesum	= $cFnc->getReq('depositfeesum');
	$sales_seq	= $cFnc->getReq('sales_seq');
	$per	= $cFnc->getReq('per');
	$dst_addr	= $cFnc->getReq('dst_addr');
	$memo	= $cFnc->getReq('memo');
	$qty_sum	= $cFnc->getReq('qty_sum');
	$price_sum	= $cFnc->getReq('price_sum');
	$sumprice_sum	= $cFnc->getReq('sumprice_sum');
	$depositfee_sum	= $cFnc->getReq('depositfee_sum');
	$depositper	= $cFnc->getReq('depositper');
	
	$arr_price	= $cFnc->getReq('price');
	$arr_linkurl	= $cFnc->getReq('linkurl');
	$arr_category_seq	= $cFnc->getReq('category_seq');
	$arr_hscode	= $cFnc->getReq('hscode');
	$arr_productname	= $cFnc->getReq('productname');
	$arr_invoicename	= $cFnc->getReq('invoicename');
	$arr_optfield	= $cFnc->getReq('optfield');
	$arr_optvalue	= $cFnc->getReq('optvalue');
	$arr_qty	= $cFnc->getReq('qty');
	$arr_sumprice	= $cFnc->getReq('sumprice');
	$arr_depositfee	= $cFnc->getReq('depositfee');
	
	//$cFnc->echoArr($_REQUEST); exit;
	//echo json_encode($_REQUEST);exit;
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
			/* 등록 */
			
			// 주문서ID 체크
			$arParam = array();
			array_push( $arParam, $orderid);
			$qry = "SELECT COUNT(ORDER_SEQ) AS CNT FROM TB_ORDER WHERE ORDERID = ?";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('data', $qry, $arParam);
			if(!$result['status']) throw new Exception($result['msg'], 1001);
			$cnt = $result['data']['CNT'];
			
			if($cnt > 0) throw new Exception("동일한 주문번호가 존재합니다\n화면을 새로고침 하거나 관리자에게 문의하세요");
			
			
			// 시스템정보조회 : 베트남요율정보 가져오기
			$arParam = array();
			array_push( $arParam, $S_SYSTEM_CD);
			$qry = "SELECT * FROM TB_SYSTEM WHERE SYSTEM_CD = ?";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('data', $qry, $arParam);
			if(!$result['status']) throw new Exception($result['msg'], 1001);
			$dsSystem = $result['data'];
			$vnd_rate = $dsSystem['RATE'];
			
			
			if($user_seq == ''){
				// 유저데이터 등록 [보류]
				/*
				$arParam = array();
				array_push( $arParam, $email);
				array_push( $arParam, $name);
				array_push( $arParam, $tel);
				array_push( $arParam, $now_dt);
				array_push( $arParam, 'S');
				array_push( $arParam, $dst_addr);
				array_push( $arParam, $PWD);
				$qry = "
					INSERT INTO TB_USER ( USER_ID, USER_NM, TEL, REG_DT, USE_YN, JOIN_TP, ADDR, PWD )
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
				if(!$result['status'])	throw new Exception($result['msg'], 1001);
				*/
			}
			
			
			if($usermstid == ''){
				// 신청데이터 등록
				$userordid = "OM". date('YmdHis') . strtoupper($cFnc->GenerateRanStr(4, 'number'));		// 주문번호생성
				$qty = $qty_sum;
				$pricesum_vnd = $pricesum * $vnd_rate;
				$depositfeesum_vnd = $depositfeesum * $vnd_rate;
				
				$arParam = array();
				array_push( $arParam, $userordid);
				array_push( $arParam, $now_dt);
				array_push( $arParam, $name);
				array_push( $arParam, $email);
				array_push( $arParam, $tel);
				array_push( $arParam, $dst_addr);
				array_push( $arParam, $memo);
				array_push( $arParam, $qty);
				array_push( $arParam, $pricesum);
				array_push( $arParam, $depositfeesum);
				array_push( $arParam, 'E');
				array_push( $arParam, $email);
				array_push( $arParam, $user_seq);
				array_push( $arParam, $pricesum_vnd);
				array_push( $arParam, $depositfeesum_vnd);
				$qry = "
					INSERT INTO TB_ORDER_REQ ( USERORDID, REG_DT, NAME, EMAIL, TEL, DST_ADDR, MEMO, QTY, PRICESUM, DEPOSITFEE, STATUS, USER_ID, USER_SEQ, PRICESUM_VND, DEPOSITFEE_VND )
					VALUES (
						?
						,?
						,?
						,?
						,?
						,?
						,?
						,?
						,?
						,?
						,?
						,?
						,?
						,?
						,?
					)

				";
				$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
				$result = $cPdo->execQuery('insert', $qry, $arParam);
				if(!$result['status'])	throw new Exception($result['msg'], 1001);
				$usermstid = $result['data']['insert_id'];
				
				
				// 신청아이템데이터 등록
				$arParam = array();
				$rowid = 0;
				
				$qry = "INSERT INTO TB_ORDER_REQITEM ( LINKURL, ITEM_NM, OPTFIELD, OPTVALUE, QTY, PRICE, REG_DT, DEPOSITFEE, USERMSTID, ROWID, PRICE_VND, DEPOSITFEE_VND ) VALUES ";
				
				foreach($arr_price as $i=>$dsPrice){
					if($arr_linkurl[$i] != ''){
						$rowid++;
						
						array_push( $arParam, $arr_linkurl[$i]);
						array_push( $arParam, $arr_productname[$i]);
						array_push( $arParam, $arr_optfield[$i]);
						array_push( $arParam, $arr_optvalue[$i]);
						array_push( $arParam, $arr_qty[$i]);
						array_push( $arParam, $arr_price[$i]);
						array_push( $arParam, $now_dt);
						array_push( $arParam, $arr_depositfee[$i]);
						array_push( $arParam, $usermstid);
						array_push( $arParam, $rowid);
						array_push( $arParam, $arr_price[$i] * $vnd_rate);
						array_push( $arParam, $arr_depositfee[$i] * $vnd_rate);
						
						if($i == 0) $qry .= "(?,?,?,?,?,?,?,?,?,?,?,?)";
						else $qry .= ",(?,?,?,?,?,?,?,?,?,?,?,?)";
					}
				}
				
				$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
				$result = $cPdo->execQuery('insert', $qry, $arParam);
				if(!$result['status'])	throw new Exception($result['msg'], 1001);
			}
			
			
			// 주문서 등록
			$qty = $qty_sum;
			if($ord_dt == '') $ord_dt = $now_dt;
			
			$arParam = array();
			array_push( $arParam, $orderid);
			array_push( $arParam, $name);
			array_push( $arParam, $tel);
			array_push( $arParam, $email);
			array_push( $arParam, $ord_dt);
			array_push( $arParam, $pricesum);
			array_push( $arParam, $depositfeesum);
			array_push( $arParam, $price_f);
			array_push( $arParam, $price_b);
			array_push( $arParam, $now_dt);
			array_push( $arParam, $email);
			array_push( $arParam, $dst_addr);
			array_push( $arParam, $memo);
			array_push( $arParam, $qty);
			array_push( $arParam, 'A');
			array_push( $arParam, $usermstid);
			array_push( $arParam, $sales_seq);
			array_push( $arParam, $per);
			array_push( $arParam, $pricesum * $vnd_rate);
			array_push( $arParam, $depositfeesum * $vnd_rate);
			array_push( $arParam, $S_SEQ);
			array_push( $arParam, $depositper);
			
			$qry = "
				INSERT INTO TB_ORDER (ORDERID, NAME, TEL, EMAIL, ORD_DT, PRICE, DEPOSITFEE, PRICE_F, PRICE_B, REG_DT, USER_ID, DST_ADDR, MEMO, QTY, ORD_TYPE, USERMSTID, SALES_SEQ, PER, PRICE_VND, DEPOSITFEE_VND, ADM_SEQ, DEPOSIT_RATE)
				VALUES (
					?
					,?
					,?
					,?
					,?
					,?
					,?
					,?
					,?
					,?
					,?
					,?
					,?
					,?
					,?
					,?
					,?
					,?
					,?
					,?
					,?
					,?
				)

			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('insert', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			$order_seq = $result['data']['insert_id'];
			
			// 주문서아이템데이터 등록
			$rowid = 0;
			
			foreach($arr_price as $i=>$dsPrice){
				if($arr_linkurl[$i] != ''){
					$rowid++;
					$itemid = "OI". date('YmdHis') . strtoupper($cFnc->GenerateRanStr(4, 'number'));		// 주문번호생성
					
					$arParam = array();
					array_push( $arParam, $itemid);
					array_push( $arParam, $arr_linkurl[$i]);
					array_push( $arParam, $arr_productname[$i]);
					array_push( $arParam, $arr_invoicename[$i]);
					array_push( $arParam, $arr_optfield[$i]);
					array_push( $arParam, $arr_optvalue[$i]);
					array_push( $arParam, $arr_qty[$i]);
					array_push( $arParam, $arr_price[$i]);
					array_push( $arParam, $arr_sumprice[$i]);
					array_push( $arParam, $arr_depositfee[$i]);
					array_push( $arParam, 'A');
					array_push( $arParam, $order_seq);
					array_push( $arParam, $now_dt);
					array_push( $arParam, $arr_category_seq[$i]);
					array_push( $arParam, $rowid);
					array_push( $arParam, $arr_hscode[$i]);
					array_push( $arParam, $arr_price[$i] * $vnd_rate);
					array_push( $arParam, $arr_sumprice[$i] * $vnd_rate);
					array_push( $arParam, $arr_depositfee[$i] * $vnd_rate);
					array_push( $arParam, $S_SEQ);
					
					$qry = "
						INSERT INTO TB_ORDER_ITEM ( ITEMID, LINKURL, PRODUCTNAME, INVOICENAME, OPTFIELD, OPTVALUE, QTY, PRICE, SUMPRICE, DEPOSITFEE, STATUS, ORDER_SEQ, REG_DT, CATEGORY_SEQ, ITEM_ROWID, HSCODE_SEQ, PRICE_VND, SUMPRICE_VND, DEPOSITFEE_VND, ADM_SEQ )
						VALUES (
							?
							,?
							,?
							,?
							,?
							,?
							,?
							,?
							,?
							,?
							,?
							,?
							,?
							,?
							,?
							,?
							,?
							,?
							,?
							,?
						)
					";
					$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
					$result = $cPdo->execQuery('insert', $qry, $arParam);
					if(!$result['status'])	throw new Exception($result['msg'], 1001);
					$item_seq = $result['data']['insert_id'];
					
					// 주문상태이력 등록
					// param : status, item_seq, item_rowid, old_status, reg_dt
					$result = SET_ORDER_STATUS_HIST('A', $item_seq, $rowid, '', $now_dt);
					if(!$result['status']) throw new Exception($result['msg'], 1001);
				}
			}
			
			// 기존 임시데이터 삭제
			$arParam = array();
			array_push($arParam, $S_SEQ);
			$qry = "DELETE FROM TB_ORDER_TEMP WHERE ADM_SEQ = ?";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			
			$arParam = array();
			array_push($arParam, $S_SEQ);
			$qry = "DELETE FROM TB_ORDER_ITEM_TEMP WHERE ADM_SEQ = ?";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			
			
			$result['msg'] = "주문신청이 완료되었습니다";
			$result['url'] = "";
		}
		else if($pageaction == 'temp'){
			/* 임시저장 */
			
			// 기존 임시데이터 삭제
			$arParam = array();
			array_push($arParam, $S_SEQ);
			$qry = "DELETE FROM TB_ORDER_TEMP WHERE ADM_SEQ = ?";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			
			$arParam = array();
			array_push($arParam, $S_SEQ);
			$qry = "DELETE FROM TB_ORDER_ITEM_TEMP WHERE ADM_SEQ = ?";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			
			
			// 주문서 등록
			$qty = $qty_sum;
			if($ord_dt == '') $ord_dt = $now_dt;
			
			$arParam = array();
			array_push( $arParam, $name);
			array_push( $arParam, $tel);
			array_push( $arParam, $email);
			array_push( $arParam, $ord_dt);
			array_push( $arParam, $pricesum);
			array_push( $arParam, $depositfeesum);
			array_push( $arParam, $price_f);
			array_push( $arParam, $price_b);
			array_push( $arParam, $now_dt);
			array_push( $arParam, $email);
			array_push( $arParam, $dst_addr);
			array_push( $arParam, $memo);
			array_push( $arParam, $qty);
			array_push( $arParam, 'A');
			array_push( $arParam, $usermstid);
			array_push( $arParam, $sales_seq);
			array_push( $arParam, $per);
			array_push( $arParam, $pricesum * $vnd_rate);
			array_push( $arParam, $depositfeesum * $vnd_rate);
			array_push( $arParam, $S_SEQ);
			array_push( $arParam, $depositper);
			$qry = "
				INSERT INTO TB_ORDER_TEMP (NAME, TEL, EMAIL, ORD_DT, PRICE, DEPOSITFEE, PRICE_F, PRICE_B, REG_DT, USER_ID, DST_ADDR, MEMO, QTY, ORD_TYPE, USERMSTID, SALES_SEQ, PER, PRICE_VND, DEPOSITFEE_VND, ADM_SEQ, DEPOSIT_RATE)
				VALUES (
					?
					,?
					,?
					,?
					,?
					,?
					,?
					,?
					,?
					,?
					,?
					,?
					,?
					,?
					,?
					,?
					,?
					,?
					,?
					,?
					,?
				)

			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('insert', $qry, $arParam);
			if(!$result['status'])	throw new Exception($result['msg'], 1001);
			$order_seq = $result['data']['insert_id'];
			
			// 주문서아이템데이터 등록
			$rowid = 0;
			foreach($arr_price as $i=>$dsPrice){
				$rowid++;
				$arParam = array();
				array_push( $arParam, $arr_linkurl[$i]);
				array_push( $arParam, $arr_productname[$i]);
				array_push( $arParam, $arr_invoicename[$i]);
				array_push( $arParam, $arr_optfield[$i]);
				array_push( $arParam, $arr_optvalue[$i]);
				array_push( $arParam, $arr_qty[$i]);
				array_push( $arParam, $arr_price[$i]);
				array_push( $arParam, $arr_sumprice[$i]);
				array_push( $arParam, $arr_depositfee[$i]);
				array_push( $arParam, 'A');
				array_push( $arParam, $order_seq);
				array_push( $arParam, $now_dt);
				array_push( $arParam, $arr_category_seq[$i]);
				array_push( $arParam, $arr_hscode[$i]);
				array_push( $arParam, $arr_price[$i] * $vnd_rate);
				array_push( $arParam, $arr_sumprice[$i] * $vnd_rate);
				array_push( $arParam, $arr_depositfee[$i] * $vnd_rate);
				array_push( $arParam, $S_SEQ);
				array_push( $arParam, $rowid);
				
				$qry = "
					INSERT INTO TB_ORDER_ITEM_TEMP ( LINKURL, PRODUCTNAME, INVOICENAME, OPTFIELD, OPTVALUE, QTY, PRICE, SUMPRICE, DEPOSITFEE, STATUS, ORDER_SEQ, REG_DT, CATEGORY_SEQ, HSCODE_SEQ, PRICE_VND, SUMPRICE_VND, DEPOSITFEE_VND, ADM_SEQ, ROWID )
					VALUES (
						?
						,?
						,?
						,?
						,?
						,?
						,?
						,?
						,?
						,?
						,?
						,?
						,?
						,?
						,?
						,?
						,?
						,?
						,?
					)
				";
				$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
				$result = $cPdo->execQuery('insert', $qry, $arParam);
				if(!$result['status'])	throw new Exception($result['msg'], 1001);
			}
			
			$result['msg'] = "임시저장이 완료되었습니다";
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
