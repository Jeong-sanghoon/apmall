<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSession($url = '/_admin/');

	$_MENU1 = '4';		// 상단메뉴
	$_MENU2 = '';		// 왼쪽메뉴

	$_NAVITITLE = "주문관리 > 구매처리";	

	//=====================================================
	//== 도움말 - Get Parameters
	//=====================================================
	$item_seq = $cFnc->getReq('item_seq', '');
	$item_rowid = $cFnc->getReq('item_rowid', '');
	$purchase_seq = $cFnc->getReq('purchase_seq', '');

	// =====================================================
	// Set Variables
	// =====================================================
	$cPdo = new cPdo($ARR_DB_INFO);
	
	// =====================================================
	// Set Parameters (Get Types)
	// =====================================================
	$gStr = "";
	$gStr = $cFnc->GetStr( $gStr, "item_seq", $item_seq );
	$gStr = $cFnc->GetStr( $gStr, "item_rowid", $item_rowid );
	$gStr = $cFnc->GetStr( $gStr, "purchase_seq", $purchase_seq );
	
	$pageaction = 'insert';

	// =====================================================
	// Start Tran
	// =====================================================
	// 주문아이템
	if($purchase_seq != ''){
		$arParam = array();
		array_push($arParam, $purchase_seq);
		$qry = "
			SELECT B.ITEM_SEQ, B.ITEM_ROWID, B.ITEMID, B.PRODUCTNAME
			, A.P_LINKURL, A.P_QTY, A.P_PRICE, A.P_DELIVERYFEE, A.P_PRICESUM, A.P_OPTFIELD, A.`P_OPTVALUE`, A.`P_MEMO`, A.P_STATUS, A.REG_DT, A.MOD_DT, A.P_DISCOUNT, A.DELIVER_NO, A.PAYMENT_TP, A.APPROVAL_NO
			, C.`050_NO`, C.`SEQ` AS 050_SEQ
			FROM TB_ORDER_PURCHASE A
			INNER JOIN TB_ORDER_ITEM B ON B.ITEM_SEQ = A.ITEM_SEQ
			LEFT OUTER JOIN TB_ORDER_PURCHASE_050 C ON C.`PURCHASE_SEQ` = A.`PURCHASE_SEQ`
			WHERE A.PURCHASE_SEQ = ?
		";
		$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
		$result = $cPdo->execQuery('data', $qry, $arParam);
		if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
		$ds = $result['data'];
		
		$pageaction = 'update';
	}
	else{
		$arParam = array();
		array_push($arParam, $item_seq);
		$qry = "
			SELECT A.ORDER_SEQ, A.ORDERID, A.NAME
			, B.LINKURL, B.ITEM_SEQ, B.ITEM_ROWID, B.ITEMID, B.`PRODUCTNAME`, B.REG_DT, B.QTY, B.PRICE, B.SUMPRICE, B.DEPOSIT_DT, B.`STATUS`
			, B.READY_DT, B.WEARRD_DT, B.WEARSC_DT, B.GOODSRD_DT, B.GOODSSC_DT, B.GOODSCF_DT, B.OPTFIELD, B.OPTVALUE
			FROM TB_ORDER A
			INNER JOIN TB_ORDER_ITEM B ON B.ORDER_SEQ = A.`ORDER_SEQ`
			WHERE B.ITEM_SEQ = ?
		";
		$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
		$result = $cPdo->execQuery('data', $qry, $arParam);
		if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
		$dsTmp = $result['data'];
		
		$ds['P_LINKURL'] = $dsTmp['LINKURL'];
		$ds['P_QTY'] = $dsTmp['QTY'];
		$ds['P_PRICE'] = $dsTmp['PRICE'];
		$ds['P_PRICESUM'] = $dsTmp['SUMPRICE'];
		$ds['P_OPTFIELD'] = $dsTmp['OPTFIELD'];
		$ds['P_OPTVALUE'] = $dsTmp['OPTVALUE'];
		
		// 050번호조회
		$arParam = array();
		$qry = "
			SELECT SEQ, 050_NO, PURCHASE_SEQ
			FROM TB_ORDER_PURCHASE_050 
			WHERE PURCHASE_SEQ IS NULL 
			ORDER BY SEQ 
			LIMIT 1
		";
		$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
		$result = $cPdo->execQuery('data', $qry, $arParam);
		if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
		$ds050 = $result['data'];
		
		$ds['050_NO'] = $ds050['050_NO'];
		$ds['050_SEQ'] = $ds050['SEQ'];
	}
	
	
	include $_SERVER["DOCUMENT_ROOT"]. "/_admin/h.php";
?>

<script type="text/javascript" src="/js/jquery.form.js"></script>

