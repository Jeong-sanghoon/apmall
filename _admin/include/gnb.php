<?
	if($S_GRADE == 'U'){
?>

<div class='gnb'>
	<ul>		
		<li class='<?=$_MENU1 == '3' ? 'sel' : ''?>'><a href='/_admin/page/request/reqInput_vt.php'>OrderMng</a></li>		
	</ul>
	<a href='javascript:doLogout();'>Logout</a>
</div>

<script>
	function doLogout(){
		location.href = "/_admin/logout.php";
	}
</script>

<?
	}
	else{
?>

<div class='gnb'>
	<ul>
		<li class='<?=$_MENU1 == '1' ? 'sel' : ''?>'><a href='/_admin/page/system/systemList.php'>시스템관리</a></li> <!-- 클래스 sel 추가 하면 현재 위치 메뉴 표시 -->
		<li class='<?=$_MENU1 == '2' ? 'sel' : ''?>'><a href='/_admin/page/adm/adminList.php'>담당자관리</a></li>
		<li class='<?=$_MENU1 == '3' ? 'sel' : ''?>'><a href='/_admin/page/request/reqList.php'>신청관리</a></li>
		<li class='<?=$_MENU1 == '4' ? 'sel' : ''?>'><a href='/_admin/page/order/orderAccept.php'>주문관리</a></li>
		<li class='<?=$_MENU1 == '5' ? 'sel' : ''?>'><a href='/_admin/page/product/manufactureList.php'>제품관리</a></li>
		<li class='<?=$_MENU1 == '6' ? 'sel' : ''?>'><a href='/_admin/page/category/categoryList.php'>카테고리관리</a></li>
		<li class='<?=$_MENU1 == '7' ? 'sel' : ''?>'><a href='/_admin/page/adjustment/adjustment.php'>정산현황</a></li>
		<li class='<?=$_MENU1 == '8' ? 'sel' : ''?>'><a href='/_admin/page/member/memberList.php'>회원관리</a></li>
	</ul>
	<a href='javascript:doLogout();'>로그아웃</a>
</div>

<script>
	function doLogout(){
		location.href = "/_admin/logout.php";
	}
</script>

<?
	}
?>