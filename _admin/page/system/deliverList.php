<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSession($url = '/_admin/');
	
	$_MENU1 = '1';		// 상단메뉴
	$_MENU2 = '4';		// 왼쪽메뉴

	$_NAVITITLE = "시스템관리 > 배송비관리";	
	
	//=====================================================
	//== 도움말 - Get Parameters
	//=====================================================
	$cal_1 = $cFnc->getReq('cal_1', date('Y-m-d', strtotime('-3 month')));
	$cal_2 = $cFnc->getReq('cal_2', date('Y-m-d'));
	// $system_nm  = $cFnc->getReq('system_nm', '');
	$use_yn = $cFnc->getReq('use_yn', '');
	$order_cont = $cFnc->getReq('order_cont', 'COST_SEQ');
	$order_asc = $cFnc->getReq('order_asc', 'DESC');
	$search_list1 = $cFnc->getReq('search_list1', '');
	
	//=====================================================
	//== 도움말 - Set Variables
	//=====================================================
	$cPdo = new cPdo($ARR_DB_INFO, true);
	
	// 페이징 세팅
	$nPageCnt = $cFnc->getReq('nPageCnt', 10);		// 블럭당 페이지 수
	$nListCnt = $cFnc->getReq('nListCnt', 100);		// 페이지당 리스트 수
	$nowPage = $cFnc->getReq('nowPage', 1);			// 현재 페이지
	$cPdo->setPagingInfo($nListCnt, $nPageCnt);
	
	// 리스트 정렬 화살표
	$arrow_{$order_cont} = "▼";
	if($order_asc == 'ASC') $arrow_{$order_cont} = "▲";
	
	// 리스트 드롭다운
	$arr_search_list1 = explode(',', $search_list1);
	$search_list1_type = $arr_search_list1[0];
	$search_list1_val = $arr_search_list1[1];
	
	// =====================================================
	// Set Parameters (Get Types)
	// =====================================================
	$gStr = "";
	$gStr = $cFnc->GetStr( $gStr, "cal_1", $cal_1 );
	$gStr = $cFnc->GetStr( $gStr, "cal_2", $cal_2 );
	// $gStr = $cFnc->GetStr( $gStr, "system_nm", $system_nm );
	$gStr = $cFnc->GetStr( $gStr, "use_yn", $use_yn );
	$gStr = $cFnc->GetStr( $gStr, "order_cont", $order_cont );
	$gStr = $cFnc->GetStr( $gStr, "order_asc", $order_asc );
	$gStr = $cFnc->GetStr( $gStr, "search_list1", $search_list1 );

	$gStr2 = "";
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_1", $cal_1 );
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_2", $cal_2 );
	// $gStr2 = $cFnc->GetStr( $gStr2, "system_nm", $system_nm );
	$gStr2 = $cFnc->GetStr( $gStr2, "use_yn", $use_yn );
	$gStr2 = $cFnc->GetStr( $gStr2, "order_cont", $order_cont );
	$gStr2 = $cFnc->GetStr( $gStr2, "order_asc", $order_asc );
	$gStr2 = $cFnc->GetStr( $gStr2, "search_list1", $search_list1 );
	$gStr2 = $cFnc->GetStr( $gStr2, "nListCnt", $nListCnt );
	$gStr2 = $cFnc->GetStr( $gStr2, "nowPage", $nowPage );
	$gStr2 = $cFnc->GetStr( $gStr2, "nPageCnt", $nPageCnt );
	
	//=====================================================
	//== 도움말 - Start Tran
	//=====================================================
	$arParam = Array();
	$qryOrder = "ORDER BY ". $order_cont ." ". $order_asc;
	$qryWhere = "WHERE 1=1 ";
	//array_push($arParam, $cal_1 .' 00:00:00');
	//array_push($arParam, $cal_2 .' 23:59:59');

