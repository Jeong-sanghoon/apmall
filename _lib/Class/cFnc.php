<?php
/**
 *  Description : 사용자 함수 모음
 */
class cFnc{
	
	// ===================================================
	// String Function
	// ===================================================
	
	// 유효성 체크
	public function chkPatternValidation($strMsg, $mode){

		$strPattern['email'] = "(^[_\.0-9a-z-]+)@(([0-9a-z][0-9a-z-]+\.)+)([a-z]{2,3}$)";//이메일
		$strPattern['tel'] = "(^[0-9]{2,3}-[0-9]{3,4}-[0-9]{4}$)";//전화(일반/휴대폰)
		
		if(preg_match($strPattern[$mode],$strMsg)){
			return true;
		}
		else{
			return false;
		}
	}

	
	// 배열로 만들기
	public function setUTFArray($str){
		$new_arr = array();
		$iCnt =0;
		$i=0;
		$iLen = strLen($str);
		$isCutCode="";
		
		for($i;$i<$iLen;$i++){
			$iCut = -1;
			$iNum = -1;
			$ch = sprintf('%08b',ord($str{$i}));
			if(strpos($ch,'0')===0){$iCut = 1;$iNum = 0;
			$isCutCode = "...";
			}else if(strpos($ch,'110')===0){$iCut = 2;$iNum = 1;$isCutCode = "...";
			}else if(strpos($ch,'1110')===0){$iCut = 3;$iNum = 2;$isCutCode = "...";
			}else if(strpos($ch,'11110')===0){$iCut = 4;$iNum = 3;$isCutCode = "...";}
			if($iCut >-1 && $iNum >-1){
				$new_arr[$iCnt++] = substr($str,$i,$iCut);
				$i += $iNum;
			}
				
		}//end for
		return $new_arr;
	}

	
	//문자열 자르기
	public function getCutStrUTF($str, $iStart, $iLen = null, $bar="..."){
		$result = implode("",array_slice($this->setUTFArray($str),$iStart,$iLen));
		if(strLen($result) < strLen($str))$result .=$bar;
		return $result;
	}
	
