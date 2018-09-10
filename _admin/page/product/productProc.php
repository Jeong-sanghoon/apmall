<?
	// ===================================================
	// include
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSessionAjax($url = '/_admin/');
	
	// ===================================================
	// get parameter
	// ===================================================
	$pageaction 		= $cFnc->getReq('pageaction');		
	$product_seq 		= $cFnc->getReq("product_seq");
	
	$PRODUCT_SEQ 		= $cFnc->getReq("PRODUCT_SEQ");
	$MANUFACTURE_SEQ 	= $cFnc->getReq("MANUFACTURE_SEQ");
	// $PRODUCTID 			= $cFnc->getReq("PRODUCTID");
	$PNAME 				= $cFnc->getReq("PNAME");
	$MANUFACTURE 		= $cFnc->getReq("MANUFACTURE");
	$STOCK_YN 			= $cFnc->getReq("STOCK_YN");
	$STORAGE_SEQ 		= $cFnc->getReq("STORAGE_SEQ");
	$LINKURL 			= $cFnc->getReq("LINKURL");
	$P_STATUS 			= $cFnc->getReq("P_STATUS");
	$CATEGORY_SEQ 		= $cFnc->getReq("CATEGORY_SEQ");
	$PINVOICENAME 		= $cFnc->getReq("PINVOICENAME");
	
	$ARRSTORAGE_SEQ		= $cFnc->getReq("arrstorage_seq");
	$ARRQTY 			= $cFnc->getReq("arrqty");
	

	// echo $ARRSTORAGE_SEQ."<Br>";
	//var_dump($ARRSTORAGE_SEQ);
	
	// echo $ARRQTY."<Br>";
	// foreach($arrStorage_seq as $SEQ){
	// 	echo $SEQ."<Br>";
	// }
	// exit;


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
			$cPdo->tran();
			
			
			$PRODUCTID = $cFnc->getCodeGen('PD', 4);

			$arParam = array();
			array_push( $arParam, $MANUFACTURE_SEQ);
			array_push( $arParam, $PRODUCTID);
			array_push( $arParam, $PNAME);
			array_push( $arParam, $MANUFACTURE);
			array_push( $arParam, $STOCK_YN);
			array_push( $arParam, $STORAGE_SEQ);
			array_push( $arParam, $LINKURL);
			array_push( $arParam, $P_STATUS);
			array_push( $arParam, $CATEGORY_SEQ);
			array_push( $arParam, $PINVOICENAME);
			$qry = "
				INSERT INTO TB_PRODUCT ( MANUFACTURE_SEQ, PRODUCTID, PNAME, REG_DT, MOD_DT, MANUFACTURE, STOCK_YN, STORAGE_SEQ, LINKURL, P_STATUS, CATEGORY_SEQ, PINVOICENAME )
				VALUES (
				?				
				,?
				,?
				,now()
				,now()
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
			$insert_id = $result['data']['insert_id'];			
			if(!$result['status'])	throw new Exception('입력중 오류가 발생했습니다');
			if(!$insert_id)	throw new Exception('입력중 오류가 발생했습니다');


			if($STOCK_YN == "Y"){
				$arParam = array();
				$arrStorage_seq = explode(',', $ARRSTORAGE_SEQ);
				$arrqty 		= explode(',', $ARRQTY);	

				// SubQuery Start
				$qry = "
				INSERT INTO TB_PRODUCT_STOCK ( STORAGE_SEQ, QTY, REG_DT, PRODUCT_SEQ )
				VALUES ";
				
				foreach($arrStorage_seq as $ss=>$arrStorage_seq){					
					$qty = $arrqty[$ss];

					
					array_push( $arParam, $arrStorage_seq);
					array_push( $arParam, $qty);
					array_push( $arParam, $insert_id);

					$qry .= "( ? ,?	,now() ,? ),";
				}
				
				$qry = substr($qry, 0, strlen($qry) - 1);
				$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
				$result = $cPdo->execQuery('insert', $qry, $arParam);
				if(!$result['status'])	throw new Exception('입력중 오류가 발생했습니다');
			}
			
			$result['msg'] = "입력이 완료되었습니다";
			$result['url'] = "productList.php";

			$cPdo->commit();
		}
		else if($pageaction == 'UPDATE'){
			if($product_seq == '') throw new Exception('잘못된 접근입니다');
			
			$cPdo->tran();
			

			// 강제 업데이트
			$arParam = array();
			array_push( $arParam, $product_seq);
			$qry = "
				DELETE FROM TB_PRODUCT_STOCK WHERE PRODUCT_SEQ = ?
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$cPdo->execQuery('update', $qry, $arParam);



			$arParam = array();			
			array_push( $arParam, $MANUFACTURE_SEQ);			
			array_push( $arParam, $PNAME);			
			array_push( $arParam, $MANUFACTURE);
			array_push( $arParam, $STOCK_YN);
			array_push( $arParam, $STORAGE_SEQ);
			array_push( $arParam, $LINKURL);
			array_push( $arParam, $P_STATUS);
			array_push( $arParam, $CATEGORY_SEQ);
			array_push( $arParam, $PINVOICENAME);

			array_push($arParam, $product_seq);
			$qry = "
				UPDATE TB_PRODUCT SET 
				MANUFACTURE_SEQ = ?				
				, PNAME = ?				
				, MOD_DT = now()
				, MANUFACTURE = ?
				, STOCK_YN = ?
				, STORAGE_SEQ = ?
				, LINKURL = ?
				, P_STATUS = ?
				, CATEGORY_SEQ = ?
				, PINVOICENAME = ?
				WHERE PRODUCT_SEQ = ?
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception('수정중 오류가 발생했습니다');


			if($STOCK_YN == "Y"){
				$arParam = array();
				$arrStorage_seq = explode(',', $ARRSTORAGE_SEQ);
				$arrqty 		= explode(',', $ARRQTY);	

				// SubQuery Start
				$qry = "
				INSERT INTO TB_PRODUCT_STOCK ( STORAGE_SEQ, QTY, REG_DT, PRODUCT_SEQ )
				VALUES ";
				
				foreach($arrStorage_seq as $ss=>$arrStorage_seq){					
					$qty = $arrqty[$ss];

					
					array_push( $arParam, $arrStorage_seq);
					array_push( $arParam, $qty);
					array_push( $arParam, $product_seq);

					$qry .= "( ? ,?	,now() ,? ),";
				}
				
				$qry = substr($qry, 0, strlen($qry) - 1);
				$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
				$result = $cPdo->execQuery('insert', $qry, $arParam);
				if(!$result['status'])	throw new Exception('입력중 오류가 발생했습니다');
			}

			$result['msg'] = "수정이 완료되었습니다";
			$result['url'] = "productView.php";

			$cPdo->commit();
		}
		else if($pageaction == 'DELETE'){
			if($manufacture_seq == '') throw new Exception('잘못된 접근입니다');
			
			$result['msg'] = "삭제되었습니다";
			$result['url'] = "manufactureList.php";
			
			$arParam = array();
			array_push($arParam, "N");
			array_push($arParam, $manufacture_seq);
			$qry = "UPDATE TB_MANUFACTURE SET USE_YN = ?, MOD_DT = NOW() WHERE MANUFACTURE_SEQ = ?";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result['status'] = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception('삭제중 오류가 발생했습니다');
		}
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
