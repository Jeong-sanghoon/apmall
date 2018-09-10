<?
	session_start();
	
	$S_SEQ			= $_SESSION['ADM_SEQ'];
	$S_ADM_ID		= $_SESSION['ADM_ID'];
	$S_ADM_NM		= $_SESSION['ADM_NM'];
	$S_SYSTEM_CD	= $_SESSION['SYSTEM_CD'];
	$S_GRADE		= $_SESSION['GRADE'];
	
	
	// 세션 체크
	function chkSession($url = '/'){
		if($_SESSION['ADM_SEQ'] == ''){
			echo "<script>alert('세션이 종료되었습니다. 로그인 해 주세요.'); location.href='". $url ."'</script>";
			exit;
		}
	}
	
	// 세션 체크(Ajax)
	function chkSessionAjax($url = '/'){
		if($_SESSION['ADM_SEQ'] == ''){
			$result = array();
			$result['status'] = 0;
			$result['msg'] = "세션이 종료되었습니다. 로그인 해 주세요.";
			$result['url'] = $url;
			
			return $result;
			exit;
		}
	}
?>