	/**
	 * Desc:	랜덤 문자열 생성
	 * Param:	length		= 길이
	 *			type		= 문자열 형식(number = 숫자만, char = 문자만)
	 * Return:	STRING
	 */
	public function GenerateRanStr($length, $type = ''){
		$result = '';
		$characters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
		
		if($type == 'number')	$characters = "0123456789";
		if($type == 'char')		$characters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ";
		
		while($length--){
			$num = mt_rand(0, strLen($characters)-1);
			$str = $characters[$num];
			$result .= $str;
		}
		
		return $result;
	}

	
	//8자리 문자열에 mask 삽입[날짜]
	public function DateStringMask($pVal, $pMask){
		$strRtn = '';
		if($pVal != '') $strRtn = substr($pVal, 0, 4) . $pMask . substr($pVal, 4, 2) . $pMask . substr($pVal, 6, 2);
		
		return $strRtn;
	}

	
	//6자리 문자열에 mask 삽입[날짜]
	public function DateStringMask6($pVal, $pMask){
		$strRtn = '';
		if($pVal != '') $strRtn = substr($pVal, 0, 4) . $pMask . substr($pVal, 4, 2);
		
		return $strRtn;
	}
	
	
	/**
	 * Desc:	method Get 형식으로 파라미터 생성
	 * Param:	str			= 최종 문자열
	 *			strName		= 파라미터 이름
	 *			strValue	= 파라미터 값
	 * Return:	STRING
	 */
	public function GetStr($str, $strName, $strValue) {

		if($str == "" or is_null($str)) {
			$str = "";
		}
		else{
			$str = $str ."&";
		}
		
		$str .= $strName ."=". $strValue;
		
		return $str;
	}
	
	
	/**
	 * Desc:	화면에 문자열 출력
	 * Param:	str		= 쿼리문
	 * Return:	STRING
	 */
	public function echoStr($str){
		global $logNo;
		$logNo = $logNo + 1;
		echo "<br>". $logNo ." == ". $str ."<br>";
	}
	
	
	/**
	 * Desc:	화면에 쿼리스트링과 파라미터 출력
	 * Param:	str		= 쿼리문
	 *			param	= 파라미터
	 * Return:	STRING
	 */
	public function echoQry($str, $param){
		$rtn = "<br>query == ". nl2br($str);
		$rtn .= "<br>param == ". json_encode($param);
		echo $rtn;
	}
	
	
	/**
	 * Desc:	배열 내용 화면에 출력
	 * Param:	arr		= 파라미터명
	 * Return:	STRING
	 */
	public function echoArr($arr){
		$rtn = "";
		
		foreach($arr as $key=>$val){
			$rtn .= '<br>'. $key ." == ". $val;
		}
		
		echo $rtn;
	}
	
	
	/**
	 * Desc:	문자열 전화번호 형식으로 변경
	 * Param:	str		: 문자열
				hide	: 가운데자리 숨김여부
	 * Return:	STRING
	 */
	public function MaskingTelNo($str, $hide = false){
		$rtn = '';
		
		$strPattern['value'] = "/(^02.{0}|^01.{1}|[0-9]{3})([0-9]+)([0-9]{4})/";		//전화(휴대폰)
		$strPattern['mask'] = "$1-$2-$3";												//전화(휴대폰)
		
		if($hide == true) $strPattern['mask'] = "$1-****-$3";
		
		$rtn = preg_replace($strPattern['value'], $strPattern['mask'], $str);
		
		return $rtn;
	}
	
	
	// 사업자번호 형식으로 변환
	public function MaskingBusiNo($pVal){
		$strRtn = '';
		if($pVal != '') $strRtn = substr($pVal, 0, 3) .'-'. substr($pVal, 3, 2) .'-'. substr($pVal, 5, 5);
		
		return $strRtn;
	}
	
	
	// 첫글자 마지막글자 제외하고 숨김
	public function MaskingMiddleString($pVal){
		$strRtn = '';
		
		for($j = 0; $j < mb_strlen($pVal); $j++){
			if($j == 0){
				$strRtn .= mb_substr($pVal, 0, 1);
			}
			else if($j == (mb_strlen($pVal) - 1)){
				$strRtn .= mb_substr($pVal, (mb_strlen($pVal) - 1));
			}
			else{
				$strRtn .= '*';
			}
		}
		
		return $strRtn;
	}
	
	
	/**
	 * Desc:	숫자 앞에 0추가 [시간앞에 붙임]
	 * Param:	str		: 문자열
	 * Return:	STRING
	 */
	public function addZero($str){
		$rtn = '';
		
		$rtn = str_pad($str, 2, '0', STR_PAD_LEFT);
		
		return $rtn;
	}
	
	
	// ===================================================
	// Date Function
	// ===================================================
	
	/**
	 * Desc:	두 날짜의 날짜차이 계산
	 * Param:	strDate1		: 날짜1
				strDate2		: 날짜2
	 * Return:	Integer
	 */
	public function getDateDiff($strDate1, $strDate2){
		return (strtotime($strDate2) - strtotime($strDate1))/60/60/24 + 1;
	}
	
	/**
	 * Desc:	요일 표시
	 * Param:	nWeek		: 요일[숫자]
	 * Return:	STRING
	 */
	public function getWeekStr($nWeek){
		$arr_week = array("일","월","화","수","목","금","토");
		
		return $arr_week[$nWeek];
	}
	
	
	public function Excel2phpTime( $tRes, $dFormat="1900" ){
		if($dFormat == "1904") $fixRes = 24107.375;
		else $fixRes = 25569.375;
		return intval((($tRes - $fixRes) * 86400));
	}


