<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSession($url = '/_admin/');

	$_MENU1 = '8';		// 상단메뉴
	$_MENU2 = '1';		// 왼쪽메뉴

	$_NAVITITLE = "회원관리 > 회원관리";	

	//=====================================================
	//== 도움말 - Get Parameters
	//=====================================================
	$user_seq = $cFnc->getReq('user_seq', '');

	$cal_1 = $cFnc->getReq('cal_1', date('Y-m-d', strtotime('-3 month')));
	$cal_2 = $cFnc->getReq('cal_2', date('Y-m-d'));
	$user_seq  = $cFnc->getReq('user_seq', '');
	$use_yn = $cFnc->getReq('use_yn', '');
	$user_id = $cFnc->getReq('user_id', '');
	$user_nm = $cFnc->getReq('user_nm', '');
	$email = $cFnc->getReq('email', '');
	$order_cont = $cFnc->getReq('order_cont', 'USER_SEQ');
	$order_asc = $cFnc->getReq('order_asc', 'DESC');
	$search_list1 = $cFnc->getReq('search_list1', '');
	
	$nPageCnt = $cFnc->getReq('nPageCnt', 10);		// 블럭당 페이지 수
	$nListCnt = $cFnc->getReq('nListCnt', 10);		// 페이지당 리스트 수
	$nowPage = $cFnc->getReq('nowPage', 1);			// 현재 페이지

	// =====================================================
	// Set Variables
	// =====================================================
	$cPdo = new cPdo($ARR_DB_INFO);

	$pageaction = "INSERT";

	// =====================================================
	// Set Parameters (Get Types)
	// =====================================================
	$gStr = "";
	$gStr = $cFnc->GetStr( $gStr, "cal_1", $cal_1 );
	$gStr = $cFnc->GetStr( $gStr, "cal_2", $cal_2 );
	$gStr = $cFnc->GetStr( $gStr, "user_nm", $user_nm );
	$gStr = $cFnc->GetStr( $gStr, "user_id", $user_id );
	$gStr = $cFnc->GetStr( $gStr, "email", $email );
	$gStr = $cFnc->GetStr( $gStr, "use_yn", $use_yn );
	$gStr = $cFnc->GetStr( $gStr, "order_cont", $order_cont );
	$gStr = $cFnc->GetStr( $gStr, "order_asc", $order_asc );
	$gStr = $cFnc->GetStr( $gStr, "search_list1", $search_list1 );

	$gStr2 = "";
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_1", $cal_1 );
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_2", $cal_2 );
	$gStr2 = $cFnc->GetStr( $gStr2, "user_nm", $user_nm );
	$gStr2 = $cFnc->GetStr( $gStr2, "user_id", $user_id );
	$gStr2 = $cFnc->GetStr( $gStr2, "email", $email );
	$gStr2 = $cFnc->GetStr( $gStr2, "use_yn", $use_yn );
	$gStr2 = $cFnc->GetStr( $gStr2, "order_cont", $order_cont );
	$gStr2 = $cFnc->GetStr( $gStr2, "order_asc", $order_asc );
	$gStr2 = $cFnc->GetStr( $gStr2, "search_list1", $search_list1 );
	$gStr2 = $cFnc->GetStr( $gStr2, "nListCnt", $nListCnt );
	$gStr2 = $cFnc->GetStr( $gStr2, "nowPage", $nowPage );
	$gStr2 = $cFnc->GetStr( $gStr2, "nPageCnt", $nPageCnt );

	// =====================================================
	// Start Tran
	// =====================================================
	if($user_seq != ''){
		$pageaction = "UPDATE";

		$arParam = array();
		array_push($arParam, $user_seq);
		$qry = "SELECT * FROM TB_USER WHERE USER_SEQ = ?";
		$rslt = $cPdo->execQuery('data', $qry, $arParam);
		$ds = $rslt['data'];
	}

	include $_SERVER["DOCUMENT_ROOT"]. "/_admin/h.php";
?>

<script type="text/javascript" src="/js/jquery.form.js"></script>

