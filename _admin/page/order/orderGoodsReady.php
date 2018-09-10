<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSession($url = '/_admin/');
	
	$_MENU1 = '4';		// 상단메뉴
	$_MENU2 = '3';		// 왼쪽메뉴

	$_NAVITITLE = "주문관리 > 상품준비";


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
	$name = $cFnc->getReq('name', '');
	$order_cont = $cFnc->getReq('order_cont', 'B.ITEM_SEQ');
	$order_asc = $cFnc->getReq('order_asc', 'DESC');
	$search_list1 = $cFnc->getReq('search_list1', '');
	
	//=====================================================
	//== 도움말 - Set Variables
	//=====================================================
	$cPdo = new cPdo($ARR_DB_INFO, true);
	
	// 페이징 세팅
	$nPageCnt = $cFnc->getReq('nPageCnt', 10);		// 블럭당 페이지 수
	$nListCnt = $cFnc->getReq('nListCnt', 500);		// 페이지당 리스트 수
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
	$gStr = $cFnc->GetStr( $gStr, "name", $name );
	$gStr = $cFnc->GetStr( $gStr, "order_cont", $order_cont );
	$gStr = $cFnc->GetStr( $gStr, "order_asc", $order_asc );
	$gStr = $cFnc->GetStr( $gStr, "search_list1", $search_list1 );

	$gStr2 = "";
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_1", $cal_1 );
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_2", $cal_2 );
	$gStr2 = $cFnc->GetStr( $gStr2, "orderid", $orderid );
	$gStr2 = $cFnc->GetStr( $gStr2, "name", $name );
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
		AND B.STATUS = 'C'
		AND A.CANCEL_YN <> 'Y'
	";
	array_push($arParam, $cal_1 .' 00:00:00');
	array_push($arParam, $cal_2 .' 23:59:59');


	if($orderid != ''){
		$qryWhere .= " AND A.ORDERID like ?";
		array_push($arParam, '%'. $orderid .'%');		
	}
	
	if($name != ''){
		$qryWhere .= " AND A.NAME like ?";
		array_push($arParam, '%'. $name .'%');		
	}
	
	$qry = "
		SELECT A.ORDER_SEQ, A.ORDERID, A.NAME
		, B.ITEM_SEQ, B.ITEM_ROWID, B.ITEMID, B.`PRODUCTNAME`, B.REG_DT, B.QTY, B.PRICE, B.SUMPRICE, B.DEPOSIT_DT, B.`STATUS`
		, B.READY_DT, B.WEARRD_DT, B.WEARSC_DT, B.GOODSRD_DT, B.GOODSSC_DT, B.GOODSCF_DT, B.OPTFIELD, B.OPTVALUE
		, (SELECT SUM(P_QTY) AS P_QTY FROM TB_ORDER_PURCHASE WHERE ITEM_SEQ = B.ITEM_SEQ AND DEL_YN = 'N') AS P_QTY
		, (SELECT SUM(CASE WHEN P_STATUS = 'P' AND DEL_YN = 'N' THEN P_QTY ELSE 0 END) FROM TB_ORDER_PURCHASE WHERE ITEM_SEQ = B.ITEM_SEQ) AS STATUS_P
		, (SELECT SUM(CASE WHEN P_STATUS = 'R' AND DEL_YN = 'N' THEN P_QTY ELSE 0 END) FROM TB_ORDER_PURCHASE WHERE ITEM_SEQ = B.ITEM_SEQ) AS STATUS_R
		, (SELECT SUM(CASE WHEN P_STATUS = 'E' AND DEL_YN = 'N' THEN P_QTY ELSE 0 END) FROM TB_ORDER_PURCHASE WHERE ITEM_SEQ = B.ITEM_SEQ) AS STATUS_E
		FROM TB_ORDER A
		INNER JOIN TB_ORDER_ITEM B ON B.ORDER_SEQ = A.`ORDER_SEQ`
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
				if(arrStatus[i] == '구매중'){
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
	
	
	// 인쇄
	function doPrint(){
		AREA_PRINT(".content");
	}
	
	
	// 엑셀저장
	function doExcel(){
		location.href = "orderGoodsReadyExcel.php?<?=$gStr?>";
	}
	
	
	// 캘린더세팅(오늘, 7일, 15일등등 버튼)
	function set_date(v1, v2){
		$('#cal_1').val(v1);
		$('#cal_2').val(v2);
	}
	
	
	// 구매정보팝업
	function popPurchase(item_seq, item_rowid){
		window.open('orderGoodsReadyPopList.php?item_seq='+ item_seq +'&item_rowid='+ item_rowid, 'out_req', 'width=1280, height=800');
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
						<th scope="row">주문자</th>
						<td class='lf'>
							<input name="name" id="name" type="text" class='w200' value="<?=$name?>" onkeypress="javascript:doEnter();" />
						</td>
						<!--
						<th scope="row">사용여부</th>
						<td class='lf'>
							<select name="use_yn" id="use_yn">
								<option value="">항목</option>
								<option value="Y" <?=$cFnc->CodeString($use_yn, 'Y', 'selected', '')?>>사용</option>
								<option value="N" <?=$cFnc->CodeString($use_yn, 'N', 'selected', '')?>>미사용</option>
							</select>						
						</td>
						-->
					</tr>
					<!--
					<tr>
						<th scope="row">이메일</th>
						<td class='lf'>
							<input name="email" id="email" type="text" class='w200' value="<?=$email?>" onkeypress="javascript:doEnter();" />
						</td>
						<th scope="row"></th>
						<td class='lf'></td>
					</tr>
					-->
				</table>
				</form>


				<div class='mt10'>
					<p class='fr'>
						<input type="button" name="btn_search" id="btn_search" value='조회' class='btn_01 w80' onclick="javascript:doSearch();" />
					</p>					
				</div>

				<div class="both"></div>

				<div class='mt10'>				
					<input type='button' value='인쇄' class='btn_02 w60' onclick="javascript:doPrint();" />
					<input type='button' value='액셀저장' class='btn_02 w80' onclick="javascript:doExcel();" />
					
					<p class='fr'>
						<input type='button' value='결제확인되돌리기' class='btn_02 w110' onclick="javascript:doProcReturn();"/>
						<input type='button' value='입고대기' class='btn_02 w60' onclick="javascript:doProc();"/>
					</p>
				</div>

				<table width="100%" border="0" cellspacing="0" cellpadding="0"	class='adtb_01 mt10'>
					<colgroup>
						<col width='5%' />
						<col width='5%' />
						<col width='12%' />
						<col width='12%' />
					</colgroup>
					<tr>
						<th scope="col"><input name="chkall" id="chkall" type="checkbox" value="" onclick="javascript:doChkall();" /></th>
						<th scope="col">NO</th>
						<th scope="col">주문번호</th>
						<th scope="col">아이템번호</th>
						<th scope="col">주문상품</th>
						<th scope="col">옵션필드(값)</th>
						<th scope="col">주문자</th>
						<th scope="col">수량</th>
						<th scope="col">단가</th>
						<th scope="col">결제금액</th>
						<th scope="col">등록일</th>
						<th scope="col">상품준비일</th>
						<th scope="col">구매확인</th>
					</tr>
<?
	if($nTotalCnt > 0){
		foreach($rsList as $i=>$ds){
			$PageS = $nTotalCnt - $i - (($nowPage - 1) * $nListCnt);
			
			$ds['REG_DT_STR'] = str_replace(' ', '<br>', $ds['REG_DT']);
			$ds['DEPOSIT_DT_STR'] = str_replace(' ', '<br>', $ds['DEPOSIT_DT']);
			$ds['READY_DT_STR'] = str_replace(' ', '<br>', $ds['READY_DT']);
			
			$p_status = '구매중';
			if($ds['STATUS_R'] > 0){
				if($ds['P_QTY'] == $ds['STATUS_R']) $p_status = '구매완료';
			}
			
			$p_qty = ($ds['P_QTY'] == '' ? '0' : $ds['P_QTY']);
?>
					<tr>
						<td><input name="chkitem" id="chkitem_<?=$ds['ITEM_SEQ']?>" type="checkbox" value="<?=$ds['ITEM_SEQ']?>" data-order_seq="<?=$ds['ORDER_SEQ']?>" data-p_status="<?=$p_status?>" /></td>
						<td><?=$PageS?></td>
						<td class='bg_01'><a href="javascript:goDetail('<?=$ds['ORDER_SEQ']?>');"><?=$ds['ORDERID']?></a></td>
						<td class='bg_01'><a href="javascript:goDetail('<?=$ds['ORDER_SEQ']?>');"><?=$ds['ITEMID']?></a></td>
						<td><?=$ds['PRODUCTNAME']?></td>
						<td>
							<?=$ds['OPTFIELD']?>
							<br>
							(<?=$ds['OPTVALUE']?>)
						</td>
						<td><?=$ds['NAME']?></td>
						<td><?=number_format($ds['QTY'])?></td>
						<td><?=number_format($ds['PRICE'])?></td>
						<td><?=number_format($ds['SUMPRICE'])?></td>
						<td><?=$ds['REG_DT_STR']?></td>
						<td><?=$ds['READY_DT_STR']?></td>
						<td class='bg_01'><a href="javascript:popPurchase('<?=$ds['ITEM_SEQ']?>', '<?=$ds['ITEM_ROWID']?>');"><?=$p_status?> (<?=$p_qty?>)</a></td>
					</tr>
<?
		}
	}
	else{
?>
					<tr>
						<td colspan="13" height="200">검색 결과가 없습니다</td>
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
	
	
	<!-- popup -->
	<div id="purchase" class="dialog-popup" style="width:600px;">
		<!-- ajax: popPurchase() -->
	</div>
	<!-- //popup -->
<?
	include $_SERVER["DOCUMENT_ROOT"]. "/_admin/f.php";
?>