	// ===================================================
	// Paging Function
	// ===================================================
	/**
	 * Desc:	템플릿 페이징
	 * Param:	nPageCnt	= 블럭당 페이지 수
	 * 			nowPage		= 현재 페이지
	 * 			nTotalPage	= 조회 리스트 수
	 * 			gStr		= 페이지 이동시 가지고다닐 파라미터
	 * Return:	STRING or NULL
	 */
	public function _getTmpPaging( $nPageCnt, $nowPage, $nTotalPage, $gStr ){
		$strPage = "<br><div id='pagenate'><div class='page'>";
		
		if($nTotalPage > 0){
			$nBlockCnt = ceil($nTotalPage / $nPageCnt);				// 블럭 수
			$nowBlock = ceil($nowPage / $nPageCnt);					// 현재 페이지가 위치한 블럭
			
			$s_page = ($nowBlock * $nPageCnt) - ($nPageCnt - 1);	// 현재 블럭의 시작번호
			if($s_page <= 1) $s_page = 1;
			$e_page = $nowBlock * $nPageCnt;						// 현재 블럭의 종료번호
			if($nTotalPage <= $e_page) $e_page = $nTotalPage;
			
			if($nTotalPage > 1) $strPage .= "<a href='". $_SERVER['PHP_SELF'] ."?nowPage=1&". $gStr ."' class='first'>&lt;&lt;</a>";						// 리스트의 가장 처음으로 이동
			
			if($nowBlock > 1){
				$strPage .= "<a href='". $_SERVER['PHP_SELF'] ."?nowPage=". ($s_page - 1) ."&". $gStr ."' class='pref'>&lt;</a>";	// 이전 블럭으로 이동
			}
			
			for($i = $s_page; $i <= $e_page; $i++){
				if($i == $nowPage){
					$strPage .= "<strong>". $i ."</strong>";																			// 현재 페이지 (이동 안됨)
				}
				else{
					$strPage .= "<a href='". $_SERVER['PHP_SELF'] ."?nowPage=". $i ."&". $gStr ."'>". $i ."</a>";						// 페이지 숫자
				}
			}
			
			if($nBlockCnt > $nowBlock){
				$strPage .= "<a href='". $_SERVER['PHP_SELF'] ."?nowPage=". ($e_page + 1) ."&". $gStr ."' class='next'>&gt;</a>";	// 다음 블럭으로 이동
			}
			
			if($nTotalPage > 1) $strPage .= "<a href='". $_SERVER['PHP_SELF'] ."?nowPage=". $nTotalPage ."&". $gStr ."' class='last'>&gt;&gt;</a>";		// 리스트의 가장 마지막으로 이동
		}
		
		$strPage .= "</div></div>";
		
		return $strPage;
	}
	
