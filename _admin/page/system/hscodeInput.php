<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSession($url = '/_admin/');

	$_MENU1 = '1';		// 상단메뉴
	$_MENU2 = '8';		// 왼쪽메뉴

	//=====================================================
	//== 도움말 - Get Parameters
	//=====================================================
	$hscode_seq = $cFnc->getReq('hscode_seq', '');

	$cal_1 = $cFnc->getReq('cal_1', date('Y-m-d', strtotime('-3 month')));
	$cal_2 = $cFnc->getReq('cal_2', date('Y-m-d'));
	$hscode_nm  = $cFnc->getReq('hscode_nm', '');
	$use_yn = $cFnc->getReq('use_yn', '');
	$order_cont = $cFnc->getReq('order_cont', 'HSCODE_SEQ');
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
	$gStr = $cFnc->GetStr( $gStr, "hscode_nm", $hscode_nm );
	$gStr = $cFnc->GetStr( $gStr, "use_yn", $use_yn );
	$gStr = $cFnc->GetStr( $gStr, "order_cont", $order_cont );
	$gStr = $cFnc->GetStr( $gStr, "order_asc", $order_asc );
	$gStr = $cFnc->GetStr( $gStr, "search_list1", $search_list1 );

	$gStr2 = "";
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_1", $cal_1 );
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_2", $cal_2 );
	$gStr2 = $cFnc->GetStr( $gStr2, "hscode_nm", $hscode_nm );
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
	if($hscode_seq != ''){
		$pageaction = "UPDATE";

		$arParam = array();
		array_push($arParam, $hscode_seq);
		$qry = "SELECT * FROM TB_HSCODE WHERE HSCODE_SEQ = ?";
		$rslt = $cPdo->execQuery('data', $qry, $arParam);
		$ds = $rslt['data'];
	}


	include $_SERVER["DOCUMENT_ROOT"]. "/_admin/h.php";
?>

<script type="text/javascript" src="/js/jquery.form.js"></script>

<script>
	var pageaction = "<?=$pageaction?>";
	
	$('document').ready(function(){
		if(pageaction == 'INSERT') $('#use_y').attr('checked', true);
	});
	
	// 등록
	function doProc(){
		if($('#hscode_nm').val() == ''){alert("HSCODE이름을 입력해 주세요"); $('#hscode_nm').focus(); return false;}
		if($('#hscode_value').val() == ''){alert("HSCODE값을 입력해 주세요"); $('#hscode_value').focus(); return false;}
		if($('input[name="use_yn"]').is(':checked') == false){alert("사용여부를 체크해주세요"); return false;}
		
		var url = "hscodeProc.php";
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
				if(pageaction == 'UPDATE') strUrl = strUrl +"?hscode_seq=<?=$hscode_seq?>&<?=$gStr2?>";
				
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

				<h2>HSCODE관리</h2>
				<div class='w900'><!-- 컨텐츠 가로길이 줄임-->
					<form method="post" name="frm" id="frm">
					<input type="hidden" name="pageaction" id="pageaction" value="<?=$pageaction?>">	
					<input type="hidden" name="hscode_seq" id="hscode_seq" value="<?=$hscode_seq?>">	
					<table border="0" width="100%" cellspacing="0" cellpadding="0" class='adtb_02 mt20 '>
						<colgroup>
							<col width='20%' />
							<col width='80%' />
						</colgroup>
						<tr>
							<th scope="row">HSCODE번호</th>
							<td class='lf'>
<?
	if($pageaction == 'UPDATE'){
?>
								<?=$ds['HSCODE_SEQ']?>
<?
	}
?>
							</td>
						</tr>
						<tr>
							<th scope="row">HSCODE이름</th>
							<td class='lf'><input name="hscode_nm" id="hscode_nm" type="text" class='w200' value="<?=$ds['HSCODE_NM']?>"/></td>
						</tr>
						<tr>
							<th scope="row">HSCODE값</th>
							<td class='lf'><input name="hscode_value" id="hscode_value" type="text" class='w200' value="<?=$ds['HSCODE_VALUE']?>"/></td>
						</tr>
						<tr>
							<th scope="row">사용여부</th>
							<td class='lf con'>
								<input name="use_yn" id="use_y" type="radio" value="Y" <?=$ds['USE_YN'] == 'Y' ? 'checked' : ''?> /> 사용
								<span class='pl10'><input name="use_yn" id="use_n" type="radio" value="N" <?=$ds['USE_YN'] == 'N' ? 'checked' : ''?> /> 중지 </span>
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