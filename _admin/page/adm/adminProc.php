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
	$adm_seq	= $cFnc->getReq('adm_seq');
	
	$ADM_ID 	= $cFnc->getReq("ADM_ID");
	$ADM_PW 	= $cFnc->getReq("ADM_PW");
	$REG_DT 	= $cFnc->getReq("REG_DT");
	$MOD_DT 	= $cFnc->getReq("MOD_DT");
	$GRADE 		= $cFnc->getReq("GRADE");
	$SYSTEM_CD 	= $cFnc->getReq("SYSTEM_CD");
	$ADM_NM 	= $cFnc->getReq("ADM_NM");
	$TEL 		= $cFnc->getReq("TEL");
	$EMAIL 		= $cFnc->getReq("EMAIL");
	$USE_YN 	= $cFnc->getReq("USE_YN");
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
			$arParam = array();			
			array_push( $arParam, $ADM_ID);
			array_push( $arParam, $ADM_PW);			
			array_push( $arParam, $GRADE);
			array_push( $arParam, $SYSTEM_CD);
			array_push( $arParam, $ADM_NM);
			array_push( $arParam, $TEL);
			array_push( $arParam, $EMAIL);			
			$qry = "
				INSERT INTO TB_ADM ( ADM_ID, ADM_PW, REG_DT, MOD_DT, GRADE, SYSTEM_CD, ADM_NM, TEL, EMAIL, USE_YN )
				VALUES (
				?				
				,password(?)
				,now()
				,now()
				,?
				,?
				,?
				,?
				,?
				,'Y'
				)
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('insert', $qry, $arParam);
			if(!$result['status'])	throw new Exception('입력중 오류가 발생했습니다');
			
			$result['msg'] = "입력이 완료되었습니다";
			$result['url'] = "adminList.php";
		}
		else if($pageaction == 'UPDATE'){
			if($adm_seq == '') throw new Exception('잘못된 접근입니다');
			
			$arParam = array();
			array_push( $arParam, $GRADE);
			array_push( $arParam, $SYSTEM_CD);
			array_push( $arParam, $ADM_NM);
			array_push( $arParam, $TEL);
			array_push( $arParam, $EMAIL);			
			
			if($ADM_PW != ''){
				$qryAdd .= ", ADM_PW = password(?)";
				array_push($arParam, $ADM_PW);
			}
			
			array_push($arParam, $adm_seq);
			$qry = "
				UPDATE TB_ADM SET
				MOD_DT = now()
				, GRADE = ?
				, SYSTEM_CD = ?
				, ADM_NM = ?
				, TEL = ?
				, EMAIL = ?
				". $qryAdd ."
				WHERE ADM_SEQ = ?
			";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception('수정중 오류가 발생했습니다');
			
			$result['msg'] = "수정이 완료되었습니다";
			$result['url'] = "adminView.php";
		}
		else if($pageaction == 'DELETE'){
			if($adm_seq == '') throw new Exception('잘못된 접근입니다');
			
			$arParam = array();
			array_push($arParam, "N");
			array_push($arParam, $adm_seq);
			$qry = "UPDATE TB_ADM SET USE_YN = ?, MOD_DT = NOW() WHERE ADM_SEQ = ?";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$result = $cPdo->execQuery('update', $qry, $arParam);
			if(!$result['status'])	throw new Exception('삭제중 오류가 발생했습니다');
			
			$result['msg'] = "삭제되었습니다";
			$result['url'] = "adminList.php";
		}
		else if($pageaction == 'IDSEARCH'){
			if($ADM_ID == '') throw new Exception('잘못된 접근입니다');
			
			$arParam = array();			
			array_push($arParam, $ADM_ID);
			$qry = "SELECT COUNT(*) AS CNT FROM TB_ADM WHERE ADM_ID = ?";
			$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
			$dsCnt = $cPdo->execQuery('data', $qry, $arParam);
			$cnt = $dsCnt['data']['CNT'];
			if($cnt > 0) throw new Exception('이미 존재하는 아이디 입니다');
			
			$result['msg'] = "사용가능합니다.";			
			$result['status'] = 0;
			$result['url'] = "";
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