	/**
	 * Desc:	관리자 페이징
	 * Param:	nPageCnt	= 블럭당 페이지 수
	 * 			nowPage		= 현재 페이지
	 * 			nTotalPage	= 조회 리스트 수
	 * 			gStr		= 페이지 이동시 가지고다닐 파라미터
	 * Return:	STRING or NULL
	 */
	public function getAdmPaging( $nPageCnt, $nowPage, $nTotalPage, $gStr ){
		$strPage = "<div id='pagenate'><div class='page'>";
		
		if($nTotalPage > 0){
			$nBlockCnt = ceil($nTotalPage / $nPageCnt);				// 블럭 수
			$nowBlock = ceil($nowPage / $nPageCnt);					// 현재 페이지가 위치한 블럭
			
			$s_page = ($nowBlock * $nPageCnt) - ($nPageCnt - 1);	// 현재 블럭의 시작번호
			if($s_page <= 1) $s_page = 1;
			$e_page = $nowBlock * $nPageCnt;						// 현재 블럭의 종료번호
			if($nTotalPage <= $e_page) $e_page = $nTotalPage;
			
			if($nTotalPage > 1) $strPage .= "<a href='". $_SERVER['PHP_SELF'] ."?nowPage=1&". $gStr ."' class='first'>&lt;&lt;</a>";						// 리스트의 가장 처음으로 이동
			
			if($nowBlock > 1){
				$strPage .= "<a href='". $_SERVER['PHP_SELF'] ."?nowPage=". ($s_page - 1) ."&". $gStr ."' class='prev'>&lt;</a>";	// 이전 블럭으로 이동
			}
			
			for($i = $s_page; $i <= $e_page; $i++){
				if($i == $nowPage){
					$strPage .= "<strong>". $i ."</strong>";																			// 현재 페이지 (이동 안됨)
				}
				else{
					$strPage .= "<a href='". $_SERVER['PHP_SELF'] ."?nowPage=". $i ."&". $gStr ."'>". $i ."</a>";						// 페이지 숫자
				}
			}
			
			if($nBlockCnt > $nowBlock){
				$strPage .= "<a href='". $_SERVER['PHP_SELF'] ."?nowPage=". ($e_page + 1) ."&". $gStr ."' class='next'>&gt;</a>";	// 다음 블럭으로 이동
			}
			
			if($nTotalPage > 1) $strPage .= "<a href='". $_SERVER['PHP_SELF'] ."?nowPage=". $nTotalPage ."&". $gStr ."' class='last'>&gt;&gt;</a>";		// 리스트의 가장 마지막으로 이동
		}
		
		$strPage .= "</div></div>";
		
		return $strPage;
	}
	
	/**
	 * Desc:	관리자 AJAX 페이징
	 * Param:	nPageCnt	= 블럭당 페이지 수
	 * 			nowPage		= 현재 페이지
	 * 			nTotalPage	= 조회 리스트 수
	 * 			gStr		= 페이지 이동시 가지고다닐 파라미터
	 * Return:	STRING or NULL
	 */
	public function getAdmPagingAjax( $nPageCnt, $nowPage, $nTotalPage, $gStr ){
		$strPage = "<div id='pagenate'><div class='page'>";
		
		if($nTotalPage > 0){
			$nBlockCnt = ceil($nTotalPage / $nPageCnt);				// 블럭 수
			$nowBlock = ceil($nowPage / $nPageCnt);					// 현재 페이지가 위치한 블럭
			
			$s_page = ($nowBlock * $nPageCnt) - ($nPageCnt - 1);	// 현재 블럭의 시작번호
			if($s_page <= 1) $s_page = 1;
			$e_page = $nowBlock * $nPageCnt;						// 현재 블럭의 종료번호
			if($nTotalPage <= $e_page) $e_page = $nTotalPage;
			
			if($nTotalPage > 1) $strPage .= "<a href='javascript:showDetailPopup(". $gStr .", 1);' class='first'>&lt;&lt;</a>";						// 리스트의 가장 처음으로 이동
			
			if($nowBlock > 1){
				$strPage .= "<a href='javascript:showDetailPopup(". $gStr .", ". ($s_page - 1) .");' class='prev'>&lt;</a>";	// 이전 블럭으로 이동
			}
			
			for($i = $s_page; $i <= $e_page; $i++){
				if($i == $nowPage){
					$strPage .= "<strong>". $i ."</strong>";																			// 현재 페이지 (이동 안됨)
				}
				else{
					$strPage .= "<a href='javascript:showDetailPopup(". $gStr .", ". $i .");'>". $i ."</a>";						// 페이지 숫자
				}
			}
			
			if($nBlockCnt > $nowBlock){
				$strPage .= "<a href='javascript:showDetailPopup(". $gStr .", ". ($e_page + 1) .");' class='next'>&gt;</a>";	// 다음 블럭으로 이동
			}
			
			if($nTotalPage > 1) $strPage .= "<a href='javascript:showDetailPopup(". $gStr .", ". $nTotalPage .");' class='last'>&gt;&gt;</a>";		// 리스트의 가장 마지막으로 이동
		}
		
		$strPage .= "</div></div>";
		
		return $strPage;
	}
	
	
	// ===================================================
	// Code Function
	// ===================================================
	
