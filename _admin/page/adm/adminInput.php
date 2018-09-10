<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSession($url = '/_admin/');

	$_MENU1 = '2';		// 상단메뉴
	$_MENU2 = '1';		// 왼쪽메뉴

	$_NAVITITLE = "담당자관리 > 계정관리";	

	//=====================================================
	//== 도움말 - Get Parameters
	//=====================================================
	$adm_seq = $cFnc->getReq('adm_seq', '');

	$cal_1 = $cFnc->getReq('cal_1', date('Y-m-d', strtotime('-3 month')));
	$cal_2 = $cFnc->getReq('cal_2', date('Y-m-d'));
	$use_yn = $cFnc->getReq('use_yn', '');
	$adm_id = $cFnc->getReq('adm_id', '');
	$adm_nm = $cFnc->getReq('adm_nm', '');
	$email = $cFnc->getReq('email', '');
	$order_cont = $cFnc->getReq('order_cont', 'SYSTEM_NM');
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
	$gStr = $cFnc->GetStr( $gStr, "adm_nm", $adm_nm );
	$gStr = $cFnc->GetStr( $gStr, "adm_id", $adm_id );
	$gStr = $cFnc->GetStr( $gStr, "email", $email );
	$gStr = $cFnc->GetStr( $gStr, "use_yn", $use_yn );
	$gStr = $cFnc->GetStr( $gStr, "order_cont", $order_cont );
	$gStr = $cFnc->GetStr( $gStr, "order_asc", $order_asc );
	$gStr = $cFnc->GetStr( $gStr, "search_list1", $search_list1 );

	$gStr2 = "";
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_1", $cal_1 );
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_2", $cal_2 );
	$gStr2 = $cFnc->GetStr( $gStr2, "adm_nm", $adm_nm );
	$gStr2 = $cFnc->GetStr( $gStr2, "adm_id", $adm_id );
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
	if($adm_seq != ''){
		$pageaction = "UPDATE";

		$arParam = array();
		array_push($arParam, $adm_seq);
		$qry = "SELECT * FROM TB_ADM WHERE ADM_SEQ = ?";
		$rslt = $cPdo->execQuery('data', $qry, $arParam);
		$ds = $rslt['data'];
	}

	// SYSTEM_CD 리스트		
	$qry = "SELECT SYSTEM_CD, SYSTEM_NM FROM TB_SYSTEM WHERE USE_YN = 'Y'";
	$rsSystem = $cPdo->execQuery('list', $qry, $arParam);
	$rsSystemList = $rsSystem['data'];

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
		if($('#ADM_ID').val() == ''){alert("아이디를 입력해 주세요"); $('#ADM_ID').focus(); return false;}

		var url = "adminProc.php";
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
					$('#hid').val($('#ADM_ID').val());
					$('#pageaction').val("INSERT");
				}
				else{
					alert(obj.msg);
					//location.replace(strUrl);
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
		
		$('#frm').submit();

	}

	// 등록
	function doProc(){
		if($('#ADM_ID').val() == ''){alert("아이디를 입력해 주세요"); $('#ADM_ID').focus(); return false;}
		if($('#EMAIL').val() == ''){alert("이메일 주소를 입력해 주세요"); $('#EMAIL').focus(); return false;}
		if($('#TEL').val() == ''){alert("연락처를 입력해 주세요"); $('#TEL').focus(); return false;}

		if(pageaction == 'INSERT'){
			if($('#hidcheck').val() != 'Y'){
				alert('아이디 중복 확인을 진행해 주세요');
				return false;
			}
			if($('#hid').val() != $('#ADM_ID').val()){
				alert('인증된 아이디와 입력된 아이디가 다릅니다. 중복 확인을 다시 진행해 주세요');
				return false;
			}
			
			if($('#ADM_PW').val() == ''){alert("비밀번호를 입력해 주세요"); $('#ADM_PW').focus(); return false;}
			if($('#ADM_PW_RE').val() != $('#ADM_PW').val()){alert("비밀번호확인을 동일하게 입력해 주세요"); $('#ADM_PW').focus(); return false;}
		}
		else{
			if($('#ADM_PW').val() != ''){
				if($('#ADM_PW_RE').val() != $('#ADM_PW').val()){alert("비밀번호확인을 동일하게 입력해 주세요"); $('#ADM_PW').focus(); return false;}
			}
		}
		

		var url = "adminProc.php";
		var param = $('#frm').serialize();
		
		$('#frm').ajaxForm({
			type:"POST",
			dataType : 'json',
			url:url,
			data: param,
			// enctype: "multipart/form-data",
			async: false,
			success: function(obj){
				//console.log(obj);
				var strUrl = obj.url;
				if(pageaction == 'UPDATE') strUrl = strUrl +"?adm_seq=<?=$adm_seq?>&<?=$gStr2?>";
				
				if(obj.status == 0){
					alert(obj.msg);
					history.back();
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
		
		$('#frm').submit();
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
					<input type="hidden" name="adm_seq" id="adm_seq" value="<?=$adm_seq?>">
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
								<?=$ds['ADM_ID']?>
<?
	}
	else{
?>
								<input name="ADM_ID" id="ADM_ID" type="text" class='w200' value="" />
								<input type='button' value='중복검색' class='btn_b01 w80' onclick="javascript:doIdSearch();"/>
<?
	}
?>
							</td>
						</tr>
						<tr>
							<th scope="row">비밀번호</th>
							<td class='lf'><input name="ADM_PW" id="ADM_PW" type="password" class='w200' value=""/></td>
						</tr>
						<tr>
							<th scope="row">비밀번호확인</th>
							<td class='lf'><input name="ADM_PW_RE" id="ADM_PW_RE" type="password" class='w200' value=""/></td>
						</tr>
						<tr>
							<th scope="row">이름</th>
							<td class='lf'><input name="ADM_NM" id="ADM_NM" type="text" class='w200' value="<?=$ds['ADM_NM']?>"/></td>
						</tr>
						<tr>
							<th scope="row">이메일</th>
							<td class='lf'><input name="EMAIL" id="EMAIL" type="text" class='w200' value="<?=$ds['EMAIL']?>"/></td>
						</tr>
						<tr>
							<th scope="row">연락처</th>
							<td class='lf'><input name="TEL" id="TEL" type="text" class='w200' value="<?=$ds['TEL']?>"/></td>
						</tr>						
						<tr>
							<th scope="row">등급</th>
							<td class='lf con'>
								<select name="GRADE" id="GRADE">
									<option value="S" <?=$cFnc->CodeString($ds['GRADE'], 'S', 'selected', '')?>>시스템관리자</option>
									<option value="A" <?=$cFnc->CodeString($ds['GRADE'], 'A', 'selected', '')?>>관리자</option>
									<option value="U" <?=$cFnc->CodeString($ds['GRADE'], 'U', 'selected', '')?>>일반관리자</option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row">서비스코드</th>
							<td class='lf con'>
								<select name="SYSTEM_CD" id="SYSTEM_CD">

<?
	foreach($rsSystemList as $j=>$dsrow){
?>
							<option value="<?=$dsrow['SYSTEM_CD']?>" <?=$cFnc->CodeString($dsrow['SYSTEM_CD'], $ds['SYSTEM_CD'], 'checked', '')?> ><?=$dsrow['SYSTEM_NM']?></option>
<?
	}
?>
								</select>
							</td>
						</tr>

<?
	if($pageaction == 'UPDATE'){
?>
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