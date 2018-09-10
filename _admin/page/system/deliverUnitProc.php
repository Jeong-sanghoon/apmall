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
	$unit_seq	= $cFnc->getReq('unit_seq');
	
	$l_weight	= $cFnc->getReq('l_weight');
	$h_weight	= $cFnc->getReq('h_weight');
	$price		= $cFnc->getReq('price');
	$up_unit	= $cFnc->getReq('up_unit');
	$use_yn		= $cFnc->getReq('use_yn');
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
		if($pageaction == 'INSERT'){
			$arParam = array();
			array_push($arParam, $S_SEQ);
			array_push($arParam, $l_weight);
			array_push($arParam, $h_weight);
			array_push($arParam, $price);
			array_push($arParam, $up_unit);
			array_push($arParam, $use_yn);
			array_push($arParam, $now_dt);
			$qry = "
				INSERT INTO TB_DELIVER_UNIT (ADM_SEQ, L_WEIGHT, H_WEIGHT, PRICE, UP_UNIT, USE_YN, REG_DT)
				VALUES(
					?
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
			if(!$result['status'])	throw new Exception('입력중 오류가 발생했습니다');
			
			$result['msg'] = "입력이 완료되었습니다";
			$result['url'] = "deliverUnitList.php";
		}
		else if($pageaction == 'UPDATE'){
			if($unit_seq == '') throw new Exception('잘못된 접근입니다');
			
			$arParam = array();
			array_push($arParam, $S_SEQ);
			array_push($arParam, $l_weight);
			array_push($arParam, $h_weight);
			array_push($arParam, $price);
			array_push($arParam, $up_unit);
			array_push($arParam, $use_yn);
			array_push($arParam, $now_dt);
			array_push($arParam, $unit_seq);
			$qry = "
				UPDATE TB_DELIVER_UNIT SET
				ADM_SEQ = ?
				, L_WEIGHT = ?
				, H_WEIGHT = ?
				, PRICE = ?
				, UP_UNIT = ?
				, USE_YN = ?
				, MOD_DT = ?
				WHERE UNIT_SEQ = ?
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result['status'] = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception('수정중 오류가 발생했습니다');
			
			$result['msg'] = "수정이 완료되었습니다";
			$result['url'] = "deliverUnitView.php?unit_seq=". $unit_seq;
		}
		else if($pageaction == 'DELETE'){
			if($unit_seq == '') throw new Exception('잘못된 접근입니다');
			
			$arParam = array();
			array_push($arParam, $unit_seq);
			$qry = "DELETE FROM TB_DELIVER_UNIT WHERE UNIT_SEQ = ?";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result['status'] = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception('삭제중 오류가 발생했습니다');
			
			$result['msg'] = "삭제되었습니다";
			$result['url'] = "deliverUnitList.php";
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
