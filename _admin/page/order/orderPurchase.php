<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSession($url = '/_admin/');

	$_MENU1 = '4';		// 상단메뉴
	$_MENU2 = '10';		// 왼쪽메뉴

	$_NAVITITLE = "주문관리 > 구매현황";	

	$arr_date = array();
	array_push($arr_date, date('Y-m-d'));
	array_push($arr_date, date('Y-m-d', strtotime('-7 day')) );
	array_push($arr_date, date('Y-m-d', strtotime('-15 day')) );
	array_push($arr_date, date('Y-m-d', strtotime('-1 month')) );
	array_push($arr_date, date('Y-m-d', strtotime('-3 month')) );
	//=====================================================
	//== 도움말 - Get Parameters
	//=====================================================
	$cal_1 = $cFnc->getReq('cal_1', date('Y-m-d', strtotime('-3 month')));
	$cal_2 = $cFnc->getReq('cal_2', date('Y-m-d'));
	$item_seq = $cFnc->getReq('item_seq', '');
	$item_rowid = $cFnc->getReq('item_rowid', '');
	$order_cont = $cFnc->getReq('order_cont', 'A.DELIVERY_SEQ');
	$order_asc = $cFnc->getReq('order_asc', 'DESC');
	$search_list1 = $cFnc->getReq('search_list1', '');
	$itemid = $cFnc->getReq('itemid', '');
	$productname = $cFnc->getReq('productname', '');
	$p_status = $cFnc->getReq('p_status', '');
	
	// =====================================================
	// Set Variables
	// =====================================================
	$cPdo = new cPdo($ARR_DB_INFO);
	
	// 페이징 세팅
	$nPageCnt = $cFnc->getReq('nPageCnt', 10);		// 블럭당 페이지 수
	$nListCnt = $cFnc->getReq('nListCnt', 20);		// 페이지당 리스트 수
	$nowPage = $cFnc->getReq('nowPage', 1);			// 현재 페이지
	$cPdo->setPagingInfo($nListCnt, $nPageCnt);
	
	// =====================================================
	// Set Parameters (Get Types)
	// =====================================================
	$gStr = "";
	$gStr = $cFnc->GetStr( $gStr, "cal_1", $cal_1 );
	$gStr = $cFnc->GetStr( $gStr, "cal_2", $cal_2 );
	$gStr = $cFnc->GetStr( $gStr, "item_seq", $item_seq );
	$gStr = $cFnc->GetStr( $gStr, "item_rowid", $item_rowid );
	$gStr = $cFnc->GetStr( $gStr, "itemid", $itemid );
	$gStr = $cFnc->GetStr( $gStr, "productname", $productname );
	$gStr = $cFnc->GetStr( $gStr, "nListCnt", $nListCnt );
	
	$gStr2 = "";
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_1", $cal_1 );
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_2", $cal_2 );
	$gStr2 = $cFnc->GetStr( $gStr2, "item_seq", $item_seq );
	$gStr2 = $cFnc->GetStr( $gStr2, "item_rowid", $item_rowid );
	$gStr2 = $cFnc->GetStr( $gStr2, "itemid", $itemid );
	$gStr2 = $cFnc->GetStr( $gStr2, "productname", $productname );
	$gStr2 = $cFnc->GetStr( $gStr2, "nListCnt", $nListCnt );
	$gStr2 = $cFnc->GetStr( $gStr2, "nowPage", $nowPage );
	$gStr2 = $cFnc->GetStr( $gStr2, "nPageCnt", $nPageCnt );

	// =====================================================
	// Start Tran
	// =====================================================
	// 주문아이템
	$arParam = array();
	$qryWhere = "WHERE A.REG_DT BETWEEN ? AND ? ";
	array_push($arParam, $cal_1 .' 00:00:00');
	array_push($arParam, $cal_2 .' 23:59:59');
	
	if($item_seq != ''){
		$qryWhere = " AND A.ITEM_SEQ = ?";
		array_push($arParam, $item_seq);
	}
	if($itemid != ''){
		$qryWhere .= " AND B.ITEMID like ?";
		array_push($arParam, '%'. $itemid .'%');
	}
	if($productname != ''){
		$qryWhere .= " AND B.PRODUCTNAME like ?";
		array_push($arParam, '%'. $productname .'%');
	}
	if($p_status != ''){
		$qryWhere .= " AND P_STATUS = ?";
		array_push($arParam, $p_status);
	}
	
	$qry = "
		SELECT B.ITEM_SEQ, B.ITEM_ROWID, B.ITEMID, B.PRODUCTNAME
		, A.PURCHASE_SEQ, A.P_LINKURL, A.P_QTY, A.P_PRICE, A.P_DELIVERYFEE, A.P_PRICESUM, A.P_OPTFIELD, A.`P_OPTVALUE`, A.`P_MEMO`, A.P_STATUS, A.REG_DT, A.MOD_DT, A.P_DISCOUNT
		, C.ORDERID
		FROM TB_ORDER_PURCHASE A
		INNER JOIN TB_ORDER_ITEM B ON B.ITEM_SEQ = A.ITEM_SEQ
		INNER JOIN TB_ORDER C ON C.ORDER_SEQ = B.ORDER_SEQ
		". $qryWhere ."
		ORDER BY B.ITEM_SEQ ASC, A.PURCHASE_SEQ DESC
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
		$rs = $result['data'];
	}
	//echo json_encode($rs);exit;
	
	include $_SERVER["DOCUMENT_ROOT"]. "/_admin/h.php";
