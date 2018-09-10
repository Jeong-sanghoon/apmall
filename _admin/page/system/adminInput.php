<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSession($url = '/_admin/');

	$_MENU1 = '1';		// 상단메뉴
	$_MENU2 = '1';		// 왼쪽메뉴

	// ===================================================
	// get parameter
	// ===================================================
	$seq = $cFnc->getReq('seq', '');

	$cal_1 = $cFnc->getReq('cal_1', date('Y-m-d', strtotime('-3 month')));
	$cal_2 = $cFnc->getReq('cal_2', date('Y-m-d'));
	$search_type = $cFnc->getReq('search_type', '');
	$search_text = $cFnc->getReq('search_text', '');
	$search_list1 = $cFnc->getReq('search_list1', '');
	$order_cont = $cFnc->getReq('order_cont', 'SEQ');
	$order_asc = $cFnc->getReq('order_asc', 'DESC');
	
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
	$gStr = $cFnc->GetStr( $gStr, "search_type", $search_type );
	$gStr = $cFnc->GetStr( $gStr, "search_text", $search_text );
	$gStr = $cFnc->GetStr( $gStr, "search_list1", $search_list1 );
	$gStr = $cFnc->GetStr( $gStr, "order_cont", $order_cont );
	$gStr = $cFnc->GetStr( $gStr, "order_asc", $order_asc );

	$gStr2 = "";
	$gStr2 = $cFnc->GetStr( $gStr2,"cal_1", $cal_1 );
	$gStr2 = $cFnc->GetStr( $gStr2,"cal_2", $cal_2 );
	$gStr2 = $cFnc->GetStr( $gStr2,"search_type", $search_type );
	$gStr2 = $cFnc->GetStr( $gStr2,"search_text", $search_text );
	$gStr2 = $cFnc->GetStr( $gStr2,"search_list1", $search_list1 );
	$gStr2 = $cFnc->GetStr( $gStr2,"order_cont", $order_cont );
	$gStr2 = $cFnc->GetStr( $gStr2,"order_asc", $order_asc );
	$gStr2 = $cFnc->GetStr( $gStr2,"nListCnt", $nListCnt );
	$gStr2 = $cFnc->GetStr( $gStr2,"nowPage", $nowPage );
	$gStr2 = $cFnc->GetStr( $gStr2,"nPageCnt", $nPageCnt );

	// =====================================================
	// Start Tran
	// =====================================================
	if($seq != ''){
		$pageaction = "UPDATE";

		$arParam = array();
		array_push($arParam, $seq);
		$qry = "SELECT * FROM TMP_ADM_BACK WHERE SEQ = ?";
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
	
	// 등록
	function doProc(){
		if($('#TITLE').val() == ''){alert("제목을 입력해 주세요"); $('#TITLE').focus(); return false;}
		if($('#CONTENT').val() == ''){alert("내용을 입력해 주세요"); $('#CONTENT').focus(); return false;}
		
		var url = "adminProc.php";
		var param = $('#frm').serialize();
		
		$('#frm').ajaxForm({
			type:"POST",
			dataType : 'json',
			url:url,
			data: param,
			enctype: "multipart/form-data",
			async: false,
			success: function(obj){
				var strUrl = obj.url;
				if(pageaction == 'UPDATE') strUrl = strUrl +"?seq=<?=$seq?>&<?=$gStr2?>";
				
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

				<h2>관리자</h2>
				<div class='w900'><!-- 컨텐츠 가로길이 줄임-->
					<form enctype="multipart/form-data" method="post" name="frm" id="frm" action="notice_proc.php">
					<input type="hidden" name="pageaction" id="pageaction" value="<?=$pageaction?>">
					<input type="hidden" name="seq" id="seq" value="<?=$ds['SEQ']?>">
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
								<input name="ADM_ID" id="ADM_ID" type="text" class='w500' value="<?=$ds['ADM_ID']?>"/>
<?
	}
?>
							</td>
						</tr>
						<tr>
							<th scope="row">비밀번호</th>
							<td class='lf'><input name="ADM_PW" id="ADM_PW" type="password" class='w500' value="<?=$ds['ADM_PW']?>"/></td>
						</tr>
						<tr>
							<th scope="row">이름</th>
							<td class='lf'><input name="ADM_NM" id="ADM_NM" type="text" class='w500' value="<?=$ds['ADM_NM']?>"/></td>
						</tr>
						<tr>
							<th scope="row">전체관리자여부</th>
							<td class='lf'>
								<input name="SYSADMIN_YN" id="SYSADMIN_Y" type="radio" value="Y" <?=$ds['SYSADMIN_YN'] == 'Y' ? 'checked' : ''?> /> 전체관리자
								<span class='pl10'><input name="SYSADMIN_YN" id="SYSADMIN_N" type="radio" value="N" <?=$ds['SYSADMIN_YN'] == 'N' ? 'checked' : ''?> /> 일반관리자 </span>
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