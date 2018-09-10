<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSession($url = '/_admin/');

	$_MENU1 = '4';		// 상단메뉴
	$_MENU2 = '';		// 왼쪽메뉴

	$_NAVITITLE = "주문관리 > 출고상세보기";

	//=====================================================
	//== 도움말 - Get Parameters
	//=====================================================
	$delivery_seq = $cFnc->getReq('delivery_seq', '');

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
	// 출고마스터 조회
	$arParam = array();
	array_push($arParam, $delivery_seq);
	$qry = "SELECT * FROM TB_DELIVERY WHERE `DELIVERY_SEQ` = ?";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('data', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$dsDeli = $result['data'];
	
	
	// 출고아이템 조회
	$arParam = array();
	array_push($arParam, $delivery_seq);
	$qry = "SELECT ITEM_SEQ FROM `TB_DELIVERY_ITEM` WHERE `DELIVERY_SEQ` = ?";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('list', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$rsDeliItem = $result['data'];
	
	$arr_item_seq = '';
	foreach($rsDeliItem as $i=>$ds){
		if($i > 0) $arr_item_seq .= ',';
		$arr_item_seq .= $ds['ITEM_SEQ'];
	}
	
	
	// 주문마스터조회
	$arParam = array();
	$qry = "
		SELECT A.ORDER_SEQ, B.ORDERID, B.NAME, B.DST_ADDR
		FROM TB_ORDER_ITEM A
		INNER JOIN TB_ORDER B ON B.ORDER_SEQ = A.`ORDER_SEQ`
		WHERE A.`ITEM_SEQ` IN (". $arr_item_seq .")
		GROUP BY B.ORDERID, B.NAME, B.DST_ADDR
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('list', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$rsOrder = $result['data'];
	
	
	// 주문아이템조회
	$arParam = array();
	$qry = "
		SELECT B.ORDERID, A.ITEM_SEQ, A.ITEM_ROWID, A.ITEMID, A.`PRODUCTNAME`, A.`QTY`, A.`PRICE`, A.`SUMPRICE`, A.`DEPOSITFEE`, A.PRICE_VND, A.SUMPRICE_VND, A.DEPOSITFEE_VND
		FROM TB_ORDER_ITEM A
		INNER JOIN TB_ORDER B ON B.ORDER_SEQ = A.`ORDER_SEQ`
		WHERE A.`ITEM_SEQ` IN (". $arr_item_seq .")
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('list', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$rsItem = $result['data'];
	
	
	$rs = $rsOrder;
	foreach($rs as $i=>$ds){
		$idx = 0;
		
		foreach($rsItem as $j=>$dsItem){
			if($ds['ORDERID'] == $dsItem['ORDERID']){
				$rs[$i]['ITEM'][$idx] = $dsItem;
				$idx++;
			}
		}
	}
	
	
	// 주문히스토리
	$arParam = array();
	array_push($arParam, $delivery_seq);
	$qry = "
		SELECT A.*
		, B.ORDER_SEQ, B.ITEMID, B.GOODSCF_YN
		, C.ADM_ID, C.ADM_NM
		FROM (
			SELECT MAX(STATUS_SEQ) AS STATUS_SEQ, STATUS, MAX(REG_DT) AS REG_DT, MEMO, ITEM_SEQ, OLD_STATUS, ITEM_ROWID, ADM_SEQ
			FROM TB_ORDER_STATUS
			GROUP BY STATUS, MEMO, ITEM_SEQ, OLD_STATUS, ITEM_ROWID, ADM_SEQ
		) A
		INNER JOIN TB_ORDER_ITEM B ON B.ITEM_SEQ = A.ITEM_SEQ
		INNER JOIN TB_ADM C ON C.ADM_SEQ = A.ADM_SEQ
		INNER JOIN TB_DELIVERY_ITEM D ON D.ITEM_SEQ = A.ITEM_SEQ
		INNER JOIN TB_DELIVERY E ON E.`DELIVERY_SEQ` = D.`DELIVERY_SEQ`
		WHERE E.`DELIVERY_SEQ` = ?
		AND A.STATUS IN ('F', 'G')
		ORDER BY A.ITEM_SEQ ASC, A.STATUS_SEQ ASC
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('list', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$rsStatus = $result['data'];
	//echo json_encode($rsStatus);exit;
	
	
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
	
</script>
	
	<div id='wrap'>

	<!-- Left menu -->
	<?include $_SERVER["DOCUMENT_ROOT"]. "/_admin/include/lnb.php";?>
	<!--// Left menu -->
	
	<form method="post" name="frm" id="frm">
	<input type="hidden" name="deliveryid" id="deliveryid" value="<?=$deliveryid?>">
	<input type="hidden" name="pageaction" id="pageaction" value="">
	<input type="hidden" name="status" id="status" value="">
	<input type="hidden" name="sum_item_seq" id="sum_item_seq" value="">
	<div id='contain'>

		<!-- menu GNB -->
		<?include $_SERVER["DOCUMENT_ROOT"]. "/_admin/include/gnb.php";?>
		<!--// menu GNB -->
		
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
							<td class='lf'><?=$dsDeli['DELIVERYID']?></td>
						</tr>
						<tr>
							<th scope="row">무게</th>
							<td class='lf'><?=number_format($dsDeli['WEIGHT'], 2, ',', ',')?>(KG)</td>
						</tr>
						<tr>
							<th scope="row">배송사운송비</th>
							<td class='lf'><?=number_format($dsDeli['W_PRICE'])?>(원)</td>
						</tr>
						<tr>
							<th scope="row">고객운송비</th>
							<td class='lf'><?=number_format($dsDeli['W_PRICE_P'])?>(원)</td>
						</tr>
						<tr>
							<th scope="row">고객운송비(VND)</th>
							<td class='lf'><?=number_format($dsDeli['W_PRICE_P_VND'])?>(VND)</td>
						</tr>
						<tr>
							<th scope="row">예상COD(VND)</th>
							<td class='lf'><?=number_format($dsDeli['COD_VND'])?>(VND)</td>
						</tr>
						<tr>
							<th scope="row">메모</th>
							<td class='lf'><?=nl2br($dsDeli['MEMO'])?></td>
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
			$f_price = ($item['SUMPRICE'] * 40) / 100;
			$l_price = ($item['SUMPRICE'] * 60) / 100;
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
					
					<div class='mt20'>
						<h2>구매정보이력</h2>
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
		foreach($rsStatus as $j=>$status){
			if($ds['ORDER_SEQ'] == $status['ORDER_SEQ']){
				$status_str = CODE_ORDER_STATUS($status['STATUS']);
				$bgcolor_str = '';
				
				if($status['STATUS'] == 'F'){
					$bgcolor_str = '#FFDAB9';
				}
?>
						<tr>
							<td style="background-color:<?=$bgcolor_str?>;" scope="row" class="ct"><?=$status['ITEMID']?></td>
							<td style="background-color:<?=$bgcolor_str?>;" scope="row" class="ct"><?=$status_str?></td>
							<td style="background-color:<?=$bgcolor_str?>;" scope="row" class="ct"><?=$status['REG_DT']?></td>
							<td style="background-color:<?=$bgcolor_str?>;" scope="row" class="ct"><?=$status['ADM_NM']?>(<?=$status['ADM_ID']?>)</td>
							<td style="background-color:<?=$bgcolor_str?>;" scope="row" class="ct"><?=nl2br($status['MEMO'])?></td>
						</tr>
<?
			}
		}
?>
					</table>
<?
	}
?>
					<div class='center' style="clear:both;">
						<input type='button' value='닫기' class='btn_b02 w80' onclick="javascript:doCancel();"/>
					</div>
				</div>

			</div>
			<!--// content -->

		</div>
		<!--// 전체 100% CONTENTS	 -->

	</div>
	<!--//contain-->
	</form>
	</div>
	<!--//wrap-->
<?
	include $_SERVER["DOCUMENT_ROOT"]. "/_admin/f.php";
?>