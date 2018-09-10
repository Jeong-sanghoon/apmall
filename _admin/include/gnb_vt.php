<div class='gnb'>
	<ul>		
		<li class='<?=$_MENU1 == '3' ? 'sel' : ''?>'><a href='/_admin/page/request/reqInput_vt.php'>신청관리</a></li>		
	</ul>
	<a href='javascript:doLogout();'>로그아웃</a>
</div>

<script>
	function doLogout(){
		location.href = "/_admin/logout.php";
	}
</script>