	//코드 치환 (pVal과 pCode가 같으면 pRtn을 리턴 아니면 pRtn2를 리턴)
	public function CodeString($pVal, $pCode, $pRtn, $pRtn2){
		return ($pVal == $pCode) ? $pRtn : $pRtn2;
	}


	// ===================================================
	// Javascript Function
	// ===================================================
	
	public function jsAlert($val){
		$strRtn = '<script>alert("'. $val .'")</script>';
		echo $strRtn;
	}
	
	
	public function jsClose(){
		$strRtn = '<script>self.close();</script>';
		echo $strRtn;
	}
	
	
	public function jsBack(){
		$strRtn = '<script>history.back();</script>';
		echo $strRtn;
	}

	
	public function jsLocation($val){
		$strRtn = '<script>location.href = "'. $val .'";</script>';
		echo $strRtn;
	}
	
	
	public function jsReplace($val){
		$strRtn = '<script>location.replace("'. $val .'");</script>';
		echo $strRtn;
	}

	
	public function jsAlertBack($val){
		$strRtn = '<script>alert("'. $val .'"); history.back();</script>';
		echo $strRtn;
	}

	
	public function jsAlertClose($val){
		$strRtn = '<script>alert("'. $val .'"); self.close();</script>';
		echo $strRtn;
	}
	
	
	// ===================================================
	// System Function
	// ===================================================
	/**
	 * Desc:	파라미터 받기
	 * Param:	name		= 파라미터명
	 * 			defValue	= 파라미터 없을시 기본값
	 * Return:	STRING or FALSE
	 */
	public function getReq($name, $defValue = ''){
		
		$temp = null;
		$arr = array();
		
		if(is_array($_REQUEST[$name])){
			$arr = $_REQUEST[$name];
			
			$temp = $arr;
		}
		else{
			if(isset($_REQUEST[$name])) $temp = $_REQUEST[$name];
			if($temp == '') $temp = $defValue;
			
			$temp = trim($temp);
			//$temp = str_replace("'", "\"", $temp);		//특수문자 치환
		}
		
		return $temp;
	}//end fnc
	
	
	// 쿠키생성
	public function fnSetCookie($name, $val, $day){
		setcookie($name, $val, time() + (60 * 60 * 24 * (int)$day), '/');
	}
	
	
	// ===================================================
	// API Function
	// ===================================================
	// 좌표 가져오기(다음API)
	public function getLatLon($addr){
		$arr_rtn = array();
		
		$apikey = "0f20567b94d09e48d97e96773065b583";
		$url = "https://dapi.kakao.com/v2/local/search/address.json";
		$post_data = "query=". $addr;
		
		$ch = curl_init();
		
		// user credencial
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		
		// post_data
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization:KakaoAK '. $apikey));
		
		$response = curl_exec($ch);
		
		curl_close($ch);
		
		$arr = json_decode($response, true);
		
		$arr_rtn['result'] = $arr['meta']['total_count'];
		$arr_rtn['lon'] = $arr['documents'][0]['x'];
		$arr_rtn['lat'] = $arr['documents'][0]['y'];
		
		return $arr_rtn;
	}


	// ==================================================
	// Code Generator
	// ==================================================
	public function getCodeGen($pCode, $pLength = '2'){
		if($pLength == "1"){
			$rand_code = sprintf('%01d',rand(0,9));
		} elseif ($pLength == "2") {
			$rand_code = sprintf('%02d',rand(00,99));
		} elseif ($pLength == "3") {
			$rand_code = sprintf('%03d',rand(000,999));
		} elseif ($pLength == "4") {
			$rand_code = sprintf('%04d',rand(0000,9999));
		}
		$returnstr = $returnstr. $pCode. date('YmdHis').$rand_code;
		return $returnstr;
	}

}//end class	
?>
