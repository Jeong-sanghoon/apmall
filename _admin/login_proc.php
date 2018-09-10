<?
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	$id = $cFnc->getReq('id');
	$pw = $cFnc->getReq('pw');
	
	$result = array();
	$result['status'] = 1;
	$result['msg'] = "";
	$result['url'] = "/_admin";
	
	$cPdo = new cPdo($ARR_DB_INFO);
	
	try{
		// 아이디, 비밀번호 체크
		$arParam = array();
		array_push($arParam, $id);
		$qry = "SELECT ADM_SEQ, ADM_ID, ADM_PW, ADM_NM, GRADE, USE_YN, SYSTEM_CD FROM TB_ADM WHERE ADM_ID = ?";
		$arrCnt = $cPdo->getCntExec($qry, $arParam);
		$nTotalCnt = $arrCnt['data']['total'];
		$nTotalPage = $arrCnt['data']['page'];
		
		if($nTotalCnt < 1){
			throw new Exception('ID가 존재하지 않습니다');
		}
		
		array_push($arParam, $pw);
		$qry .= " AND ADM_PW = password(?)";
		$rslt = $cPdo->execQuery('data', $qry, $arParam);
		$ds = $rslt['data'];
		
		if(!$ds){
			throw new Exception('비밀번호가 맞지 않습니다');
		}
		
		
		session_start();
		
		$_SESSION['ADM_SEQ'] = $ds['ADM_SEQ'];
		$_SESSION['ADM_ID'] = $ds['ADM_ID'];
		$_SESSION['ADM_NM'] = $ds['ADM_NM'];
		$_SESSION['SYSTEM_CD'] = $ds['SYSTEM_CD'];
		$_SESSION['GRADE'] = $ds['GRADE'];

		if($ds['GRADE'] == 'U'){
			$result['url'] = "/_admin/page/request/reqInput_vt.php";
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