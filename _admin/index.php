<?
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	// 세션 체크
	if($S_SEQ == ''){
		echo "<script>location.href='/_admin/login.php'</script>";
		exit;
	}
	
	header("Location: /_admin/main.php");
?>