<script>
	var pageaction = "<?=$pageaction?>";
	
	$('document').ready(function(){
		if(pageaction == 'INSERT') $('#USE_Y').attr('checked', true);
	});
	

	// ID 검색
	function doIdSearch(){
		if(!VALIDATION_EMAIL($('#USER_ID').val())){alert("아이디를 이메일 형식으로 입력해 주세요"); $('#USER_ID').focus(); return false;}

		var url = "memberProc.php";
		var param = $('#frm').serialize();
		$('#pageaction').val("IDSEARCH");

		$('#frm').ajaxForm({
			type:"POST",
			dataType : 'json',
			url:url,
			data: param,			
			async: false,
			success: function(obj){
				var strUrl = obj.url;				
				
				if(obj.status == 0){
					alert(obj.msg);
					$('#hidcheck').val('Y');
					$('#hid').val($('#USER_ID').val());
					$('#pageaction').val("INSERT");
				}
				else{
					alert(obj.msg);
				}
			},
			error: function(request, status, error){
				alert('Find Error -> '+ status);
				return false;
			}
		});
		
		$('#frm').submit();

	}

	// 등록
	function doProc(){
		if(VALIDATION_EMAIL(!$('#USER_ID').val())){alert("아이디를 이메일 형식으로 입력해 주세요"); $('#USER_ID').focus(); return false;}
		
		if(pageaction == 'INSERT'){
			if($('#PWD').val() == ''){alert("비밀번호를 입력해 주세요"); $('#PWD').focus(); return false;}
			if($('#PWD_RE').val() == ''){alert("비밀번호확인을 입력해 주세요"); $('#PWD_RE').focus(); return false;}
			if($('#PWD_RE').val() != $('#PWD').val()){alert("비밀번호를 동일하게 입력해 주세요"); $('#ADM_PW').focus(); return false;}
		}
		
		if($('#USER_NM').val() == ''){alert("이름을 입력해 주세요"); $('#USER_NM').focus(); return false;}
		// if($('#TEL').val() == ''){alert("연락처를 입력해 주세요"); $('#TEL').focus(); return false;}
		// if($('#EMAIL').val() == ''){alert("이메일 주소를 입력해 주세요"); $('#EMAIL').focus(); return false;}
		
		if(pageaction == 'INSERT'){
			if($('#hidcheck').val() != 'Y'){
				alert('아이디 중복 확인을 진행해 주세요');
				return false;
			}
			if($('#hid').val() != $('#USER_ID').val()){
				alert('인증된 아이디와 입력된 아이디가 다릅니다. 중복 확인을 다시 진행해 주세요');
				return false;
			}
		}
		
		var url = "memberProc.php";
		var param = $('#frm').serialize();
		
		$.ajax({
			type:"POST",
			dataType : 'json',
			url:url,
			data: param,
			// enctype: "multipart/form-data",
			async: false,
			success: function(obj){
				var strUrl = obj.url;
				if(pageaction == 'UPDATE') strUrl = strUrl +"?user_seq=<?=$user_seq?>&<?=$gStr2?>";
				
				if(obj.status == 0){
					alert(obj.msg);
					if(obj.url != "") location.replace(strUrl);
				}
				else{
					alert(obj.msg);
					location.replace(strUrl);
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
	
	// 취소
	function doCancel(){
		history.back();
	}
	
</script>

	<div id='wrap'>

	<!-- Left menu -->
	<?include $_SERVER["DOCUMENT_ROOT"]. "/_admin/include/lnb.php";?>
	<!--// Left menu -->

	<div id='contain'>

		<!-- menu GNB -->
		<?include $_SERVER["DOCUMENT_ROOT"]. "/_admin/include/gnb.php";?>
		<!--// menu GNB -->

		<!-- 전체 100% CONTENTS -->
		<div id='content_wrap'> <!-- 100%의 전체 컨텐츠 영역-->
		
			<!-- content -->
			<div class='content w100p'> <!-- 원하는 컨텐츠 넓이를 추가 w960 클래스는 width:960px란 의미 -->

				<h2><?=$_NAVITITLE?></h2>
				<div class='w900'><!-- 컨텐츠 가로길이 줄임-->
					<form method="post" name="frm" id="frm" action="systemProc.php">
					<input type="hidden" name="pageaction" id="pageaction" value="<?=$pageaction?>">
					<input type="hidden" name="user_seq" id="user_seq" value="<?=$user_seq?>">
					<input type="hidden" name="hidcheck" id="hidcheck" value="N">
					<input type="hidden" name="hid" id="hid" value="">
					<table border="0" width="100%" cellspacing="0" cellpadding="0" class='adtb_02 mt20 '>
						<colgroup>
							<col width='20%' />
							<col width='80%' />
						</colgroup>
						<tr>
							<th scope="row">아이디</th>
							<td class='lf'>
<?
	if($pageaction == 'UPDATE'){
?>
								<?=$ds['USER_ID']?>
<?
	}
	else{
?>
								<input name="USER_ID" id="USER_ID" type="text" class='w200' value="" />
								<input type='button' value='중복검색' class='btn_b01 w80' onclick="javascript:doIdSearch();"/>
<?
	}
?>
							</td>
						</tr>
						<tr>
							<th scope="row">비밀번호</th>
							<td class='lf'><input name="PWD" id="PWD" type="password" class='w200' value=""/></td>
						</tr>
						<tr>
							<th scope="row">비밀번호확인</th>
							<td class='lf'><input name="PWD_RE" id="PWD_RE" type="password" class='w200' value=""/></td>
						</tr>
						<tr>
							<th scope="row">이름</th>
							<td class='lf'><input name="USER_NM" id="USER_NM" type="text" class='w200' value="<?=$ds['USER_NM']?>"/></td>
						</tr>
						<tr>
							<th scope="row">연락처</th>
							<td class='lf'><input name="TEL" id="TEL" type="text" class='w200' value="<?=$ds['TEL']?>"/></td>
						</tr>						
						<tr>
							<th scope="row">주소</th>
							<td class='lf'><input name="ADDR" id="ADDR" type="text" class='w600' value="<?=$ds['ADDR']?>"/></td>
						</tr>												
						<tr>
							<th scope="row">가입유형</th>
							<td class='lf con'>
								<select name="JOIN_TP" id="JOIN_TP">
									<option value="EMAIL" <?=$cFnc->CodeString($ds['JOIN_TP'], 'EMAIL', 'selected', '')?>>이메일</option>
									<option value="FACEBOOK" <?=$cFnc->CodeString($ds['JOIN_TP'], 'FACEBOOK', 'selected', '')?>>페이스북</option>
									<option value="TWITTER" <?=$cFnc->CodeString($ds['JOIN_TP'], 'TWITTER', 'selected', '')?>>트위터</option>
								</select>
							</td>
						</tr>					
						<tr>
							<th scope="row">사용여부</th>
							<td class='lf con'>
								<input name="USE_YN" id="USE_Y" type="radio" value="Y" <?=$ds['USE_YN'] == 'Y' ? 'checked' : ''?> /> 사용
								<span class='pl10'><input name="USE_YN" id="USE_N" type="radio" value="N" <?=$ds['USE_YN'] == 'N' ? 'checked' : ''?> /> 중지 </span>
							</td>
						</tr>
<?
	if($pageaction == 'UPDATE'){
?>
						<tr>
							<th scope="row">최근로그인일</th>
							<td class='lf'><?=$ds['LAST_ACC_DT']?></td>
						</tr>						
						<tr>
							<th scope="row">등록일</th>
							<td class='lf'><?=$ds['REG_DT']?></td>
						</tr>
						<tr>
							<th scope="row">수정일</th>
							<td class='lf'><?=$ds['MOD_DT']?></td>
						</tr>
						
<?
	}
?>
					</table>
					<div class='center mt20'>
						<input type='button' value='등록' class='btn_b01 w80' onclick="javascript:doProc();"/>
						<input type='button' value='취소' class='btn_b02 w80' onclick="javascript:doCancel();"/>
					</div>
					</form>
				</div>

			</div>
			<!--// content -->

		</div>
		<!--// 전체 100% CONTENTS	 -->

	</div>
	<!--//contain-->

	</div>
	<!--//wrap-->
<?
	include $_SERVER["DOCUMENT_ROOT"]. "/_admin/f.php";
?>