<script>
	$('document').ready(function(){
		
	});
	
	// 합계계산
	function cal_p_pricesum(){
		var p_qty = Number($('#p_qty').val());
		var p_price = Number($('#p_price').val());
		var p_deliveryfee = Number($('#p_deliveryfee').val());
		var p_discount = Number($('#p_discount').val());
		var sum = (p_qty * p_price) + p_deliveryfee - p_discount;
		
		$('#p_pricesum').val(sum);
	}
	
	// 취소
	function doCancel(){
		history.back();
	}
	
	// 구매처리
	function doProc(){
		if($('#p_linkurl').val() == ''){alert("구매URL을 입력해 주세요"); $('#p_linkurl').focus(); return false;}
		if($('#p_qty').val() == ''){alert("수량을 입력해 주세요"); $('#p_qty').focus(); return false;}
		if($('#p_price').val() == ''){alert("구매단가를 입력해 주세요"); $('#p_price').focus(); return false;}
		if($('#p_deliveryfee').val() == ''){alert("구매배송비를 입력해 주세요"); $('#p_deliveryfee').focus(); return false;}
		
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
					return false;
				}
				else{
					// 성공
					alert(obj.msg);
					window.opener.location.reload();
					location.href = "orderGoodsReadyPopList.php?<?=$gStr?>";
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
</script>
	
	<div id='contain'>
		<!-- 전체 100% CONTENTS -->
		<div id='content_wrap'> <!-- 100%의 전체 컨텐츠 영역-->
		
			<!-- content -->
			<div class='content w100p'> <!-- 원하는 컨텐츠 넓이를 추가 w960 클래스는 width:960px란 의미 -->

				<h2><?=$_NAVITITLE?></h2>
				<div class='w100p'><!-- 컨텐츠 가로길이 줄임-->
					<form method="post" name="frm" id="frm" action="">
					<input type="hidden" name="pageaction" id="pageaction" value="<?=$pageaction?>">
					<input type="hidden" name="item_seq" id="item_seq" value="<?=$item_seq?>">
					<input type="hidden" name="item_rowid" id="item_rowid" value="<?=$item_rowid?>">
					<input type="hidden" name="purchase_seq" id="purchase_seq" value="<?=$purchase_seq?>">
					
					<div class="both"></div>
					
					<table border="0" width="100%" cellspacing="0" cellpadding="0" class='adtb_02 mt10'>
						<colgroup>
							<col width='20%' />
							<col width='80%' />
						</colgroup>
						<tr>
							<th scope="row">*주문URL</th>
							<td class='lf'>
								<input type="text" name="p_linkurl" id="p_linkurl" class="w95p" value="<?=$ds['P_LINKURL']?>">
							</td>
						</tr>
						<tr>
							<th scope="row">*수량</th>
							<td class='lf'>
								<input type="text" name="p_qty" id="p_qty" class="w95p" value="<?=$ds['P_QTY']?>" onkeyup="javascript:cal_p_pricesum();">
							</td>
						</tr>
						<tr>
							<th scope="row">*단가</th>
							<td class='lf'>
								<input type="text" name="p_price" id="p_price" class="w95p" value="<?=$ds['P_PRICE']?>" onkeyup="javascript:cal_p_pricesum();">
							</td>
						</tr>
						<tr>
							<th scope="row">*배송비</th>
							<td class='lf'>
								<input type="text" name="p_deliveryfee" id="p_deliveryfee" class="w95p" value="<?=$ds['P_DELIVERYFEE']?>" onkeyup="javascript:cal_p_pricesum();">
							</td>
						</tr>
						<tr>
							<th scope="row">할인금액</th>
							<td class='lf'>
								<input type="text" name="p_discount" id="p_discount" class="w95p" value="<?=$ds['P_DISCOUNT']?>" onkeyup="javascript:cal_p_pricesum();">
							</td>
						</tr>
						<tr>
							<th scope="row">합계</th>
							<td class='lf'>
								<input type="text" name="p_pricesum" id="p_pricesum" class="w95p" value="<?=$ds['P_PRICESUM']?>">
							</td>
						</tr>
						<tr>
							<th scope="row">옵션필드</th>
							<td class='lf'>
								<input type="text" name="p_optfield" id="p_optfield" class="w95p" value="<?=$ds['P_OPTFIELD']?>">
							</td>
						</tr>
						<tr>
							<th scope="row">옵션값</th>
							<td class='lf'>
								<input type="text" name="p_optvalue" id="p_optvalue" class="w95p" value="<?=$ds['P_OPTVALUE']?>">
							</td>
						</tr>
						<tr>
							<th scope="row">메모</th>
							<td class='lf'>
								<textarea name="p_memo" id="p_memo" class="w95p" rows="5"><?=nl2br($ds['P_MEMO'])?></textarea>
							</td>
						</tr>
						<tr>
							<th scope="row">050번호</th>
							<td class='lf'>
								<?=$cFnc->MaskingTelNo($ds['050_NO'])?>
								<input type="hidden" name="050_seq" id="050_seq" class="w95p" value="<?=$ds['050_SEQ']?>">
							</td>
						</tr>
						<tr>
							<th scope="row">결제종류</th>
							<td class='lf'>
								<select name="payment_tp" id="payment_tp">
									<option value="CREDIT" <?=$cFnc->CodeString($ds['PAYMENT_TP'], 'CREDIT', 'selected', '')?>>신용카드</option>
									<option value="CHECK" <?=$cFnc->CodeString($ds['PAYMENT_TP'], 'CHECK', 'selected', '')?>>체크카드</option>
								</select>
							</td>
						</tr>
<?
	if($pageaction == 'update'){
?>
						<tr>
							<th scope="row">결제승인번호</th>
							<td class='lf'>
								<input type="text" name="approval_no" id="approval_no" class="w95p" value="<?=$ds['APPROVAL_NO']?>">
							</td>
						</tr>
						<tr>
							<th scope="row">운송장번호</th>
							<td class='lf'>
								<input type="text" name="deliver_no" id="deliver_no" class="w95p" value="<?=$ds['DELIVER_NO']?>">
							</td>
						</tr>
<?
	}
?>
					</table>
					
					<div class='center mt20'>
						<input type='button' value='뒤로' class='btn_b02 w80' onclick="javascript:doCancel();"/>
						<input type='button' value='등록' class='btn_b01 w80' onclick="javascript:doProc();"/>
					</div>
					</form>
				</div>
			</div>
			<!--// content -->

		</div>
		<!--// 전체 100% CONTENTS	 -->

	</div>
	<!--//contain-->
	<!--//wrap-->
<?
	include $_SERVER["DOCUMENT_ROOT"]. "/_admin/f.php";
?>