?>

<script type="text/javascript" src="/js/jquery.form.js"></script>

<script>
	$('document').ready(function(){
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
	});
	
	// 체크박스[전체체크]
	function doChkall(){
		if($('#chkall').is(':checked') == true){
			$('input[name="chkitem"]').attr('checked', true);
		}
		else{
			$('input[name="chkitem"]').attr('checked', false);
		}
	}
	
	// 취소
	function doCancel(){
		window.close();
	}
	
	// 등록
	function goInsert(){
		location.href = "orderGoodsReadyPopInput.php?<?=$gStr?>";
	}
	
	// 수정
	function goUpdate(purchase_seq){
		location.href = "orderGoodsReadyPopInput.php?<?=$gStr?>&purchase_seq="+ purchase_seq;
	}
	
	// 삭제
	function doDelete(){
		if($('input[name="chkitem"]').is(':checked') == ''){alert("체크박스를 체크해 주세요"); return false;}
		
		if(confirm("삭제하시겠습니까?")){
			var sum_purchase_seq = '';
			$('input[name="chkitem"]').each(function(i){
				if(i > 0) sum_purchase_seq = sum_purchase_seq +',';
				sum_purchase_seq = sum_purchase_seq + $(this).val();
			});
			
			$('#pageaction').val('delete');
			$('#sum_purchase_seq').val(sum_purchase_seq);
			
			var url = "orderGoodsReadyPopProc.php";
			var param = $('#frm').serialize();
			
			$.ajax({
				type:"POST",
				dataType : 'json',
				url: url,
				data: param,
				async: false,
				success: function(obj){
					if(obj.status == 0){
						// 실패
						alert(obj.msg);
					}
					else{
						// 성공
						alert(obj.msg);
						window.opener.location.reload();
						location.reload();
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
	}
	/*
	// 입고처리
	function doEnter(){
		if($('input[name="chkitem"]').is(':checked') == ''){alert("체크박스를 체크해 주세요"); return false;}
		
		if(confirm("입고처리 하시겠습니까?")){
			var sum_purchase_seq = '';
			$('input[name="chkitem"]').each(function(i){
				if(i > 0) sum_purchase_seq = sum_purchase_seq +',';
				sum_purchase_seq = sum_purchase_seq + $(this).val();
			});
			
			$('#pageaction').val('enter');
			$('#sum_purchase_seq').val(sum_purchase_seq);
			
			var url = "orderGoodsReadyPopProc.php";
			var param = $('#frm').serialize();
			
			$.ajax({
				type:"POST",
				dataType : 'json',
				url: url,
				data: param,
				async: false,
				success: function(obj){
					if(obj.status == 0){
						// 실패
						alert(obj.msg);
					}
					else{
						// 성공
						alert(obj.msg);
						window.opener.location.reload();
						location.reload();
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
	}
	*/
	// 검색
	function doSearch(type, value){
		if(type != undefined){
			$('#search_list1').val(type +','+ value);
		}
		
		$('#frmSearch').submit();
	}
	
	// 캘린더세팅(오늘, 7일, 15일등등 버튼)
	function set_date(v1, v2){
		$('#cal_1').val(v1);
		$('#cal_2').val(v2);
	}
	
	// 엔터키이벤트
	function onEnter(){
		if(event.keyCode == 13){
			doSearch();
		}
	}
	
	// 입고처리
	function doEnter(){
		if($('input[name="chkitem"]').is(':checked') == ''){alert("체크박스를 체크해 주세요"); return false;}
		
		if(confirm("입고처리 하시겠습니까?")){
			var sum_purchase_seq = '';
			$('input[name="chkitem"]:checked').each(function(i){
				if(i > 0) sum_purchase_seq = sum_purchase_seq +',';
				sum_purchase_seq = sum_purchase_seq + $(this).val();
			});
			
			$('#pageaction').val('enter');
			$('#sum_purchase_seq').val(sum_purchase_seq);
			
			var url = "orderGoodsReadyPopProc.php";
			var param = $('#frm').serialize();
			
			$.ajax({
				type:"POST",
				dataType : 'json',
				url: url,
				data: param,
				async: false,
				success: function(obj){
					if(obj.status == 0){
						// 실패
						alert(obj.msg);
					}
					else{
						// 성공
						alert(obj.msg);
						location.reload();
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
	}
	
	// 구매정보팝업
	function popPurchase(purchase_seq){
		window.open('orderPurchaseDetailPop.php?purchase_seq='+ purchase_seq, 'purchase_detail', 'width=1280, height=800');
	}
</script>
	
	<form method="post" name="frm" id="frm">
	<input type="hidden" name="pageaction" id="pageaction" value="">
	<input type="hidden" name="sum_purchase_seq" id="sum_purchase_seq" value="">
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
				<input type="hidden" name="search_list1" id="search_list1" value="<?=$search_list1?>">
				<input type="hidden" name="order_cont" id="order_cont" value="<?=$order_cont?>">
				<input type="hidden" name="order_asc" id="order_asc" value="<?=$order_asc?>">
				<table border="0" width="100%" cellspacing="0" cellpadding="0" class='adtb_02 '>
					<colgroup>
						<col width='20%' />
						<col width='30%' />
						<col width='20%' />
						<col width='30%' />
					</colgroup>
					<tr>
						<th scope="row">기간검색</th>
						<td class='lf' colspan=3>
							<input name="cal_1" id="cal_1" type="text" class='w100' value='<?=$cal_1?>' readonly /> ~ <input name="cal_2" id="cal_2" type="text" class='w100' value='<?=$cal_2?>' readonly />
							<input type="button" value="오늘" class='btn_s_03 w60' onclick="javascript:set_date('<?=$arr_date[0]?>','<?=$arr_date[0]?>');">
							<input type="button" value="7일" class='btn_s_03 w60' onclick="javascript:set_date('<?=$arr_date[1]?>','<?=$arr_date[0]?>');">
							<input type="button" value="15일" class='btn_s_03 w60' onclick="javascript:set_date('<?=$arr_date[2]?>','<?=$arr_date[0]?>');">
							<input type="button" value="1개월" class='btn_s_03 w60' onclick="javascript:set_date('<?=$arr_date[3]?>','<?=$arr_date[0]?>');">
							<input type="button" value="3개월" class='btn_s_03 w60' onclick="javascript:set_date('<?=$arr_date[4]?>','<?=$arr_date[0]?>');">
						</td>
					</tr>					
					<tr>
						<th scope="row">아이템번호</th>
						<td class='lf'>
							<input name="itemid" id="itemid" type="text" class='w200' value="<?=$itemid?>" onkeypress="javascript:onEnter();" />
						</td>
						<th scope="row">주문상품</th>
						<td class='lf'>
							<input name="productname" id="productname" type="text" class='w200' value="<?=$productname?>" onkeypress="javascript:onEnter();" />
						</td>
					</tr>
					<tr>
						<th scope="row">상태</th>
						<td class='lf'>
							<select name="p_status" id="p_status">
								<option value="">전체</option>
								<option value="P" <?=$cFnc->CodeString($p_status, 'P', 'selected', '')?>>구매중</option>
								<option value="R" <?=$cFnc->CodeString($p_status, 'R', 'selected', '')?>>입고대기</option>
								<option value="E" <?=$cFnc->CodeString($p_status, 'E', 'selected', '')?>>입고완료</option>
							</select>
						</td>
						<th scope="row"></th>
						<td class='lf'></td>
					</tr>
				</table>
				
				
				<div class='mt10'>
					<p class='fl'>
						<select name="nListCnt" id="nListCnt" onchange="javascript:doSearch();">
							<option value="5" <?=$cFnc->CodeString($nListCnt, 5, 'selected', '')?>>5</option>
							<option value="10" <?=$cFnc->CodeString($nListCnt, 10, 'selected', '')?>>10</option>
							<option value="20" <?=$cFnc->CodeString($nListCnt, 20, 'selected', '')?>>20</option>
							<option value="30" <?=$cFnc->CodeString($nListCnt, 30, 'selected', '')?>>30</option>
							<option value="50" <?=$cFnc->CodeString($nListCnt, 50, 'selected', '')?>>50</option>
							<option value="100" <?=$cFnc->CodeString($nListCnt, 100, 'selected', '')?>>100</option>
						</select>
					</p>
					
					<p class='fr'>
						<input type="button" name="btn_search" id="btn_search" value='조회' class='btn_01 w80' onclick="javascript:doSearch();" />
					</p>
				</div>
				
				<div class="both"></div>
				
				<div class='mt10'>
					<p class='fr'>
						<input type='button' value='입고처리' class='btn_02 w60' onclick="javascript:doEnter();"/>
					</p>
				</div>
				</form>
				
				<div class="both"></div>
				
				<table width="100%" border="0" cellspacing="0" cellpadding="0"	class='adtb_01 mt10'>
					<colgroup>
						<col width='5%' />
						<col width='5%' />
						<col width='12%' />
						<col width='12%' />
						<col width='25%' />
					</colgroup>
					<tr>
						<th scope="col"><input name="chkall" id="chkall" type="checkbox" value="" onclick="javascript:doChkall();" /></th>
						<th scope="col">NO</th>
						<th scope="col">아이템번호</th>
						<th scope="col">주문상품<br>옵션필드(값)</th>
						<th scope="col">구매URL</th>
						<th scope="col">수량</th>
						<th scope="col">단가</th>
						<th scope="col">배송비</th>
						<th scope="col">할인금액</th>
						<th scope="col">합계</th>
						<th scope="col">상태</th>
						<th scope="col">등록일</th>
					</tr>
<?
	if($nTotalCnt > 0){
		foreach($rs as $i=>$ds){
			$PageS = $nTotalCnt - $i - (($nowPage - 1) * $nListCnt);
			
			$ds['REG_DT_STR'] = str_replace(' ', '<br>', $ds['REG_DT']);
			
			$p_status = CODE_PURCHASE_STATUS($ds['P_STATUS']);
?>
					<tr>
						<td><input name="chkitem" id="chkitem_<?=$ds['PURCHASE_SEQ']?>" type="checkbox" value="<?=$ds['PURCHASE_SEQ']?>" /></td>
						<td><?=$PageS?></td>
						<td class='bg_01'><a href="javascript:popPurchase('<?=$ds['PURCHASE_SEQ']?>');"><?=$ds['ITEMID']?></a></td>
						<td>
							<?=$ds['PRODUCTNAME']?>
							<br>
							<font color="red"><?=$ds['P_OPTFIELD']?>
							<br>
							(<?=$ds['P_OPTVALUE']?>)
							</font>
						</td>
						<td><a href="<?=$ds['P_LINKURL']?>" target="_blank"><?=$ds['P_LINKURL']?></a></td>
						<td><?=number_format($ds['P_QTY'])?></td>
						<td><?=number_format($ds['P_PRICE'])?></td>
						<td><?=number_format($ds['P_DELIVERYFEE'])?></td>
						<td>-<?=number_format($ds['P_DISCOUNT'])?></td>
						<td><?=number_format($ds['P_PRICESUM'])?></td>
						<td><?=CODE_PURCHASE_STATUS($ds['P_STATUS'])?></td>
						<td><?=$ds['REG_DT_STR']?></td>
					</tr>
<?
		}
	}
	else{
?>
					<tr>
						<td colspan="12" height="200">검색 결과가 없습니다</td>
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