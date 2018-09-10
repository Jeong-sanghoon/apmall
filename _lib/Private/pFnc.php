<?
	/* 바코드 */
	require_once($_SERVER['DOCUMENT_ROOT'] .'/plugin/barcodegen/class/BCGFontFile.php');
	require_once($_SERVER['DOCUMENT_ROOT'] .'/plugin/barcodegen/class/BCGColor.php');
	require_once($_SERVER['DOCUMENT_ROOT'] .'/plugin/barcodegen/class/BCGDrawing.php');

	// Including the barcode technology
	require_once($_SERVER['DOCUMENT_ROOT'] .'/plugin/barcodegen/class/BCGcode128.barcode.php');
	require_once($_SERVER['DOCUMENT_ROOT'] .'/plugin/barcodegen/class/BCGcode39.barcode.php');
	/* 바코드 */
	
	// 모바일여부 체크
	function CHECK_MOBILE($param){
		$m_agent = array("iPhone","iPod","Android","Blackberry", "Opera Mini", "Windows ce", "Nokia", "sony");
		$rtn = false;
		
		for($i = 0; $i < sizeof($m_agent); $i++){
			if(stripos( $param, $m_agent[$i] )){
				$rtn = true;
				break;
			}
		}
		
		return $rtn;
	}
	
	
	// 주문상태이력
	// param : status, item_seq, item_rowid, old_status, reg_dt
	function SET_ORDER_STATUS_HIST($status, $item_seq, $item_rowid, $old_status = '', $reg_dt = ''){
		global $ARR_DB_INFO, $G_ERROR;
		global $cLog;
		global $S_SEQ;
		
		$cPdo = new cPdo($ARR_DB_INFO, $G_ERROR);
		
		if($reg_dt == '') $reg_dt = date('Y-m-d H:i:s');
		
		// 주문상태이력 등록
		$arParam = array();
		array_push( $arParam, $status);
		array_push( $arParam, $item_seq);
		array_push( $arParam, $item_rowid);
		array_push( $arParam, $old_status);
		array_push( $arParam, $reg_dt);
		array_push( $arParam, $S_SEQ);
		$qry = "
			INSERT INTO TB_ORDER_STATUS (`STATUS`, ITEM_SEQ, ITEM_ROWID, OLD_STATUS, REG_DT, ADM_SEQ)
			VALUES(
				?
				, ?
				, ?
				, ?
				, ?
				, ?
			)
		";
		$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
		$result = $cPdo->execQuery('insert', $qry, $arParam);
		
		return $result;
	}
	
	
	// 구매상태이력
	// param : status, purchase_seq, old_status, reg_dt
	function SET_ORDER_PURCHASE_STATUS_HIST($status, $purchase_seq, $reg_dt = '', $old_status = ''){
		global $ARR_DB_INFO, $G_ERROR;
		global $cLog;
		global $S_SEQ;
		
		$cPdo = new cPdo($ARR_DB_INFO, $G_ERROR);
		
		if($reg_dt == '') $reg_dt = date('Y-m-d H:i:s');
		
		// 주문상태이력 등록
		$arParam = array();
		array_push( $arParam, $status);
		array_push( $arParam, $purchase_seq);
		array_push( $arParam, $reg_dt);
		array_push( $arParam, $old_status);
		array_push( $arParam, $S_SEQ);
		$qry = "
			INSERT INTO TB_ORDER_PURCHASE_STATUS (`STATUS`, PURCHASE_SEQ, REG_DT, OLD_STATUS, ADM_SEQ)
			VALUES(
				?
				, ?
				, ?
				, ?
				, ?
			)
		";
		$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
		$result = $cPdo->execQuery('insert', $qry, $arParam);
		
		return $result;
	}
	
	
	/*
	바코드 이미지 생성
	prm:	$text		: 바코드번호
			$dir		: 파일저장경로

	return	return_msg
	*/
	function MAKE_BARCODE($text, $dir, $type){
		$font = new BCGFontFile($_SERVER['DOCUMENT_ROOT'] .'/font/NanumGothic.ttf', 10);
		
		$color_black = new BCGColor(0, 0, 0);
		$color_white = new BCGColor(255, 255, 255);

		$drawException = null;
		try {
			if($type == 'code39') $code = new BCGcode39();
			else if($type == 'code128') $code = new BCGcode128();
			
			$code->setScale(1); // Resolution
			$code->setThickness(30); // Thickness
			$code->setForegroundColor($color_black); // Color of bars
			$code->setBackgroundColor($color_white); // Color of spaces
			$code->setFont($font); // Font (or 0)			
			$code->parse($text); // Text		
		}
		catch( Exception $exception ) {
			$drawException = $exception;
		}
		
		$file = $text .'_'. $type .'.jpg';
		
		$drawing = new BCGDrawing($dir .'/'. $file, $color_white);
		if( $drawException ) {
			$drawing->drawException($drawException);
		}else{
			$drawing->setBarcode($code);
			$drawing->draw();
		}

		$drawing->finish(BCGDrawing::IMG_FORMAT_JPEG);
		
		return $file;
	}
?>