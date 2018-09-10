<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSession($url = '/_admin/');
	
	$_MENU1 = '4';		// 상단메뉴
	$_MENU2 = '7';		// 왼쪽메뉴

	$_NAVITITLE = "주문관리 > 출고완료";


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
	$deliveryid = $cFnc->getReq('deliveryid', '');
	$name = $cFnc->getReq('name', '');
	$order_cont = $cFnc->getReq('order_cont', 'A.DELIVERY_SEQ');
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
	$gStr = $cFnc->GetStr( $gStr, "deliveryid", $deliveryid );
	$gStr = $cFnc->GetStr( $gStr, "name", $name );
	$gStr = $cFnc->GetStr( $gStr, "order_cont", $order_cont );
	$gStr = $cFnc->GetStr( $gStr, "order_asc", $order_asc );
	$gStr = $cFnc->GetStr( $gStr, "search_list1", $search_list1 );

	$gStr2 = "";
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_1", $cal_1 );
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_2", $cal_2 );
	$gStr2 = $cFnc->GetStr( $gStr2, "deliveryid", $deliveryid );
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
		AND C.STATUS = 'G' AND A.GOODSCF_YN <> 'Y'
		AND D.CANCEL_YN <> 'Y'
	";
	array_push($arParam, $cal_1 .' 00:00:00');
	array_push($arParam, $cal_2 .' 23:59:59');


	if($deliveryid != ''){
		$qryWhere .= " AND A.DELIVERYID like ?";
		array_push($arParam, '%'. $orderid .'%');
	}
	
	$qry = "
		SELECT A.`DELIVERY_SEQ`, A.`DELIVERYID`, A.REG_DT, A.WEIGHT, A.W_PRICE, A.MEMO, A.W_PRICE_P, A.W_PRICE_P_VND, A.COD, A.COD_VND
		, COUNT(B.`DELIVERY_ITEM_SEQ`) AS CNT, SUM(C.`SUMPRICE`) AS SUMPRICE, MAX(C.GOODSSC_DT) AS GOODSSC_DT
		, SUM(C.SUMPRICE_VND) AS SUMPRICE_VND, SUM(C.DEPOSITFEE) AS DEPOSITFEE, SUM(C.DEPOSITFEE_VND) AS DEPOSITFEE_VND
		FROM TB_DELIVERY A
		INNER JOIN TB_DELIVERY_ITEM B ON B.`DELIVERY_SEQ` = A.`DELIVERY_SEQ`
		INNER JOIN TB_ORDER_ITEM C ON C.ITEM_SEQ = B.`ITEM_SEQ`
		INNER JOIN TB_ORDER D ON D.ORDER_SEQ = C.ORDER_SEQ
		". $qryWhere ."
		GROUP BY A.`DELIVERY_SEQ`, A.`DELIVERYID`, A.REG_DT, A.WEIGHT, A.W_PRICE, A.MEMO
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

		if(confirm("선택한 항목의 구매확인완료를 진행하시겠습니까?")){
			var arrChk = new Array();
			$('input[name="chkitem"]:checked').each(function(){
				arrChk.push($(this).val());
			});
			
			$('#pageaction').val('OK');
			$('#arr_delivery_seq').val(arrChk);

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
	
	
	// 상세
	function goDetail(seq){
		window.open("orderOutDetail.php?delivery_seq="+ seq);
	}
	
	
	// 인쇄
	function doPrint(){
		AREA_PRINT(".content");
	}
	
	
	// 엑셀저장
	function doExcel(){
		location.href = "orderOutFinExcel.php?<?=$gStr?>";
	}
	
	
	// 캘린더세팅(오늘, 7일, 15일등등 버튼)
	function set_date(v1, v2){
		$('#cal_1').val(v1);
		$('#cal_2').val(v2);
	}
	
	
	// 입고명세서팝업
	function popBarcode(){
		if($('input[name="chkitem"]').is(':checked') == ''){alert("체크박스를 체크해 주세요"); return false;}
		$('#barcode_tb tr').remove();
		
		$('#barcode_pop').bPopup({
			follow: [false, false]
			, modalClose: false
			, escClose: false
		});
	}
	
	// 바코드생성
	function doMakeBarcode(){
		var arr_deliveryid = new Array();
		
		$('input[name="chkitem"]:checked').each(function(){
			arr_deliveryid.push($(this).data('deliveryid'));
		});
		
		var url = "_makeBarcodeAjax.php";
		var param = {
			arr_deliveryid: arr_deliveryid
			, barcode_type: $('input[name="barcode_type"]:checked').val()
			, barcode_step: 'out'
		};
		
		$.ajax({
			type:"POST",
			dataType : 'json',
			url: url,
			data: param,
			async: false,
			success: function(obj){
				//console.log(obj);
				var arr_data = obj.data;
				var strHtml = '';
				
				for(var i = 0; i < arr_data.length; i++){
					var main = arr_data[i];
					
					for(var j = 0; j < main.order.length; j++){
						var order = main.order[j];
						
						strHtml += '<tr>';
						strHtml += '	<th scope="row">주문번호</th>';
						strHtml += '	<td class="lf2">'+ order.orderid +'</td>';
						strHtml += '</tr>';
						strHtml += '<tr>';
						strHtml += '	<th scope="row">아이템번호</th>';
						strHtml += '	<td class="lf2">';
						
						for(var k = 0; k < order.item.length; k++){
							var itemid = order.item[k];
							
							if(k > 0) strHtml += '<br>';
							strHtml += itemid;
						}
						
						strHtml += '	</td>';
						strHtml += '</tr>';
					}
					
					
					strHtml += '<tr>';
					strHtml += '	<td colspan="2">';
					strHtml += '		<img src="<?=IMG_URL?>/barcode/'+ main.barcodefile +'" />';
					strHtml += '	</td>';
					strHtml += '</tr>';
				}
				
				$('#barcode_tb tr').remove();
				$('#barcode_tb').append(strHtml);
				OPEN_LAYER_POPUP('#barcode_pop');
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
	
	// 입고명세서인쇄
	function doPrintBarcode(){
		AREA_PRINT("#barcode_tb");
	}

	// CI, PL 엑셀저장
	function doCIPLExcel(delivery_seq){
		location.href = "_CI_PL_Excel.php?delivery_seq="+ delivery_seq;
	}
</script>

	<form method="post" name="frmProc" id="frmProc" action="">
		<input type="hidden" name="pageaction" id="pageaction" value="">
		<input type="hidden" name="arr_item_seq" id="arr_item_seq" value="">
		<input type="hidden" name="arr_delivery_seq" id="arr_delivery_seq" value="">
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
							<input name="deliveryid" id="deliveryid" type="text" class='w200' value="<?=$deliveryid?>" onkeypress="javascript:doEnter();" />
						</td>
						<th scope="row"></th>
						<td class='lf'></td>
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
					<input type='button' value='인쇄' class='btn_02 w80' onclick="javascript:doPrint();" />
					<input type='button' value='액셀저장' class='btn_02 w80' onclick="javascript:doExcel();" />
					
					<p class='fr'>
						<input type='button' value='구매확인완료' class='btn_02 w110' onclick="javascript:doProc();"/>
					</p>
				</div>
				
				<table width="100%" border="0" cellspacing="0" cellpadding="0"	class='adtb_01 mt10'>
					<colgroup>
						<col width='5%' />
						<col width='5%' />
						<col width='20%' />
					</colgroup>
					<tr>
						<th scope="col"><input name="chkall" id="chkall" type="checkbox" value="" onclick="javascript:doChkall();" /></th>
						<th scope="col">NO</th>
						<th scope="col">출고번호</th>
						<th scope="col">출고완료일</th>
						<th scope="col">아이템갯수</th>
						<th scope="col">무게</th>
						<th scope="col">배송사운송비</th>
						<th scope="col">고객운송비</th>
						<th scope="col">예상COD</th>
						<th scope="col">CI / PL</th>
					</tr>
<?
	if($nTotalCnt > 0){
		foreach($rsList as $i=>$ds){
			$PageS = $nTotalCnt - $i - (($nowPage - 1) * $nListCnt);
			
			$ds['GOODSSC_DT_STR'] = str_replace(' ', '<br>', $ds['GOODSSC_DT']);
?>
					<tr>
						<td><input name="chkitem" id="chkitem_<?=$ds['DELIVERY_SEQ']?>" type="checkbox" value="<?=$ds['DELIVERY_SEQ']?>" data-deliveryid="<?=$ds['DELIVERYID']?>" /></td>
						<td><?=$PageS?></td>
						<td class='bg_01'><a href="javascript:goDetail('<?=$ds['DELIVERY_SEQ']?>');"><?=$ds['DELIVERYID']?></a></td>
						<td><?=$ds['GOODSSC_DT_STR']?></td>
						<td><?=number_format($ds['CNT'])?></td>
						<td><?=number_format($ds['WEIGHT'], 2, ',', ',')?>KG</td>
						<td><?=number_format($ds['W_PRICE'])?>원</td>
						<td><?=number_format($ds['W_PRICE_P'])?>원<br>(<?=number_format($ds['W_PRICE_P_VND'])?>₫)</td>
						<td><?=number_format($ds['COD'])?>원<br>(<?=number_format($ds['COD_VND'])?>₫)</td>
						<td><input type='button' value='엑셀저장' class='btn_02 w60' onclick="javascript:doCIPLExcel('<?=$ds['DELIVERY_SEQ']?>');"/></td>
					</tr>
<?
		}
	}
	else{
?>
					<tr>
						<td colspan="10" height="200">검색 결과가 없습니다</td>
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