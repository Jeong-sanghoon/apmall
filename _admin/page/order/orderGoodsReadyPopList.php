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

	// =====================================================
	// Set Variables
	// =====================================================
	$cPdo = new cPdo($ARR_DB_INFO);
	
	// 페이징 세팅
	$nPageCnt = $cFnc->getReq('nPageCnt', 10);		// 블럭당 페이지 수
	$nListCnt = $cFnc->getReq('nListCnt', 10);		// 페이지당 리스트 수
	$nowPage = $cFnc->getReq('nowPage', 1);			// 현재 페이지
	$cPdo->setPagingInfo($nListCnt, $nPageCnt);
	
	// =====================================================
	// Set Parameters (Get Types)
	// =====================================================
	$gStr = "";
	$gStr = $cFnc->GetStr( $gStr, "item_seq", $item_seq );
	$gStr = $cFnc->GetStr( $gStr, "item_rowid", $item_rowid );

	// =====================================================
	// Start Tran
	// =====================================================
	// 주문아이템
	$arParam = array();
	array_push($arParam, $item_seq);
	$qry = "
		SELECT B.ITEM_SEQ, B.ITEM_ROWID, B.ITEMID, B.PRODUCTNAME
		, A.PURCHASE_SEQ, A.P_LINKURL, A.P_QTY, A.P_PRICE, A.P_DELIVERYFEE, A.P_PRICESUM, A.P_OPTFIELD, A.`P_OPTVALUE`, A.`P_MEMO`, A.P_STATUS, A.REG_DT, A.MOD_DT, A.P_DISCOUNT, A.DELIVER_NO, A.PAYMENT_TP, A.APPROVAL_NO
		, C.`050_NO`, C.`SEQ` AS 050_SEQ
		FROM TB_ORDER_PURCHASE A
		INNER JOIN TB_ORDER_ITEM B ON B.ITEM_SEQ = A.ITEM_SEQ
		LEFT OUTER JOIN TB_ORDER_PURCHASE_050 C ON C.`PURCHASE_SEQ` = A.`PURCHASE_SEQ`
		WHERE A.DEL_YN = 'N'
		AND A.ITEM_SEQ = ?
		ORDER BY A.PURCHASE_SEQ DESC
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
	
	
	include $_SERVER["DOCUMENT_ROOT"]. "/_admin/h.php";
?>

<script type="text/javascript" src="/js/jquery.form.js"></script>

<script>
	$('document').ready(function(){
		
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
			$('input[name="chkitem"]:checked').each(function(i){
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
	
	// 구매완료
	function doEnter(){
		if($('input[name="chkitem"]').is(':checked') == ''){alert("체크박스를 체크해 주세요"); return false;}
		
		if(confirm("구매완료 하시겠습니까?")){
			var sum_purchase_seq = '';
			$('input[name="chkitem"]:checked').each(function(i){
				if(i > 0) sum_purchase_seq = sum_purchase_seq +',';
				sum_purchase_seq = sum_purchase_seq + $(this).val();
			});
			
			$('#pageaction').val('buy');
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
</script>
	
	<form method="post" name="frm" id="frm">
	<input type="hidden" name="pageaction" id="pageaction" value="">
	<input type="hidden" name="sum_purchase_seq" id="sum_purchase_seq" value="">
	<div id='contain'>
		<!-- 전체 100% CONTENTS -->
		<div id='content_wrap'> <!-- 100%의 전체 컨텐츠 영역-->
		
			<!-- content -->
			<div class='content w100p'> <!-- 원하는 컨텐츠 넓이를 추가 w960 클래스는 width:960px란 의미 -->

				<h2><?=$_NAVITITLE?></h2>
				
				<div class='mt10'>
					<p class='fr'>
						<input type='button' value='구매완료' class='btn_02 w80' onclick="javascript:doEnter();"/>
						<input type='button' value='등록' class='btn_02 w60' onclick="javascript:goInsert();"/>
						<input type='button' value='삭제' class='btn_02 w60' onclick="javascript:doDelete();"/>
					</p>
				</div>
				
				<div class="both"></div>
				
				<table width="100%" border="0" cellspacing="0" cellpadding="0"	class='adtb_01 mt10'>
					<colgroup>
						<col width='5%' />
						<col width='5%' />
						<col width='15%' />
						<col width='20%' />
					</colgroup>
					<tr>
						<th scope="col"><input name="chkall" id="chkall" type="checkbox" value="" onclick="javascript:doChkall();" /></th>
						<th scope="col">NO</th>
						<th scope="col">주문상품</th>
						<th scope="col">구매URL</th>
						<th scope="col">옵션필드(값)</th>
						<th scope="col">수량</th>
						<th scope="col">합계</th>
						<th scope="col">상태</th>
						<th scope="col">등록일</th>
						<th scope="col">운송장번호</th>
						<th scope="col">050번호</th>
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
						<td class='bg_01'><a href="javascript:goUpdate('<?=$ds['PURCHASE_SEQ']?>');"><?=$ds['PRODUCTNAME']?></a></td>
						<td class='bg_01'><a href="javascript:goUpdate('<?=$ds['PURCHASE_SEQ']?>');"><?=$ds['P_LINKURL']?></a></td>
						<td>
							<?=$ds['P_OPTFIELD']?>
							<br>
							(<?=$ds['P_OPTVALUE']?>)
						</td>
						<td><?=number_format($ds['P_QTY'])?></td>
						<td><?=number_format($ds['P_PRICESUM'])?></td>
						<td><?=$p_status?></td>
						<td><?=$ds['REG_DT_STR']?></td>
						<td><?=$ds['DELIVER_NO']?></td>
						<td><?=$cFnc->MaskingTelNo($ds['050_NO'])?></td>
					</tr>
<?
		}
	}
	else{
?>
					<tr>
						<td colspan="11" height="200">검색 결과가 없습니다</td>
					</tr>
<?
	}
?>
				</table>

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