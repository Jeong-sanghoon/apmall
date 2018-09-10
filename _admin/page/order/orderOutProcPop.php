<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSession($url = '/_admin/');

	$_MENU1 = '4';		// 상단메뉴
	$_MENU2 = '';		// 왼쪽메뉴

	$_NAVITITLE = "주문관리 > 출고처리";	

	//=====================================================
	//== 도움말 - Get Parameters
	//=====================================================
	$arr_item_seq = $cFnc->getReq('arr_item_seq', '');

	// =====================================================
	// Set Variables
	// =====================================================
	$cPdo = new cPdo($ARR_DB_INFO);
	
	$deliveryid = "DM". date('YmdHis') . strtoupper($cFnc->GenerateRanStr(4, 'number'));		// 출고번호생성
	
	// =====================================================
	// Set Parameters (Get Types)
	// =====================================================
	$gStr = "";
	$gStr = $cFnc->GetStr( $gStr, "cal_1", $cal_1 );

	// =====================================================
	// Start Tran
	// =====================================================
	// 주문마스터조회
	$arParam = array();
	$qry = "
		SELECT B.ORDERID, B.NAME, B.DST_ADDR
		FROM TB_ORDER_ITEM A
		INNER JOIN TB_ORDER B ON B.ORDER_SEQ = A.`ORDER_SEQ`
		WHERE A.`ITEM_SEQ` IN (". $arr_item_seq .")
		GROUP BY B.ORDERID, B.NAME, B.DST_ADDR
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('list', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$rsOrder = $result['data'];
	//echo json_encode($rsOrder);exit;
	
	// 주문아이템조회
	$arParam = array();
	$qry = "
		SELECT B.ORDERID, A.ITEM_SEQ, A.ITEM_ROWID, A.ITEMID, A.`PRODUCTNAME`, A.`QTY`, A.`PRICE`, A.`SUMPRICE`, A.`DEPOSITFEE`, A.PRICE_VND, A.SUMPRICE_VND, A.DEPOSITFEE_VND
		, SUM(C.P_QTY) AS P_QTY, SUM(C.P_PRICE) AS P_PRICE, SUM(C.P_DELIVERYFEE) AS P_DELIVERYFEE, SUM(C.P_PRICESUM) AS P_PRICESUM
		FROM TB_ORDER_ITEM A
		INNER JOIN TB_ORDER B ON B.ORDER_SEQ = A.`ORDER_SEQ`
		INNER JOIN TB_ORDER_PURCHASE C ON C.ITEM_SEQ = A.ITEM_SEQ
		WHERE A.`ITEM_SEQ` IN (". $arr_item_seq .")
		GROUP BY B.ORDERID, A.ITEM_SEQ, A.ITEM_ROWID, A.ITEMID, A.`PRODUCTNAME`, A.`QTY`, A.`PRICE`, A.`SUMPRICE`, A.`DEPOSITFEE`, A.PRICE_VND, A.SUMPRICE_VND, A.DEPOSITFEE_VND
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('list', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$rsItem = $result['data'];
	
	
	$sumprice_tot = 0;
	$depositfee_tot = 0;
	$rs = $rsOrder;
	foreach($rs as $i=>$ds){
		$idx = 0;
		
		foreach($rsItem as $j=>$dsItem){
			if($ds['ORDERID'] == $dsItem['ORDERID']){
				$rs[$i]['ITEM'][$idx] = $dsItem;
				$sumprice_tot += $dsItem['SUMPRICE'];
				$depositfee_tot += $dsItem['DEPOSITFEE'];
				$idx++;
			}
		}
	}
	//echo '<br>price == '. $sumprice_tot;
	//echo '<br>depositfee == '. $depositfee_tot;
	//echo '<br>'. json_encode($rs);exit;
	
	
	// 운송비(배송사)조회
	$arParam = array();
	$qry = "SELECT * FROM TB_DELIVER_COST WHERE USE_YN = 'Y' AND COST_TYPE = 'O' AND OBJ_TP = 'C' ORDER BY COST_SEQ DESC LIMIT 1";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('data', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$dsCost = $result['data'];
	
	// 운송비(고객)조회
	$arParam = array();
	$qry = "SELECT * FROM TB_DELIVER_COST WHERE USE_YN = 'Y' AND COST_TYPE = 'O' AND OBJ_TP = 'P' ORDER BY COST_SEQ DESC LIMIT 1";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('data', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$dsCost2 = $result['data'];
	
	// 고객운송비단위조회
	$arParam = array();
	$qry = "SELECT * FROM TB_DELIVER_UNIT WHERE USE_YN = 'Y' ORDER BY L_WEIGHT";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('list', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$rsDlvUnit = $result['data'];
	
	// 시스템정보조회 : 베트남요율정보 가져오기
	$arParam = array();
	array_push( $arParam, $S_SYSTEM_CD);
	$qry = "SELECT * FROM TB_SYSTEM WHERE SYSTEM_CD = ?";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('data', $qry, $arParam);
	if(!$result['status']) throw new Exception($result['msg'], 1001);
	$dsSystem = $result['data'];
	$vnd_rate = $dsSystem['RATE'];
	
	
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
	
	// 출고처리
	function doProc(){
		if($('#weight').val() == ''){alert("무게를 입력해 주세요"); $('#weight').focus(); return false;}
		if($('#w_price').val() == ''){alert("운송비를 입력해 주세요"); $('#w_price').focus(); return false;}
		
		if(confirm("출고처리 하시겠습니까?")){
			var sum_item_seq = '';
			$('input[name="item_seq[]"]').each(function(i){
				if(i > 0) sum_item_seq = sum_item_seq +',';
				sum_item_seq = sum_item_seq + $(this).val();
			});
			
			$('#pageaction').val('out_req');
			$('#status').val('F');
			$('#sum_item_seq').val(sum_item_seq);
			
			var url = "orderProc.php";
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
						window.close();
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
	
	// 운송비계산 : 무게입력시
	function calDelPrice(){
		var weight = $('#weight').val();
		var cost = '<?=$dsCost['COST']?>';
		var cost_up = '<?=$dsCost['COST_UP']?>';
		
		var cost2 = '<?=$dsCost2['COST']?>';
		var arr_unit = <?=json_encode($rsDlvUnit)?>;
		
		//console.log(cost2);
		// 배송사 운송비 계산
		if(weight.indexOf('.') > -1){
			var arr_dec = weight.split('.');
			var integer = arr_dec[0];
			var dec = arr_dec[1];
			
			if(dec >= cost_up){
				integer = Number(integer) + 1;
			}
			
			weight = integer;
		}
		
		var w_price = cost * weight;
		$('#w_price').val(w_price);
		
		// 고객 운송비 계산
		var w_price_p = 0;
		var vnd_rate = '<?=$vnd_rate?>';
		var sumprice_tot = Number('<?=$sumprice_tot?>');
		var depositfee_tot = Number('<?=$depositfee_tot?>');
		var init_weight = Number($('#weight').val());
		weight = Number($('#weight').val());
		for(var i = 0; i < arr_unit.length; i++){
			if(weight > 0){
				if(init_weight <= 1){
					w_price_p = Number(cost2);
				}
				else{
					var l_weight = arr_unit[i].L_WEIGHT;
					var h_weight = arr_unit[i].H_WEIGHT;
					var price = arr_unit[i].PRICE;
					var up_unit = arr_unit[i].UP_UNIT;
					
					var h_weight2 = 0;
					if(i > 0) h_weight2 = arr_unit[i-1].H_WEIGHT;
					
					if(h_weight != 0){
						if(init_weight > h_weight){
							// 구간보다 클때
							w_price_p += Math.ceil((h_weight - h_weight2) / up_unit) * price;
							weight = init_weight - h_weight;
						}
						else{
							// 구간에 속할때
							w_price_p += Math.ceil(weight / up_unit) * price;
							weight = 0;
						}
					}
					else{
						if(init_weight > l_weight){
							// 가장 큰 영역일때
							w_price_p += Math.ceil(weight / up_unit) * price;
							weight = 0;
						}
					}
				}
			}
		}
		
		var cod = sumprice_tot - depositfee_tot + w_price_p;
		var cod_vnd = cod * vnd_rate;
		
		cod_vnd = Math.floor((cod_vnd + 999) / 1000) * 1000;
		
		$('#w_price_p').val(w_price_p);
		$('#w_price_p_vnd').val(w_price_p * Number(vnd_rate));
		$('#cod_span').html(COMMIFY(cod_vnd));
		$('#cod').val(cod);
		$('#cod_vnd').val(cod_vnd);
	}
	
	// 운송비계산 : 고객운송비(VND) 입력시
	function calDelPriceDirect(){
		var vnd_rate = '<?=$vnd_rate?>';
		var sumprice_tot = Number('<?=$sumprice_tot?>');
		var depositfee_tot = Number('<?=$depositfee_tot?>');
		var w_price_p = Number($('#w_price_p').val());
		
		var cod = sumprice_tot - depositfee_tot + w_price_p;
		var cod_vnd = cod * vnd_rate;
		var w_price_p_vnd = w_price_p * vnd_rate
		
		cod_vnd = Math.floor((cod_vnd + 999) / 1000) * 1000;
		
		$('#cod_span').html(COMMIFY(cod_vnd));
		$('#w_price_p_vnd').val(w_price_p_vnd);
		$('#cod').val(cod);
		$('#cod_vnd').val(cod_vnd);
	}
</script>
	
	<form method="post" name="frm" id="frm">
	<input type="hidden" name="deliveryid" id="deliveryid" value="<?=$deliveryid?>">
	<input type="hidden" name="pageaction" id="pageaction" value="">
	<input type="hidden" name="status" id="status" value="">
	<input type="hidden" name="sum_item_seq" id="sum_item_seq" value="">
	<div id='contain'>
		<!-- 전체 100% CONTENTS -->
		<div id='content_wrap'> <!-- 100%의 전체 컨텐츠 영역-->
		
			<!-- content -->
			<div class='content w100p'> <!-- 원하는 컨텐츠 넓이를 추가 w960 클래스는 width:960px란 의미 -->

				<h2><?=$_NAVITITLE?></h2>
				<div class='w100p'><!-- 컨텐츠 가로길이 줄임-->
					<div class='mt20'>
						<h2>1. 출고정보</h2>
					</div>
					<table border="0" width="100%" cellspacing="0" cellpadding="0" class='adtb_02 mt10'>
						<colgroup>
							<col width='20%' />
							<col width='80%' />
						</colgroup>
						<tr>
							<th scope="row">출고번호</th>
							<td class='lf'><?=$deliveryid?></td>
						</tr>
						<tr>
							<th scope="row">무게</th>
							<td class='lf'><input type="text" name="weight" id="weight" value="" onkeyup="javascript:calDelPrice();">(KG)</td>
						</tr>
						<tr>
							<th scope="row">배송사운송비</th>
							<td class='lf'><input type="text" name="w_price" id="w_price" value="">(원)</td>
						</tr>
						<tr>
							<th scope="row">고객운송비</th>
							<td class='lf'><input type="text" name="w_price_p" id="w_price_p" value="" onkeyup="javascript:calDelPriceDirect();">(원)</td>
						</tr>
						<tr>
							<th scope="row">고객운송비(VND)</th>
							<td class='lf'><input type="text" name="w_price_p_vnd" id="w_price_p_vnd" value="" readonly>(VND)</td>
						</tr>
						<tr>
							<th scope="row">예상COD(VND)</th>
							<td class='lf'><span id="cod_span"></span>(VND)</td>
							<input type="hidden" name="cod" id="cod" value="">
							<input type="hidden" name="cod_vnd" id="cod_vnd" value="">
						</tr>
						<tr>
							<th scope="row">메모</th>
							<td class='lf'><textarea rows="5" style="width:100%;" name="memo" id="memo"></textarea></td>
						</tr>
					</table>
					
<?
	foreach($rs as $i=>$ds){
		$num = $i + 1;
?>
					<div class='mt20'>
						<h2><?=$num?>. <?=$ds['ORDERID']?> / <?=$ds['NAME']?> / <?=$ds['DST_ADDR']?></h2>
					</div>
					<table border="0" width="100%" cellspacing="0" cellpadding="0" class='adtb_02 mt10' id="tb_3">
						<colgroup>
							<col width='20%' />
							<col width='20%' />
						</colgroup>
						<tr>
							<th scope="row">아이템번호</th>
							<th scope="row">주문상품</th>
							<th scope="row">수량</th>
							<th scope="row">단가</th>
							<th scope="row">합계</th>
							<th scope="row">디파짓피</th>
							<th scope="row">단가(VND)</th>
							<th scope="row">합계(VND)</th>
							<th scope="row">디파짓피(VND)</th>
						</tr>
<?
		foreach($ds['ITEM'] as $j=>$item){
?>
						<tr>
							<td scope="row" class="ct"><?=$item['ITEMID']?></td>
							<td scope="row" class="ct"><?=$item['PRODUCTNAME']?></td>
							<td scope="row" class="rt"><?=number_format($item['QTY'])?></td>
							<td scope="row" class="rt"><?=number_format($item['PRICE'])?></td>
							<td scope="row" class="ct"><?=number_format($item['SUMPRICE'])?></td>
							<td scope="row" class="rt"><?=number_format($item['DEPOSITFEE'])?></td>
							<td scope="row" class="rt"><?=number_format($item['PRICE_VND'])?></td>
							<td scope="row" class="ct"><?=number_format($item['SUMPRICE_VND'])?></td>
							<td scope="row" class="rt"><?=number_format($item['DEPOSITFEE_VND'])?></td>
						</tr>
						<input type="hidden" name="item_seq[]" id="item_seq_<?=$j?>" value="<?=$item['ITEM_SEQ']?>">
<?
		}
?>
					</table>
<?
	}
?>
					<div class='center mt20'>
						<input type='button' value='닫기' class='btn_b02 w80' onclick="javascript:doCancel();"/>
						<input type='button' value='출고처리' class='btn_b01 w80' onclick="javascript:doProc();"/>
					</div>
				</div>

			</div>
			<!--// content -->

		</div>
		<!--// 전체 100% CONTENTS	 -->

	</div>
	<!--//contain-->
	</form>
	<!--//wrap-->
<?
	include $_SERVER["DOCUMENT_ROOT"]. "/_admin/f.php";
?>