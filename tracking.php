<?
	include_once $_SERVER['DOCUMENT_ROOT'] .'/_lib/Conf/Config.php';
	
	//=====================================================
	//== 도움말 - Get Parameters
	//=====================================================
	$param5		= $oVAR->getVar_d('param5',Variable::VARIABLE_ALL,'string');
	$param9		= $oVAR->getVar_d('param9',Variable::VARIABLE_ALL,'string');
	$param10	= $oVAR->getVar_d('param10',Variable::VARIABLE_ALL,'string');
	$paramRe	= $oVAR->getVar_d('paramRe',Variable::VARIABLE_ALL,'string');
	
	$param1 = "N";
	$param2 = session_id();
	$param3 = "";
	$param4 = $_SERVER['SERVER_NAME'];
	$param5 = $param5;
	$param6 = $_SERVER['REMOTE_ADDR'];
	$param7 = "";
	$param8 = $_SERVER['HTTP_USER_AGENT'];
	$param9 = $param9;
	$param10 = $param10;
	$paramReffer = $paramRe;
	
	$param11 = "";
	$param12 = "";
	
	if ($paramReffer != "") {
		$arrReffer = explode("/",$paramReffer);
		
		for($i=0; $i < sizeof($arrReffer); $i++){
			if ($i == 2) {
				$REFFERDOMAIN = $arrReffer[0]."//".$arrReffer[$i];
			}
			if ($i > 2) {
				$REFFERPAGE_TOTAL = $REFFERPAGE_TOTAL."/".$arrReffer[$i];
			}
		}
		$param11 = $REFFERDOMAIN;
		$param12 = $REFFERPAGE_TOTAL;
	}
	
	if($param8){
		$param8  = substr($param8, 0, 800);
	}
	if($param10){
		$param10 = substr($param10, 0, 800);
	}
	if($param11){
		$param11 = substr($param11, 0, 400);
	}
	if($param12){
		$param12 = substr($param12, 0, 200);
	}
	
	//echo '<br>param4 == '. $param4;
	//echo '<br>param1 == '. $param1;
	//echo '<br>param2 == '. $param2;
	//echo '<br>param3 == '. $param3;
	//echo '<br>param5 == '. $param5;
	//echo '<br>param6 == '. $param6;
	//echo '<br>param7 == '. $param7;
	//echo '<br>param8 == '. $param8;
	//echo '<br>param9 == '. $param9;
	//echo '<br>param10 == '. $param10;
	//echo '<br>param11 == '. $param11;
	//echo '<br>param12 == '. $param12;
	
	$oMySQL = new cMySQLi($arDbInfo, $G_ERROR);
	$arParam = Array();
	
	$strQuery = " INSERT INTO N_TRAFFIC (HTTP_HOST, USER_SESSION_TYPE, USER_SESSION_ID, USER_LOGIN_SESSION_ID, URL, REMOTE_HOST, HTTP_COOKIE, HTTP_USER_AGENT, HTTPS, QUERY_STRING, HTTP_REFERER, HTTP_REFERER_PAGE, REG_DATE, REG_DATE_YMD)";
	$strQuery .= " VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), curdate())";
	$arParam[0] = $param4;
	$arParam[1] = $param1;
	$arParam[2] = $param2;
	$arParam[3] = $param3;
	$arParam[4] = $param5;
	$arParam[5] = $param6;
	$arParam[6] = $param7;
	$arParam[7] = $param8;
	$arParam[8] = $param9;
	$arParam[9] = $param10;
	$arParam[10] = $param11;
	$arParam[11] = $param12;
	$result = $oMySQL->exec($strQuery,$arParam,'insert');
	
	if(is_object($oMySQL))$oMySQL->close();
?>