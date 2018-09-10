<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSession($url = '/_admin/');

	$_MENU1 = '4';		// 상단메뉴
	$_MENU2 = '';		// 왼쪽메뉴

	$_NAVITITLE = "주문관리 > 주문상세보기";	

	//=====================================================
	//== 도움말 - Get Parameters
	//=====================================================
	$order_seq = $cFnc->getReq('order_seq', '');

	// =====================================================
	// Set Variables
	// =====================================================
	$cPdo = new cPdo($ARR_DB_INFO);
	
	// =====================================================
	// Set Parameters (Get Types)
	// =====================================================
	$gStr = "";
	$gStr = $cFnc->GetStr( $gStr, "cal_1", $cal_1 );

	// =====================================================
	// Start Tran
	// =====================================================
	// 주문상세정보
	$arParam = array();
	array_push($arParam, $order_seq);
	$qry = "
		SELECT A.ORDER_SEQ, A.`ORDERID`, A.`NAME`, A.REG_DT, B.`DEPOSIT_DT`, A.PRICE AS PRICE_TOT, A.`DEPOSITFEE` AS DEPOSITFEE_TOT, B.STATUS, A.`DST_ADDR`, A.`MEMO`, A.CANCEL_YN, A.CANCEL_MEMO, A.CANCEL_DT
		, B.ITEM_SEQ, B.ITEMID, B.`LINKURL`, B.`OPTFIELD`, B.`OPTVALUE`, B.QTY, B.PRICE, B.`SUMPRICE`, B.`DEPOSITFEE`, B.PRODUCTNAME
		, SUM(C.P_QTY) AS P_QTY, SUM(C.P_PRICE) AS P_PRICE, SUM(C.P_DELIVERYFEE) AS P_DELIVERYFEE, SUM(C.P_PRICESUM) AS P_PRICESUM
		, A.PRICE_VND AS PRICE_VND_ORD, A.DEPOSITFEE_VND AS DEPOSITFEE_VND_ORD, B.PRICE_VND AS PRICE_VND_ITEM, B.SUMPRICE_VND AS SUMPRICE_VND_ITEM, B.DEPOSITFEE_VND AS DEPOSITFEE_VND_ITEM
		FROM TB_ORDER A
		INNER JOIN TB_ORDER_ITEM B ON B.ORDER_SEQ = A.`ORDER_SEQ`
		LEFT OUTER JOIN TB_ORDER_PURCHASE C ON C.ITEM_SEQ = B.ITEM_SEQ
		WHERE A.ORDER_SEQ = ?
		GROUP BY A.ORDER_SEQ, A.`ORDERID`, A.`NAME`, A.REG_DT, B.`DEPOSIT_DT`, A.PRICE, A.`DEPOSITFEE`, B.STATUS, A.`DST_ADDR`, A.`MEMO`, A.CANCEL_YN, A.CANCEL_MEMO, A.CANCEL_DT
		, B.ITEM_SEQ, B.ITEMID, B.`LINKURL`, B.`OPTFIELD`, B.`OPTVALUE`, B.QTY, B.PRICE, B.`SUMPRICE`, B.`DEPOSITFEE`, B.PRODUCTNAME
		ORDER BY B.PRODUCTNAME
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('list', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$rs = $result['data'];
	
	$cancel_flag = 'Y';
	if($rs[0]['CANCEL_YN'] == 'Y') $cancel_flag = 'N';
		
	foreach($rs as $i=>$ds){
		$rs[$i]['REG_DT_STR'] = str_replace(' ', '<br>', $ds['REG_DT']);
		$rs[$i]['DEPOSIT_DT_STR'] = str_replace(' ', '<br>', $ds['DEPOSIT_DT']);
		
		if($ds['STATUS'] == 'F' || $ds['STATUS'] == 'G') $cancel_flag = 'N';
	}
	
	
	// 주문출고내역
	$arParam = array();
	array_push($arParam, $order_seq);
	$qry = "
		SELECT D.DELIVERY_SEQ, D.`DELIVERYID`, D.WEIGHT, D.W_PRICE, D.`MEMO`, B.GOODSRD_DT, B.GOODSSC_DT, B.STATUS
		FROM TB_ORDER A
		INNER JOIN TB_ORDER_ITEM B ON B.ORDER_SEQ = A.`ORDER_SEQ`
		INNER JOIN TB_DELIVERY_ITEM C ON C.ITEM_SEQ = B.`ITEM_SEQ`
		INNER JOIN TB_DELIVERY D ON D.DELIVERY_SEQ = C.DELIVERY_SEQ
		WHERE A.ORDER_SEQ = ?
		GROUP BY D.DELIVERY_SEQ, D.`DELIVERYID`, D.WEIGHT, D.W_PRICE, D.`MEMO`, B.GOODSRD_DT, B.GOODSSC_DT, B.STATUS
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('list', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$rsDelivery = $result['data'];
	
	
	$arParam = array();
	array_push($arParam, $order_seq);
	$qry = "
		SELECT A.ORDERID, B.ITEMID, D.DELIVERY_SEQ
		FROM TB_ORDER A
		INNER JOIN TB_ORDER_ITEM B ON B.ORDER_SEQ = A.`ORDER_SEQ`
		INNER JOIN TB_DELIVERY_ITEM C ON C.ITEM_SEQ = B.`ITEM_SEQ`
		INNER JOIN TB_DELIVERY D ON D.DELIVERY_SEQ = C.DELIVERY_SEQ
		WHERE A.ORDER_SEQ = ?
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('list', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$rsDeliveryItem = $result['data'];
	
	foreach($rsDelivery as $i=>$ds){
		$idx = 0;
		
		foreach($rsDeliveryItem as $j=>$dsItem){
			if($ds['DELIVERY_SEQ'] == $dsItem['DELIVERY_SEQ']){
				$rsDelivery[$i]['ITEM'][$idx] = $dsItem;
				$idx++;
			}
		}
		
		$rsDelivery[$i]['ITEM_CNT'] = $idx;
	}
	
	
	// 주문히스토리
	$arParam = array();
	array_push($arParam, $order_seq);
	$qry = "
		SELECT A.*
		, B.ITEMID
		, C.ADM_ID, C.ADM_NM
		FROM TB_ORDER_STATUS A
		INNER JOIN TB_ORDER_ITEM B ON B.ITEM_SEQ = A.ITEM_SEQ
		INNER JOIN TB_ADM C ON C.ADM_SEQ = A.ADM_SEQ
		WHERE B.ORDER_SEQ = ?
		ORDER BY A.ITEM_SEQ ASC, A.STATUS_SEQ ASC
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('list', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$rsStatus = $result['data'];
	
	
	include $_SERVER["DOCUMENT_ROOT"]. "/_admin/h.php";
?>

<script type="text/javascript" src="/js/jquery.form.js"></script>

<script>
	$('document').ready(function(){
		
	});
	
	// 취소
	function doCancel(){
		window.close();
	}
	
	// 주문취소
	function doOrderCancel(order_seq){
		if(confirm("주문의 취소처리를 하시겠습니까?")){
			$.ajax({
				type:"POST",
				dataType : 'json',
				url: "orderProc.php",
				data: {
					order_seq: order_seq
					, cancel_memo: $('#cancel_memo').val()
					, pageaction: 'cancel'
				},
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
	
	// 인쇄
	function doPrint(){
		AREA_PRINT("#print_area");
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
				
				<div class='mt10'>				
					<input type='button' value='인쇄' class='btn_02 w60' onclick="javascript:doPrint();" />
				</div>
				
				<div class='w100p' id="print_area"><!-- 컨텐츠 가로길이 줄임-->
					<div class='mt20'>
						<h2>1. 주문기본정보</h2>
					</div>
					<table border="0" width="100%" cellspacing="0" cellpadding="0" class='adtb_02 mt10'>
						<colgroup>
							<col width='20%' />
						</colgroup>
						<tr>
							<th scope="row">주문번호</th>
							<th scope="row">주문자</th>
							<th scope="row">주문일</th>
							<th scope="row">결제확인일</th>
							<th scope="row">총결제금액</th>
							<th scope="row">총결제금액(VND)</th>
							<th scope="row">디파짓피</th>
							<th scope="row">디파짓피(VND)</th>
						</tr>
						<tr>
							<td class='ct'><?=$rs[0]['ORDERID']?></td>
							<td class='ct'><?=$rs[0]['NAME']?></td>
							<td class='ct'><?=$rs[0]['REG_DT_STR']?></td>
							<td class='ct'><?=$rs[0]['DEPOSIT_DT_STR']?></td>
							<td class='rt'><?=number_format($rs[0]['PRICE_TOT'])?></td>
							<td class='rt'><?=number_format($rs[0]['PRICE_VND_ORD'])?></td>
							<td class='rt'><?=number_format($rs[0]['DEPOSITFEE_TOT'])?></td>
							<td class='rt'><?=number_format($rs[0]['DEPOSITFEE_VND_ORD'])?></td>
						</tr>
					</table>
					
					<div class='mt20'>
						<h2>2. 주문출고내역</h2>
					</div>
					<table border="0" width="100%" cellspacing="0" cellpadding="0" class='adtb_02 mt10'>
						<colgroup>
							<col width='15%' />
							<col width='15%' />
						</colgroup>
						<tr>
							<th scope="row">출고번호</th>
							<th scope="row">아이템번호</th>
							<th scope="row">상태</th>
							<th scope="row">출고요청일</th>
							<th scope="row">출고완료일</th>
							<th scope="row">무게</th>
							<th scope="row">운송비</th>
							<th scope="row">메모</th>
						</tr>
<?
	if(count($rsDelivery) > 0){
		foreach($rsDelivery as $i=>$ds){
			$ds['GOODSRD_DT_STR'] = str_replace(' ', '<br>', $ds['GOODSRD_DT']);
			$ds['GOODSSC_DT_STR'] = str_replace(' ', '<br>', $ds['GOODSSC_DT']);
?>
						<tr>
							<td class='ct'><?=$ds['DELIVERYID']?></td>
							<td class='ct'>
<?
			for($j = 0; $j < $ds['ITEM_CNT']; $j++){
				if($j > 0) echo "<br>";
?>
								<?=$ds['ITEM'][$j]['ITEMID']?>
<?
			}
?>
							</td>
							<td class='ct'><?=CODE_ORDER_STATUS($ds['STATUS'])?></td>
							<td class='ct'><?=$ds['GOODSRD_DT_STR']?></td>
							<td class='ct'><?=$ds['GOODSSC_DT_STR']?></td>
							<td class='rt'><?=number_format($ds['WEIGHT'])?>KG</td>
							<td class='rt'><?=number_format($ds['W_PRICE'])?>원</td>
							<td class='lf'><?=nl2br($ds['MEMO'])?></td>
						</tr>
<?
		}
	}
	else{
?>
						<tr>
							<td class='ct' style="height:50px;" colspan="8">주문출고내역이 없습니다</td>
						</tr>
<?
	}
?>
					</table>
					
					<div class='mt20'>
						<h2>3. 배송정보</h2>
					</div>
					<table border="0" width="100%" cellspacing="0" cellpadding="0" class='adtb_02 mt10'>
						<colgroup>
							<col width='20%' />
							<col width='80%' />
						</colgroup>
						<tr>
							<th scope="row">도착지영문주소</th>
							<td class='lf'><?=$rs[0]['DST_ADDR']?></td>
						</tr>
						<tr>
							<th scope="row">기타요청사항</th>
							<td class='lf'><?=nl2br($rs[0]['MEMO'])?></td>
						</tr>
					</table>
					
					<div class='mt20'>
						<h2>4. 주문항목</h2>
					</div>
					<table border="0" width="100%" cellspacing="0" cellpadding="0" class='adtb_02 mt10' id="tb_3">
						<colgroup>
							<col width='13%' />
							<col width='20%' />
						</colgroup>
						<tr>
							<th scope="row">항목번호</th>
							<th scope="row">신청링크<br>구매수량/구매금액</th>
							<th scope="row">주문상품</th>
							<th scope="row">옵션(필드/값)</th>
							<th scope="row">주문상태</th>
							<th scope="row">수량</th>
							<th scope="row">단가</th>
							<th scope="row">합계</th>
							<th scope="row">디파짓피</th>
							<th scope="row">단가(VND)</th>
							<th scope="row">합계(VND)</th>
							<th scope="row">디파짓피(VND)</th>
						</tr>
<?
	foreach($rs as $i=>$ds){
		$qty_sum += $ds['QTY'];
		$price_sum += $ds['PRICE'];
		$depositfee_sum += $ds['DEPOSITFEE'];
		$buy_price_sum += $ds['SUMPRICE'];
		
		$price_sum_vnd += $ds['PRICE_VND_ITEM'];
		$depositfee_sum_vnd += $ds['DEPOSITFEE_VND_ITEM'];
		$buy_price_sum_vnd += $ds['SUMPRICE_VND_ITEM'];
?>
						<tr>
							<td scope="row" class="ct"><?=$ds['ITEMID']?></td>
							<td scope="row" class="ct"><?=$ds['LINKURL']?><br><font color="red"><?=number_format($ds['P_QTY'])?> / <?=number_format($ds['P_PRICESUM'])?></font></td>
							<td scope="row" class="ct"><?=$ds['PRODUCTNAME']?></td>
							<td scope="row" class="ct"><?=$ds['OPTFIELD']?>/<?=$ds['OPTVALUE']?></td>
							<td scope="row" class="ct"><?=CODE_ORDER_STATUS($ds['STATUS'])?></td>
							<td scope="row" class="rt"><?=number_format($ds['QTY'])?></td>
							<td scope="row" class="rt"><?=number_format($ds['PRICE'])?></td>
							<td scope="row" class="rt"><?=number_format($ds['SUMPRICE'])?></td>
							<td scope="row" class="rt"><?=number_format($ds['DEPOSITFEE'])?></td>
							<td scope="row" class="rt"><?=number_format($ds['PRICE_VND_ITEM'])?></td>
							<td scope="row" class="rt"><?=number_format($ds['SUMPRICE_VND_ITEM'])?></td>
							<td scope="row" class="rt"><?=number_format($ds['DEPOSITFEE_VND_ITEM'])?></td>
						</tr>
<?
	}
?>
						<tr>
							<td scope="row" class="ct" colspan="5">합계</td>
							<td scope="row" class="rt"><?=number_format($qty_sum)?></td>
							<td scope="row" class="rt"><?=number_format($price_sum)?></td>
							<td scope="row" class="rt"><?=number_format($buy_price_sum)?></td>
							<td scope="row" class="rt"><?=number_format($depositfee_sum)?></td>
							<td scope="row" class="rt"><?=number_format($price_sum_vnd)?></td>
							<td scope="row" class="rt"><?=number_format($buy_price_sum_vnd)?></td>
							<td scope="row" class="rt"><?=number_format($depositfee_sum_vnd)?></td>
						</tr>
					</table>
					
					<div class='mt20'>
						<h2>5. 주문정보이력</h2>
					</div>
					<table border="0" width="100%" cellspacing="0" cellpadding="0" class='adtb_03 mt10'>
						<colgroup>
							<col width='20%' />
							<col width='8%' />
							<col width='17%' />
							<col width='12%' />
						</colgroup>
						<tr>
							<th scope="row">아이템번호</th>
							<th scope="row">상태</th>
							<th scope="row">일자</th>
							<th scope="row">담당자</th>
							<th scope="row">메모</th>
						</tr>
<?
	foreach($rsStatus as $i=>$ds){
		$status_str = CODE_ORDER_STATUS($ds['STATUS']);
		$bgcolor_str = '';
		
		if($ds['STATUS'] == 'A'){
			$bgcolor_str = '#FFDAB9';
		}
?>
						<tr>
							<td style="background-color:<?=$bgcolor_str?>;" scope="row" class="ct"><?=$ds['ITEMID']?></td>
							<td style="background-color:<?=$bgcolor_str?>;" scope="row" class="ct"><?=$status_str?></td>
							<td style="background-color:<?=$bgcolor_str?>;" scope="row" class="ct"><?=$ds['REG_DT']?></td>
							<td style="background-color:<?=$bgcolor_str?>;" scope="row" class="ct"><?=$ds['ADM_NM']?>(<?=$ds['ADM_ID']?>)</td>
							<td style="background-color:<?=$bgcolor_str?>;" scope="row" class="ct"><?=nl2br($ds['MEMO'])?></td>
						</tr>
<?
	}
?>
					</table>
					
<?
	if($rs[0]['CANCEL_YN'] == 'Y'){
?>
					<div class='mt20'>
						<h2>6. 취소정보</h2>
					</div>
					<table border="0" width="100%" cellspacing="0" cellpadding="0" class='adtb_02 mt10'>
						<colgroup>
							<col width='20%' />
							<col width='80%' />
						</colgroup>
						<tr>
							<th scope="row">취소일자</th>
							<td class='lf'><?=$rs[0]['CANCEL_DT']?></td>
						</tr>
						<tr>
							<th scope="row">취소메모</th>
							<td class='lf'><textarea name="cancel_memo" id="cancel_memo" style="width:100%" rows="5"><?=$rs[0]['CANCEL_MEMO']?></textarea></td>
						</tr>
					</table>
<?
	}
?>
				</div>
				
				<div class='center' style="clear:both;">
<?
	if($cancel_flag == 'Y'){
?>
					<input type='button' value='주문취소' class='btn_b01 w80' onclick="javascript:doOrderCancel('<?=$rs[0]['ORDER_SEQ']?>');"/>
<?
	}
?>
					<input type='button' value='닫기' class='btn_b02 w80' onclick="javascript:doCancel();"/>
				</div>

			</div>
			<!--// content -->

		</div>
		<!--// 전체 100% CONTENTS	 -->

	</div>
	<!--//contain-->

	</div>
	<!--//wrap-->
	
	
	<!-- popup -->
	<div id="search_product" class="dialog-popup" style="width:900px;">
		<!-- ajax: popSearch() -->
	</div>
	
	<div id="search_user" class="dialog-popup" style="width:900px;">
		<!-- ajax: popUser() -->
	</div>
	<!-- //popup -->
<?
	include $_SERVER["DOCUMENT_ROOT"]. "/_admin/f.php";
?>