/*
	if($system_nm != ''){
		$qryWhere .= " AND SYSTEM_NM like ?";
		array_push($arParam, '%'. $system_nm .'%');		
	}
	
	if($use_yn != ''){
		$qryWhere .= " AND USE_YN = ?";
		array_push($arParam, $use_yn);
	}*/
	
	$qry = "
		SELECT COST_SEQ, COST, REG_DT, ADM_SEQ, COST_NM, USE_YN, COST_UP, MOD_DT, COST_TYPE, OBJ_TP
		, DATE_FORMAT(REG_DT, '%Y-%m-%d') AS REG_DT_STR
		, DATE_FORMAT(MOD_DT, '%Y-%m-%d') AS MOD_DT_STR
		FROM TB_DELIVER_COST		
		". $qryWhere ."
		". $qryOrder ."
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->getCntExec($qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$dsCount = $result['data'];			// 전체카운트
	
	if(is_array($dsCount)){
		$nTotalCnt = $dsCount["total"];
		$nTotalPage = $dsCount["page"];
		
		$qry .= " LIMIT ". (($nowPage - 1) * $nListCnt) .",". $nListCnt;
		$result = $cPdo->execQuery('list', $qry, $arParam);
		$rsList = $result['data'];
	}
	
	
	include $_SERVER["DOCUMENT_ROOT"]. "/_admin/h.php";
?>

<script type="text/javascript" src="/js/jquery.form.js"></script>

<script type="text/javascript">
	$(document).ready(function() {
		// 달력 플러그인
		$('#cal_1, #cal_2').datepicker({
			changeYear: false,
			changeMonth: false,
			minDate: '<?=date("Y-m-d", strtotime("-12 month"))?>',
			maxDate: '<?=date("Y-m-d")?>',
			numberOfMonths: 1,
			showOn:"button",
			buttonImage:"/_admin/images/icon_calendar.png",
			buttonImageOnly:true
		});

		// 리스트 드롭다운 버튼
		$(".item_use").click(function(){
			$(".sel_use").toggle();
		});

		$('.th_sel').css({display:'none'});
		// 리스트 드롭다운 버튼
	});

	// 검색
	function doSearch(type, value){
		if(type != undefined){
			$('#search_list1').val(type +','+ value);
		}

		$('#frmSearch').submit();
	}

	// 정렬
	function doOrder(cont){
		var order_asc = $('#order_asc').val();
		var order_cont = $('#order_cont').val();

		if(cont == order_cont){
			if(order_asc == 'DESC') order_asc = 'ASC';
			else if(order_asc == 'ASC') order_asc = 'DESC';
		}
		else{
			order_asc = 'DESC';
		}

		$('#order_cont').val(cont);
		$('#order_asc').val(order_asc);
		$('#frmSearch').submit();
	}

	// 엔터키이벤트
	function doEnter(){
		if(event.keyCode == 13){
			doSearch();
		}
	}

	// 체크박스[전체체크]
	function doChkall(){
		if($('#chkall').is(':checked') == true){
			$('input[name="chkitem"]').attr('checked', true);
		}
		else{
			$('input[name="chkitem"]').attr('checked', false);
		}
	}


	// 수정
	function goUpdate(deposit_seq){
		location.href = "depositInput.php?deposit_seq=" + deposit_seq + "&<?=$gStr2?>";
	}

	// 입력
	function goInput(){
		location.href = "depositInput.php?<?=$gStr2?>";
	}
	

	function doInput(){
		if($('#COST').val() == ''){alert("금액을 입력해 주세요"); $('#COST').focus(); return false;}
		
		if($('#OBJ_TP option:selected').val() == 'C'){
			if($('#COST_UP').val() == ''){alert("오름단위를 입력해 주세요"); $('#COST_UP').focus(); return false;}
		}
		
		$('#cost').val($('#COST').val());		
		$('#cost_up').val($('#COST_UP').val());	
		$('#cost_type').val($("#COST_TYPE option:selected").val());
		$('#obj_tp').val($("#OBJ_TP option:selected").val());
		$('#pageaction').val('INSERT');


		var url = "deliverProc.php";
		var param = $('#frmProc').serialize();

		$('#frmProc').ajaxForm({
			type:"POST",
			dataType : 'json',
			url:url,
			data: param,			
			async: false,
			success: function(obj){
				var strUrl = obj.url;				
				
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
		
		$('#frmProc').submit();

	}


	function doUpdate(cost_seq, cost_type, obj_tp){
		

		$('#cost_seq').val(cost_seq);
		$('#cost_type').val(cost_type);
		$('#obj_tp').val(obj_tp);
		$('#pageaction').val('UPDATE');

		var url = "deliverProc.php";
		var param = $('#frmProc').serialize();
		
		$('#frmProc').ajaxForm({
			type:"POST",
			dataType : 'json',
			url:url,
			data: param,			
			async: false,
			success: function(obj){
				var strUrl = obj.url;				
				
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
		
		$('#frmProc').submit();

	}
	
	// 상세
	function goDetail(system_cd){
		location.href = "systemView.php?system_cd="+ system_cd +"&<?=$gStr2?>";
	}
	
	// 파일업로드
	function doUploadFile(){
		$('#FILE').trigger('click');
	}
	
	// 파일업로드 파일선택 후
	function onFileUpload(){
		$('#FILE_TEXT').val($('#FILE').val());
	}
</script>

	<form method="post" name="frmProc" id="frmProc" action="">
		<input type="hidden" name="pageaction" id="pageaction" value="">
		<input type="hidden" name="rate" id="rate" value="">
		<input type="hidden" name="cost" id="cost" value="">
		<input type="hidden" name="cost_up" id="cost_up" value="">
		<input type="hidden" name="cost_seq" id="cost_seq" value="">
		<input type="hidden" name="cost_type" id="cost_type" value="">
		<input type="hidden" name="obj_tp" id="obj_tp" value="">
	</form>
	
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
				<form method="get" name="frmSearch" id="frmSearch" action="<?=$_SERVER['PHP_SELF']?>">
				<input type="hidden" name="pageaction" id="pageaction" value="<?=$pageaction?>">	
				<input type="hidden" name="search_list1" id="search_list1" value="<?=$search_list1?>">
				<input type="hidden" name="order_cont" id="order_cont" value="<?=$order_cont?>">
				<input type="hidden" name="order_asc" id="order_asc" value="<?=$order_asc?>">
				<table border="0" width="100%" cellspacing="0" cellpadding="0" class='adtb_02 '>
					<colgroup>
						<col width='20%' />
						<col width='80%' />
						<!-- <col width='20%' />
						<col width='30%' /> -->
					</colgroup>										
					<tr>
						<th scope="row">금액</th>
						<td class='lf'>
							<input name="COST" id="COST" type="text" class='w200' value="<?=$COST?>" onkeypress="javascript:doEnter();" onkeyup="javascript:INPUT_ONLY_NUMBER('#COST');"/>
						</td>
					</tr>
					<tr>
						<th scope="row">오름단위(1~9)</th>
						<td class='lf'>
							<input name="COST_UP" id="COST_UP" type="text" class='w200' value="<?=$COST_UP?>" onkeypress="javascript:doEnter();" maxlength=1 onkeyup="javascript:INPUT_ONLY_NUMBER('#COST_UP');"/>
						</td>
					</tr>
					<tr>
						<th scope="row">배송구분</th>
						<td class='lf'>
							<select name="COST_TYPE" id="COST_TYPE">
								<option value="O">구매대행</option>
								<option value="D">배송대행</option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">적용대상</th>
						<td class='lf'>
							<select name="OBJ_TP" id="OBJ_TP">
								<option value="P">고객</option>
								<option value="C">배송사</option>
							</select>
						</td>
					</tr>
				</table>
				</form>

				<div class='mt10'>
					<p class='tc'>
						<input type="button" name="btn_search" id="btn_search" value='등록' class='btn_01 w80' onclick="javascript:doInput();" />
					</p>
				</div>

				<div class="both"></div>

				<table width="100%" border="0" cellspacing="0" cellpadding="0"	class='adtb_01 mt10'>
					<colgroup>
						<col width='5%' />
						<col width='5%' />
					</colgroup>
					<tr>
						<th scope="col"><input name="chkall" id="chkall" type="checkbox" value="" onclick="javascript:doChkall();" /></th>
						<th scope="col">NO</th>
						<th scope="col">배송구분</th>
						<th scope="col">적용대상</th>
						<th scope="col">배송비</th>
						<th scope="col">반올림단위</th>
						<th scope="col">등록일 </th>
						<th scope="col">사용여부 </th>
						<th scope="col">ACTION </th>
					</tr>
<?
	if($nTotalCnt > 0){
		foreach($rsList as $i=>$ds){
			$PageS = $nTotalCnt - $i - (($nowPage - 1) * $nListCnt);
			
			$ds['REG_DT_STR'] = substr($ds['REG_DT'], 0, 10);
?>
					<tr>
						<td><input name="chkitem" id="chkitem_<?=$ds['COST_SEQ']?>" type="checkbox" value="<?=$ds['COST_SEQ']?>" /></td>
						<td><?=$PageS?></td>
						<td><?=CODE_DELIVERY_COST_TYPE($ds['COST_TYPE'])?></td>
						<td><?=CODE_DELIVERY_OBJ_TYPE($ds['OBJ_TP'])?></td>
						<td><?=number_format($ds['COST'])?></td>
						<td><?=$ds['COST_UP']?></td>
						<td><?=$ds['REG_DT_STR']?></td>
						<td><?=CODE_USE_YN($ds['USE_YN'])?></td>						
						<td>														
							<input type='button' value='즉시적용' class='btn_02 w80' onclick="javascript:doUpdate('<?=$ds['COST_SEQ']?>', '<?=$ds['COST_TYPE']?>', '<?=$ds['OBJ_TP']?>');"/>
						</td>						
					</tr>
<?
		}
	}
	else{
?>
					<tr>
						<td colspan="7" height="200">검색 결과가 없습니다</td>
					</tr>
<?
	}
?>
				</table>



				<!-- paging -->
				<?=$cFnc->getAdmPaging( $nPageCnt, $nowPage, $nTotalPage, $gStr )?>
				<!-- //paging -->

			
				
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