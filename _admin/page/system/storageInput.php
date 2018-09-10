<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSession($url = '/_admin/');

	$_MENU1 = '1';		// 상단메뉴
	$_MENU2 = '7';		// 왼쪽메뉴

	$_NAVITITLE = "시스템관리 > 재고창고관리";	

	//=====================================================
	//== 도움말 - Get Parameters
	//=====================================================
	$storage_seq = $cFnc->getReq('storage_seq', '');

	$cal_1 = $cFnc->getReq('cal_1', date('Y-m-d', strtotime('-3 month')));
	$cal_2 = $cFnc->getReq('cal_2', date('Y-m-d'));	
	$storage_nm  = $cFnc->getReq('storage_nm', '');
	$manager_nm  = $cFnc->getReq('manager_nm', '');
	$use_yn = $cFnc->getReq('use_yn', '');
	$order_cont = $cFnc->getReq('order_cont', 'RATE_SEQ');
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
	$gStr = $cFnc->GetStr( $gStr, "storage_nm", $storage_nm );
	$gStr = $cFnc->GetStr( $gStr, "manager_nm", $manager_nm );	
	$gStr = $cFnc->GetStr( $gStr, "use_yn", $use_yn );
	$gStr = $cFnc->GetStr( $gStr, "order_cont", $order_cont );
	$gStr = $cFnc->GetStr( $gStr, "order_asc", $order_asc );
	$gStr = $cFnc->GetStr( $gStr, "search_list1", $search_list1 );

	$gStr2 = "";
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_1", $cal_1 );
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_2", $cal_2 );
	$gStr2 = $cFnc->GetStr( $gStr2, "storage_nm", $storage_nm );
	$gStr2 = $cFnc->GetStr( $gStr2, "manager_nm", $manager_nm );
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
	if($storage_seq != ''){
		$pageaction = "UPDATE";

		$arParam = array();
		array_push($arParam, $storage_seq);
		$qry = "SELECT * FROM TB_STORAGE WHERE STORAGE_SEQ = ?";
		$rslt = $cPdo->execQuery('data', $qry, $arParam);
		$ds = $rslt['data'];
	}


	include $_SERVER["DOCUMENT_ROOT"]. "/_admin/h.php";
?>

<script type="text/javascript" src="/js/jquery.form.js"></script>
<script src="http://dmaps.daum.net/map_js_init/postcode.v2.js"></script>	<!-- 다음 주소 API -->
<script>
	var pageaction = "<?=$pageaction?>";
	
	$('document').ready(function(){
		if(pageaction == 'INSERT') $('#USE_Y').attr('checked', true);
	});
	



	// 우편번호찾기
	function doPostSearch(){
		new daum.Postcode({
			oncomplete: function(data) {
				// 팝업에서 검색결과 항목을 클릭했을때 실행할 코드를 작성하는 부분입니다.
				// 예제를 참고하여 다양한 활용법을 확인해 보세요.
				var post_no = data.zonecode;
				var addr = "";
				
				if(data.userSelectedType == 'R'){
					addr = data.roadAddress;
				}
				else if(data.userSelectedType == 'J'){
					addr = data.jibunAddress;
				}
				
				$('#POST_NO').val(post_no);
				$('#ADDR').val(addr);
				$('#ADDR_DETAIL').focus();
			}
		}).open();
	}
	


	// 등록
	function doProc(){
		if($('#CATEGORY').val() == ''){alert("분류를 입력해 주세요"); $('#CATEGORY').focus(); return false;}
		if($('#STORAGE_NM').val() == ''){alert("창고명를 입력해 주세요"); $('#STORAGE_NM').focus(); return false;}		
		if($('input[name="USE_YN"]').is(':checked') == false){
			alert("사용여부를 체크해주세요");
			return false;
		}	

		var url = "storageProc.php";
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
				if(pageaction == 'UPDATE') strUrl = strUrl +"?storage_seq=<?=$storage_seq?>&<?=$gStr2?>";
				
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

				<h2><?=$_NAVITITLE?></h2>
				<div class='w900'><!-- 컨텐츠 가로길이 줄임-->
					<form method="post" name="frm" id="frm" action="systemProc.php">
					<input type="hidden" name="pageaction" id="pageaction" value="<?=$pageaction?>">
					<input type="hidden" name="storage_seq" id="storage_seq" value="<?=$storage_seq?>">
					<table border="0" width="100%" cellspacing="0" cellpadding="0" class='adtb_02 mt20 '>
						<colgroup>
							<col width='20%' />
							<col width='80%' />
						</colgroup>
						<tr>
							<th scope="row">분류</th>
							<td class='lf'><input name="CATEGORY" id="CATEGORY" type="text" class='w200' value="<?=$ds['CATEGORY']?>"/></td>
						</tr>
						<tr>
							<th scope="row">창고코드</th>
							<td class='lf'><input name="CODE_SEQ" id="CODE_SEQ" type="text" class='w200' value="<?=$ds['CODE_SEQ']?>" onkeypress="javascript:INPUT_ONLY_NUMBER('#CODE_SEQ');"/></td>
						</tr>						
						<tr>
							<th scope="row">창고명</th>
							<td class='lf'><input name="STORAGE_NM" id="STORAGE_NM" type="text" class='w200' value="<?=$ds['STORAGE_NM']?>"/></td>
						</tr>						
						<tr>
							<th scope="row">주소</th>
							<td class='lf'>
								<input type="button" value="우편번호찾기" onclick="javascript:doPostSearch();">
								<input name="POST_NO" id="POST_NO" type="text" class='w100' value="<?=$ds['POST_NO']?>" readonly />
								<input name="ADDR" id="ADDR" type="text" class='w350' value="<?=$ds['ADDR']?>" readonly />
								<br>
								<input name="ADDR_DETAIL" id="ADDR_DETAIL" type="text" class='w550' value="<?=$ds['ADDR_DETAIL']?>" style="margin-top:5px;" />
							</td>
						</tr>						
						<tr>
							<th scope="row">담당자명</th>
							<td class='lf'><input name="MANAGER_NM" id="MANAGER_NM" type="text" class='w200' value="<?=$ds['MANAGER_NM']?>"/></td>
						</tr>						
						<tr>
							<th scope="row">휴대폰</th>
							<td class='lf'><input name="TEL" id="TEL" type="text" class='w200' value="<?=$ds['TEL']?>"/></td>
						</tr>												
						<tr>
							<th scope="row">이메일</th>
							<td class='lf'><input name="EMAIL" id="EMAIL" type="text" class='w200' value="<?=$ds['EMAIL']?>"/></td>
						</tr>																		
						<tr>
							<th scope="row">사용여부</th>
							<td class='lf con'>
								<input name="USE_YN" id="USE_YN" type="radio" value="Y" <?=$ds['USE_YN'] == 'Y' ? 'checked' : ''?> /> 사용
								<span class='pl10'><input name="USE_YN" id="USE_YN" type="radio" value="N" <?=$ds['USE_YN'] == 'N' ? 'checked' : ''?> /> 중지 </span>
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