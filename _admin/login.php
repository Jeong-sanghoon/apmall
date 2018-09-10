<?
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	include $_SERVER["DOCUMENT_ROOT"]. "/_admin/h.php";
?>

<script>
	function doLogin(){
		if($('#id').val() == ''){alert("아이디를 입력해 주세요"); $('#id').focus(); return false;}
		if($('#pw').val() == ''){alert("비밀번호를 입력해 주세요"); $('#pw').focus(); return false;}
		
		$.ajax({
			type:"POST",
			dataType : 'json',
			url: 'login_proc.php',
			data: $('#frm').serialize(),
			async: false,
			success: function(obj){
				console.log(obj);
				if(obj.status == 0){
					if(obj.msg != "") alert(obj.msg);
					if(obj.url != "") location.replace(obj.url);
					return false;
				}
				else{
					if(obj.msg != "") alert(obj.msg);
					location.replace(obj.url);
				}
			},
			error: function(request, status, error){
				alert('Find Error -> '+ status);
				return false;
				//console.log(request);
				//console.log(status);
				//console.log(error);
			}
		});
	}
	
	function evEnter(){
		if(event.keyCode == 13){
			doLogin();
		}
	}
</script>

	<div id='login'>

		<div class='login'>
			<h1><img src ='/_admin/images/logo_m.png' /></h1>
			<form method="post" name="frm" id="frm" action="">
			<fieldset>
			<div class='lg_bx'>
				<ul>
					<li><label for='id'>ID</label><input type='text' name='id' id='id' class='lg_txt' style='ime-mode:disabled;' tabIndex='1'></li>
					<li><label for='pw'>PASSWORD</label><input type='password' name='pw' id='pw'  class='lg_txt' style='ime-mode:disabled;' tabIndex='2' onkeypress="javascript:evEnter();"></li>
				</ul>
				<p><input type='button' value='로그인' tabIndex='4' class='btn_lgn' onclick="javascript:doLogin();"></p>
			</div>
			</fieldset>
			</form>
			<p class='copy'>COPYRIGHT (C) NS9 Call ALL RIGHTS RESERVED.</p>
		</div>

	</div>
	<!--//wrap-->

<?
	include $_SERVER["DOCUMENT_ROOT"]. "/_admin/f.php";
?>