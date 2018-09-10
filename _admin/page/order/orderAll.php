<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSession($url = '/_admin/');
	
	$_MENU1 = '4';		// 상단메뉴
	$_MENU2 = '9';		// 왼쪽메뉴

	$_NAVITITLE = "주문관리 > 통합주문현황";


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
	$orderid = $cFnc->getReq('orderid', '');
	$itemid = $cFnc->getReq('itemid', '');
	$deliveryid = $cFnc->getReq('deliveryid', '');
	$productname = $cFnc->getReq('productname', '');
	$status = $cFnc->getReq('status', '');
	$name = $cFnc->getReq('name', '');
	$cancel_yn = $cFnc->getReq('cancel_yn', '');
	$order_cont = $cFnc->getReq('order_cont', 'B.ITEM_SEQ');
	$order_asc = $cFnc->getReq('order_asc', 'DESC');
	$search_list1 = $cFnc->getReq('search_list1', '');
	
	//=====================================================
	//== 도움말 - Set Variables
	//=====================================================
	$cPdo = new cPdo($ARR_DB_INFO, true);
	
	// 페이징 세팅
	$nPageCnt = $cFnc->getReq('nPageCnt', 10);		// 블럭당 페이지 수
	$nListCnt = $cFnc->getReq('nListCnt', 20);		// 페이지당 리스트 수
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
	$gStr = $cFnc->GetStr( $gStr, "orderid", $orderid );
	$gStr = $cFnc->GetStr( $gStr, "itemid", $itemid );
	$gStr = $cFnc->GetStr( $gStr, "deliveryid", $deliveryid );
	$gStr = $cFnc->GetStr( $gStr, "productname", $productname );
	$gStr = $cFnc->GetStr( $gStr, "status", $status );
	$gStr = $cFnc->GetStr( $gStr, "name", $name );
	$gStr = $cFnc->GetStr( $gStr, "cancel_yn", $cancel_yn );
	$gStr = $cFnc->GetStr( $gStr, "order_cont", $order_cont );
	$gStr = $cFnc->GetStr( $gStr, "order_asc", $order_asc );
	$gStr = $cFnc->GetStr( $gStr, "search_list1", $search_list1 );

	$gStr2 = "";
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_1", $cal_1 );
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_2", $cal_2 );
	$gStr2 = $cFnc->GetStr( $gStr2, "orderid", $orderid );
	$gStr2 = $cFnc->GetStr( $gStr2, "itemid", $itemid );
	$gStr2 = $cFnc->GetStr( $gStr2, "deliveryid", $deliveryid );
	$gStr2 = $cFnc->GetStr( $gStr2, "productname", $productname );
	$gStr2 = $cFnc->GetStr( $gStr2, "status", $status );
	$gStr2 = $cFnc->GetStr( $gStr2, "name", $name );
	$gStr2 = $cFnc->GetStr( $gStr2, "cancel_yn", $cancel_yn );
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
	$qryWhere = "
		WHERE A.REG_DT BETWEEN ? AND ?
	";
	array_push($arParam, $cal_1 .' 00:00:00');
	array_push($arParam, $cal_2 .' 23:59:59');


	if($orderid != ''){
		$qryWhere .= " AND A.ORDERID like ?";
		array_push($arParam, '%'. $orderid .'%');		
	}
	if($itemid != ''){
		$qryWhere .= " AND B.ITEMID like ?";
		array_push($arParam, '%'. $itemid .'%');		
	}
	if($deliveryid != ''){
		$qryWhere .= " AND DLV.DELIVERYID like ?";
		array_push($arParam, '%'. $deliveryid .'%');		
	}
	
	if($productname != ''){
		$qryWhere .= " AND B.PRODUCTNAME like ?";
		array_push($arParam, '%'. $productname .'%');		
	}
	if($name != ''){
		$qryWhere .= " AND A.NAME like ?";
		array_push($arParam, '%'. $name .'%');		
	}
	if($status != ''){
		$qryWhere .= " AND B.STATUS = ?";
		array_push($arParam, $status);
		
		if($status == 'END'){
			$qryWhere .= " AND B.GOODSCF_YN = 'Y'";
		}
		else{
			$qryWhere .= " AND B.GOODSCF_YN = 'N'";
		}
	}
	if($cancel_yn != ''){
		$qryWhere .= " AND A.CANCEL_YN = ?";
		array_push($arParam, $cancel_yn);
	}
	
	$qry = "
		SELECT A.ORDER_SEQ, A.ORDERID, A.CANCEL_YN, B.ITEM_SEQ, B.ITEMID, DLV.DELIVERY_SEQ, DLV.DELIVERYID
		, B.`PRODUCTNAME`, B.OPTFIELD, B.OPTVALUE, A.`NAME`, B.QTY, B.PRICE, B.SUMPRICE, B.STATUS, B.GOODSCF_YN
		, B.REG_DT, B.DEPOSIT_DT, B.READY_DT, B.WEARRD_DT, B.WEARSC_DT, B.GOODSRD_DT, B.GOODSSC_DT, B.GOODSCF_DT
		, DLV.REAL_WEIGHT, DLV.REAL_W_PRICE, DLV.HBL_CD
		, OP.`PURCHASE_SEQ`, OP.P_QTY, OP.P_PRICE, OP.P_DELIVERYFEE, OP.P_DISCOUNT, OP.P_OPTFIELD, OP.P_OPTVALUE, OP.P_STATUS, OP.APPROVAL_DT, OP.APPROVAL_NO
		FROM TB_ORDER A
		INNER JOIN TB_ORDER_ITEM B ON B.ORDER_SEQ = A.`ORDER_SEQ`
		LEFT OUTER JOIN (
			SELECT D.DELIVERY_SEQ, D.DELIVERYID, DI.ITEM_SEQ, D.REAL_WEIGHT, D.REAL_W_PRICE, D.HBL_CD
			FROM TB_DELIVERY D
			INNER JOIN TB_DELIVERY_ITEM DI ON DI.DELIVERY_SEQ = D.DELIVERY_SEQ
		) DLV ON DLV.ITEM_SEQ = B.ITEM_SEQ
		LEFT OUTER JOIN TB_ORDER_PURCHASE OP ON OP.`ITEM_SEQ` = B.`ITEM_SEQ`
		". $qryWhere ."
		ORDER BY A.ORDER_SEQ DESC, B.ITEM_SEQ ASC, OP.PURCHASE_SEQ ASC
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
	
	
	// 상태변경
	function doProc(){
		if($('input[name="chkitem"]').is(':checked') == ''){alert("체크박스를 체크해 주세요"); return false;}

		if(confirm("선택한 항목의 상태를 변경하시겠습니까?")){
			var arrChk = new Array();
			var arrStatus = new Array();
			$('input[name="chkitem"]:checked').each(function(){
				arrChk.push($(this).val());
				arrStatus.push($(this).data('p_status'));
			});
			
			var nChk = 0;
			for(var i = 0; i < arrStatus.length; i++){
				if(arrStatus[i] == '미확인'){
					nChk++;
				}
			}
			
			if(nChk > 0){alert("구매확인이 안된 상품이 있습니다"); return false;}
			
			$('#pageaction').val('enter_ready');
			$('#arr_item_seq').val(arrChk);
			$('#status').val('D');

			$.ajax({
				type:"POST",
				dataType : 'json',
				url: "orderProc.php",
				data: $('#frmProc').serialize(),
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
	
	
	// 상태되돌리기
	function doProcReturn(){
		if($('input[name="chkitem"]').is(':checked') == ''){alert("체크박스를 체크해 주세요"); return false;}

		if(confirm("선택한 항목의 상태를 이전상태로 되돌리고\n구매정보가 있을시 구매정보가 모두 삭제됩니다")){
			var arrChk = new Array();
			$('input[name="chkitem"]:checked').each(function(){
				arrChk.push($(this).val());
			});
			
			$('#pageaction').val('pay');
			$('#arr_item_seq').val(arrChk);
			$('#status').val('B');

			$.ajax({
				type:"POST",
				dataType : 'json',
				url: "orderProcRtn.php",
				data: $('#frmProc').serialize(),
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
	
	
	// 상세
	function goDetail(seq){
		window.open("orderDetail.php?order_seq="+ seq);
	}
	
	// 출고상세
	function goDetailDelivery(delivery_seq){
		window.open("orderOutDetail.php?delivery_seq="+ delivery_seq);
	}
	
	// 인쇄
	function doPrint(){
		AREA_PRINT(".content");
	}
	
	
	// 엑셀저장
	function doExcel(){
		location.href = "orderAllExcel.php?<?=$gStr?>";
	}
	
	
	// 캘린더세팅(오늘, 7일, 15일등등 버튼)
	function set_date(v1, v2){
		$('#cal_1').val(v1);
		$('#cal_2').val(v2);
	}
	
	
	// 구매확인팝업
	function popPurchase(item_seq, item_rowid){
		window.open('orderGoodsReadyPopList.php?item_seq='+ item_seq +'&item_rowid='+ item_rowid, 'out_req', 'width=1280, height=800');
		
		/*
		var url = "orderGoodsReadyPop.php";
		var param = {
			item_seq: item_seq
		};
		
		$.ajax({
			type:"POST",
			dataType : 'html',
			url: url,
			data: param,
			async: false,
			success: function(obj){
				$('#purchase').html(obj);
				OPEN_LAYER_POPUP('#purchase');
			},
			error: function(request, status, error){
				alert('Find Error -> '+ status);
				return false;
				//console.log(request);
				//console.log(status);
				//console.log(error);
			}
		});
		*/
	}

</script>

	<form method="post" name="frmProc" id="frmProc" action="">
		<input type="hidden" name="pageaction" id="pageaction" value="">
		<input type="hidden" name="arr_item_seq" id="arr_item_seq" value="">
		<input type="hidden" name="item_seq" id="item_seq" value="">
		<input type="hidden" name="status" id="status" value="">
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
						<th scope="row">주문번호</th>
						<td class='lf'>
							<input name="orderid" id="orderid" type="text" class='w200' value="<?=$orderid?>" onkeypress="javascript:doEnter();" />
						</td>
						<th scope="row">주문상품</th>
						<td class='lf'>
							<input name="productname" id="productname" type="text" class='w200' value="<?=$productname?>" onkeypress="javascript:doEnter();" />
						</td>
					</tr>
					<tr>
						<th scope="row">아이템번호</th>
						<td class='lf'>
							<input name="itemid" id="itemid" type="text" class='w200' value="<?=$itemid?>" onkeypress="javascript:doEnter();" />
						</td>
						<th scope="row">주문자</th>
						<td class='lf'>
							<input name="name" id="name" type="text" class='w200' value="<?=$name?>" onkeypress="javascript:doEnter();" />
						</td>
					</tr>
					<tr>
						<th scope="row">출고번호</th>
						<td class='lf'>
							<input name="deliveryid" id="deliveryid" type="text" class='w200' value="<?=$deliveryid?>" onkeypress="javascript:doEnter();" />
						</td>
						<th scope="row">상태</th>
						<td class='lf'>
							<select name="status" id="status">
								<option value="">전체</option>
								<option value="A" <?=$cFnc->CodeString($status, 'A', 'selected', '')?>>주문접수</option>
								<option value="B" <?=$cFnc->CodeString($status, 'B', 'selected', '')?>>결제확인</option>
								<option value="C" <?=$cFnc->CodeString($status, 'C', 'selected', '')?>>상품준비</option>
								<option value="D" <?=$cFnc->CodeString($status, 'D', 'selected', '')?>>입고대기</option>
								<option value="E" <?=$cFnc->CodeString($status, 'E', 'selected', '')?>>입고완료</option>
								<option value="F" <?=$cFnc->CodeString($status, 'F', 'selected', '')?>>출고요청</option>
								<option value="G" <?=$cFnc->CodeString($status, 'G', 'selected', '')?>>출고완료</option>
								<option value="END" <?=$cFnc->CodeString($status, 'END', 'selected', '')?>>구매확인완료</option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">취소여부</th>
						<td class='lf'>
							<select name="cancel_yn" id="cancel_yn">
								<option value="">전체</option>
								<option value="Y" <?=$cFnc->CodeString($cancel_yn, 'Y', 'selected', '')?>>주문취소</option>
								<option value="N" <?=$cFnc->CodeString($cancel_yn, 'N', 'selected', '')?>>주문중</option>
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
					<input type='button' value='인쇄' class='btn_02 w60' onclick="javascript:doPrint();" />
					<input type='button' value='액셀저장' class='btn_02 w80' onclick="javascript:doExcel();" />
				</div>
				</form>
				
				<!-- scroll_sample -->
				<div class='scroll'>
					<div style='width:2500px;'>
						<table width="100%" border="0" cellspacing="0" cellpadding="0"	class='adtb_01'>
							<colgroup>
								<col width='3%' />
								<col width='7%' />
								<col width='7%' />
								<col width='15%' />
								<col width='10%' />
								<col width='*' />
								<col width='*' />
								<col width='*' />
								<col width='*' />
								<col width='*' />
								<col width='*' />
								<col width='7%' />
								<col width='*' />
								<col width='*' />
								<col width='*' />
								<col width='*' />
								<col width='*' />
							</colgroup>
							<tr>
								<th scope="col">NO</th>
								<th scope="col">주문번호</th>
								<th scope="col">아이템번호</th>
								<th scope="col">주문상품</th>
								<th scope="col">옵션필드(값)</th>
								<th scope="col">주문자</th>
								<th scope="col">수량</th>
								<th scope="col">단가</th>
								<th scope="col">결제금액</th>
								<th scope="col">결제승인일</th>
								<th scope="col">승인번호(카드)</th>
								<th scope="col">출고번호</th>
								<th scope="col">실제중량</th>
								<th scope="col">실제운송비</th>
								<th scope="col">H.B/L NO</th>
								<th scope="col">상태</th>
								<th scope="col">취소여부</th>
							</tr>
<?
	$str = "";
	
	$l_orderid = "";
	$l_old_orderid = "";
	$rowspan = 1;
	$bRow = false;
	
	$l_itemid = "";
	$l_old_itemid = "";
	$rowspan2 = 1;
	$bRow2 = false;
	
	$l_deliveryid = "";
	$l_old_deliveryid = "";
	$rowspan3 = 1;
	$bRow3 = false;
	
	$real_weight = 0;
	$real_w_price = 0;
	$hbl_cd = '';
	
	if($nTotalCnt > 0){
		foreach($rsList as $i=>$ds){
			$PageS = $nTotalCnt - $i - (($nowPage - 1) * $nListCnt);
			
			$p_status = CODE_ORDER_STATUS($ds['STATUS']);
			if($ds['STATUS'] == 'G' && $ds['GOODSCF_YN'] == 'Y'){
				$p_status = '구매확인완료';
			}
			
			// order단위
			if ($l_orderid != $ds["ORDERID"]){
				if($l_orderid == ""){
					$l_old_orderid = 'r_'.$ds["ORDERID"];
				}
				else {
					$l_old_orderid = 'r_'.$l_orderid;
				}
				
				$l_orderid = $ds["ORDERID"];
				$l_new_orderid = 'r_'.$ds["ORDERID"];
				$bRow = true;
				
				if($rowspan >= 1) {
					$str = str_replace($l_old_orderid, $rowspan, $str);
				}
				
				$rowspan = 1;
			}
			else {
				$bRow = false;
				$rowspan++;
			}
			
			if($bRow){
				$strRowspan = "<td class=\"bg_01\" rowspan=\"". $l_new_orderid ."\"><a href=\"javascript:goDetail('". $ds['ORDER_SEQ'] ."');\">". $ds['ORDERID'] ."</a></td>";
			} else {
				$strRowspan = "";
			}
			
			// item단위
			if ($l_itemid != $ds["ITEMID"]){
				if($l_itemid == ""){
					$l_old_itemid = 'r_'.$ds["ITEMID"];
				}
				else {
					$l_old_itemid = 'r_'.$l_itemid;
				}
				
				$l_itemid = $ds["ITEMID"];
				$l_new_itemid = 'r_'.$ds["ITEMID"];
				$bRow2 = true;
				
				if($rowspan2 >= 1) {
					$str = str_replace($l_old_itemid, $rowspan2, $str);
				}
				
				$rowspan2 = 1;
			}
			else {
				$bRow2 = false;
				$rowspan2++;
			}
			
			if($bRow2){
				$strRowspan2 = "<td class=\"bg_01\" rowspan=\"". $l_new_itemid ."\"><a href=\"javascript:goDetail('". $ds['ORDER_SEQ'] ."');\">". $ds['ITEMID'] ."</a></td>";
				$strRowspan2_b1 = "<td rowspan=\"". $l_new_itemid ."\">". number_format($ds['QTY']) ."</td>";
				$strRowspan2_b2 = "<td rowspan=\"". $l_new_itemid ."\">". number_format($ds['PRICE']) ."</td>";
				$strRowspan2_b3 = "<td rowspan=\"". $l_new_itemid ."\">". number_format($ds['SUMPRICE']) ."</td>";
				
			} else {
				$strRowspan2 = "";
				$strRowspan2_b1 = "";
				$strRowspan2_b2 = "";
				$strRowspan2_b3 = "";
			}
			
			// delivery단위
			if ($l_deliveryid != $ds["DELIVERYID"]){
				if($l_deliveryid == ""){
					$l_old_deliveryid = 'r_'.$ds["DELIVERYID"];
				}
				else {
					$l_old_deliveryid = 'r_'.$l_deliveryid;
				}
				
				$l_deliveryid = $ds["DELIVERYID"];
				$l_new_deliveryid = 'r_'.$ds["DELIVERYID"];
				$bRow3 = true;
				
				if($rowspan3 >= 1) {
					$str = str_replace($l_old_deliveryid.'real1', $real_weight, $str);
					$str = str_replace($l_old_deliveryid.'real2', $real_w_price, $str);
					$str = str_replace($l_old_deliveryid.'real3', $hbl_cd, $str);
					$str = str_replace($l_old_deliveryid, $rowspan3, $str);
				}
				
				$rowspan3 = 1;
			}
			else {
				$bRow3 = false;
				$rowspan3++;
			}
			
			if($bRow3){
				$real_weight = $ds['REAL_WEIGHT'];
				$real_w_price = number_format($ds['REAL_W_PRICE']);
				$hbl_cd = $ds['HBL_CD'];
				
				if($real_weight == '') $real_weight = 0;
				
				$strRowspan3 = "<td class=\"bg_01\" rowspan=\"". $l_new_deliveryid ."\"><a href=\"javascript:goDetailDelivery('". $ds['DELIVERY_SEQ'] ."');\">". $ds['DELIVERYID'] ."</a></td>";
				$strRowspan3_b1 = "<td rowspan=\"". $l_new_deliveryid ."\">". $l_new_deliveryid ."real1</td>";
				$strRowspan3_b2 = "<td rowspan=\"". $l_new_deliveryid ."\">". $l_new_deliveryid ."real2</td>";
				$strRowspan3_b3 = "<td rowspan=\"". $l_new_deliveryid ."\">". $l_new_deliveryid ."real3</td>";
				
				if($ds['DELIVERYID'] == ''){
					$strRowspan3_b1 = "<td rowspan=\"". $l_new_deliveryid ."\"></td>";
					$strRowspan3_b2 = "<td rowspan=\"". $l_new_deliveryid ."\"></td>";
					$strRowspan3_b3 = "<td rowspan=\"". $l_new_deliveryid ."\"></td>";
				}
			}
			else {
				$real_weight = 0;
				$real_w_price = 0;
				$hbl_cd = '';
				
				$strRowspan3 = "<td></td>";
				$strRowspan3_b1 = "<td></td>";
				$strRowspan3_b2 = "<td></td>";
				$strRowspan3_b3 = "<td></td>";
				
				if($ds['DELIVERYID'] != ''){
					$strRowspan3 = "";
					$strRowspan3_b1 = "";
					$strRowspan3_b2 = "";
					$strRowspan3_b3 = "";
				}
			}
			
			$str .= "
				<tr>
					<td>". $PageS ."</td>
					". $strRowspan ."
					". $strRowspan2 ."
					<td>". $ds['PRODUCTNAME'] ." [". $bRow2 ."]</td>
					<td>
						". $ds['OPTFIELD'] ."
						<br>
						(". $ds['OPTVALUE'] .")
					</td>
					<td>". $ds['NAME'] ."</td>
					". $strRowspan2_b1 ."
					". $strRowspan2_b2 ."
					". $strRowspan2_b3 ."
					<td>". $ds['APPROVAL_DT'] ."</td>
					<td>". $ds['APPROVAL_NO'] ."</td>
					". $strRowspan3 ."
					". $strRowspan3_b1 ."
					". $strRowspan3_b2 ."
					". $strRowspan3_b3 ."
					<td>". $p_status ."</td>
					<td>". CODE_ORDER_CANCEL_YN($ds['CANCEL_YN']) ."</td>
				</tr>
			";
		}
		
		$str = str_replace($l_new_orderid, $rowspan, $str);
		
		$str = str_replace($l_new_itemid, $rowspan2, $str);
		
		$str = str_replace($l_new_deliveryid.'real1', $real_weight, $str);
		$str = str_replace($l_new_deliveryid.'real2', $real_w_price, $str);
		$str = str_replace($l_new_deliveryid.'real3', $hbl_cd, $str);
		$str = str_replace($l_new_deliveryid, $rowspan3, $str);
	}
	else{
		$str = "
			<tr>
				<td colspan=\"17\" height=\"200\">검색 결과가 없습니다</td>
			</tr>
		";
	}
	
	echo $str;
?>
						</table>
						<!-- scroll_sample -->
					</div>
				</div>
				
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