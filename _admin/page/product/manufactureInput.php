<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSession($url = '/_admin/');

	$_MENU1 = '5';		// 상단메뉴
	$_MENU2 = '1';		// 왼쪽메뉴

	$_NAVITITLE = "제품관리 > 유통사관리";		

	//=====================================================
	//== 도움말 - Get Parameters
	//=====================================================
	$manufacture_seq 	= $cFnc->getReq('manufacture_seq', '');

	$cal_1 				= $cFnc->getReq('cal_1', date('Y-m-d', strtotime('-3 month')));
	$cal_2 				= $cFnc->getReq('cal_2', date('Y-m-d'));
	$manufacturename  	= $cFnc->getReq('manufacturename', '');
	$order_cont 		= $cFnc->getReq('order_cont', 'MANUFACTURE_SEQ');
	$order_asc 			= $cFnc->getReq('order_asc', 'DESC');
	$search_list1 		= $cFnc->getReq('search_list1', '');
	
	
	$nPageCnt 	= $cFnc->getReq('nPageCnt', 10);		// 블럭당 페이지 수
	$nListCnt 	= $cFnc->getReq('nListCnt', 10);		// 페이지당 리스트 수
	$nowPage 	= $cFnc->getReq('nowPage', 1);			// 현재 페이지

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
	$gStr = $cFnc->GetStr( $gStr, "manufacturename", $manufacturename );
	$gStr = $cFnc->GetStr( $gStr, "use_yn", $use_yn );
	$gStr = $cFnc->GetStr( $gStr, "order_cont", $order_cont );
	$gStr = $cFnc->GetStr( $gStr, "order_asc", $order_asc );
	$gStr = $cFnc->GetStr( $gStr, "search_list1", $search_list1 );

	$gStr2 = "";
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_1", $cal_1 );
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_2", $cal_2 );
	$gStr2 = $cFnc->GetStr( $gStr2, "manufacturename", $manufacturename );
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
	if($manufacture_seq != ''){
		$pageaction = "UPDATE";

		$arParam = array();
		array_push($arParam, $manufacture_seq);
		$qry = "SELECT * FROM TB_MANUFACTURE WHERE MANUFACTURE_SEQ = ?";
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
		if($('#MANUFACTURENAME').val() == ''){alert("제조사명을 입력해 주세요"); $('#MANUFACTURENAME').focus(); return false;}
		if($('#USERNAME').val() == ''){alert("담당자명 입력해 주세요"); $('#USERNAME').focus(); return false;}


		var url = "manufactureProc.php";
		var param = $('#frm').serialize();
		
		$('#frm').ajaxForm({
			type:"POST",
			dataType : 'json',
			url:url,
			data: param,
			// enctype: "multipart/form-data",
			async: false,
			success: function(obj){
				var strUrl = obj.url;
				if(pageaction == 'UPDATE') strUrl = strUrl +"?manufacture_seq=<?=$manufacture_seq?>&<?=$gStr2?>";
				
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
					<form method="post" name="frm" id="frm" action="manufactureProc.php">
					<input type="hidden" name="pageaction" id="pageaction" value="<?=$pageaction?>">
					<input type="hidden" name="manufacture_seq" id="manufacture_seq" value="<?=$manufacture_seq?>">
					<table border="0" width="100%" cellspacing="0" cellpadding="0" class='adtb_02 mt20 '>
						<colgroup>
							<col width='20%' />
							<col width='80%' />
						</colgroup>
						<tr>
							<th scope="row">제조사명</th>
							<td class='lf'><input name="MANUFACTURENAME" id="MANUFACTURENAME" type="text" class='w200' value="<?=$ds['MANUFACTURENAME']?>"/></td>
						</tr>
						<tr>
							<th scope="row">담당자명</th>
							<td class='lf'><input name="USERNAME" id="USERNAME" type="text" class='w200' value="<?=$ds['USERNAME']?>"/></td>
						</tr>
						<tr>
							<th scope="row">이메일</th>
							<td class='lf'><input name="EMAIL" id="EMAIL" type="text" class='w200' value="<?=$ds['EMAIL']?>"/></td>
						</tr>
						<tr>
							<th scope="row">주소</th>
							<td class='lf'><input name="ADDR" id="ADDR" type="text" class='w600' value="<?=$ds['ADDR']?>"/></td>
						</tr>
						<tr>
							<th scope="row">연락처</th>
							<td class='lf'><input name="TEL" id="TEL" type="text" class='w200' value="<?=$ds['TEL']?>" onkeypress="javascript:INPUT_ONLY_NUMBER('#TEL');"/></td>
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