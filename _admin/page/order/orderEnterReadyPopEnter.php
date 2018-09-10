<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSession($url = '/_admin/');

	$_MENU1 = '4';		// 상단메뉴
	$_MENU2 = '';		// 왼쪽메뉴

	$_NAVITITLE = "주문관리 > 입고확인";	

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
	
	// =====================================================
	// Start Tran
	// =====================================================
	
	
	include $_SERVER["DOCUMENT_ROOT"]. "/_admin/h.php";
?>

<script type="text/javascript" src="/js/jquery.form.js"></script>

<script>
	var pageaction = '<?=$pageaction?>';
	$('document').ready(function(){
		$('#barcodeid').focus();
	});
	
	// 취소
	function doCancel(){
		history.back();
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
	
	// 조회
	function doSearch(){
		var url = "orderEnterReadyPopEnterAjax.php";
		var param = $('#frm').serialize();
		
		$.ajax({
			type:"POST",
			dataType : 'json',
			url: url,
			data: param,
			async: false,
			success: function(obj){
				//console.log(obj);
				if(obj.status == 0){
					// 실패
					if(obj.msg != '') alert(obj.msg);
				}
				else{
					// 성공
					var ds = obj.data;
					var str_html = '';
					var bChk = true;
					
					$('input[name="chkitem"]').each(function(){
						var seq = $(this).val();
						var chk_seq = 0;
						
						chk_seq = ds.PURCHASE_SEQ;
						
						if(seq == chk_seq){
							bChk = false;
						}
					});
					
					if(bChk){
						str_html += '<tr>';
						str_html += '	<td scope="row" class="ct"><input type="checkbox" name="chkitem" id="chkitem_'+ ds.PURCHASE_SEQ +'" value="'+ ds.PURCHASE_SEQ +'" checked></td>';
						str_html += '	<td scope="row" class="ct">'+ ds.PRODUCTNAME +'</td>';
						str_html += '	<td scope="row" class="ct">'+ ds.P_LINKURL +'</td>';
						str_html += '	<td scope="row" class="ct">'+ ds.P_OPTFIELD +' / '+ ds.P_OPTVALUE +'</td>';
						str_html += '	<td scope="row" class="ct">'+ COMMIFY(ds.P_QTY) +'</td>';
						str_html += '	<td scope="row" class="ct">'+ COMMIFY(ds.P_PRICE) +'</td>';
						str_html += '	<td scope="row" class="ct">'+ COMMIFY(ds.P_DELIVERYFEE) +'</td>';
						str_html += '	<td scope="row" class="ct">'+ COMMIFY(ds.P_DISCOUNT) +'</td>';
						str_html += '	<td scope="row" class="ct">'+ COMMIFY(ds.P_PRICESUM) +'</td>';
						str_html += '	<td scope="row" class="ct">'+ ds.REG_DT_STR +'</td>';
						str_html += '</tr>';
						
						$('#tb_3').append(str_html);
					}
					else{
						alert('이미 추가된 항목입니다');
					}
				}
				
				$('#barcodeid').val('');
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
	
	// 입고확인
	function doProc(){
		if($('input[name="chkitem"]').is(':checked') == ''){alert("체크박스를 체크해 주세요"); return false;}

		if(confirm("선택한 항목의 상태를 변경하시겠습니까?")){
			var arrChk = new Array();
			$('input[name="chkitem"]:checked').each(function(){
				arrChk.push($(this).val());
			});
			
			$('#pageaction').val('enter');
			$('#sum_purchase_seq').val(arrChk);
			
			$.ajax({
				type:"POST",
				dataType : 'json',
				url: "orderEnterReadyPopProc.php",
				data: $('#frm').serialize(),
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
	
	// 엔터키 이벤트
	function evEnter(){
		if(event.keyCode == 13){
			doSearch();
		}
	}
</script>
	
	<div id='contain'>
		<!-- 전체 100% CONTENTS -->
		<div id='content_wrap'> <!-- 100%의 전체 컨텐츠 영역-->
		
			<!-- content -->
			<div class='content w100p'> <!-- 원하는 컨텐츠 넓이를 추가 w960 클래스는 width:960px란 의미 -->

				<h2><?=$_NAVITITLE?></h2>
				<div class='w100p'><!-- 컨텐츠 가로길이 줄임-->
					<form method="post" name="frm" id="frm" onsubmit="return false;">
					<input type="hidden" name="pageaction" id="pageaction" value="<?=$pageaction?>">
					<input type="hidden" name="item_seq" id="item_seq" value="<?=$item_seq?>">
					<input type="hidden" name="item_rowid" id="item_rowid" value="<?=$item_rowid?>">
					<input type="hidden" name="purchase_seq" id="purchase_seq" value="<?=$purchase_seq?>">
					<input type="hidden" name="sum_purchase_seq" id="sum_purchase_seq" value="">
					
					<div class="both"></div>
					
					<table border="0" width="100%" cellspacing="0" cellpadding="0" class='adtb_02 mt10'>
						<colgroup>
							<col width='20%' />
							<col width='80%' />
						</colgroup>
						<tr>
							<th scope="row">운송장번호/050연락처</th>
							<td class='lf'>
								<input name="barcodeid" id="barcodeid" type="text" class='w100p' value="" style="IME_MODE:inactive;" onkeypress="javascript:evEnter();" />
							</td>
						</tr>
					</table>
					</form>
					<div class='center mt10'>
						<input type='button' value='조회' class='btn_b01 w80' onclick="javascript:doSearch();"/>
					</div>
					
					<div class='mt20'>
						<h2>2. 코드목록</h2>
					</div> 	
					<table border="0" width="100%" cellspacing="0" cellpadding="0" class='adtb_02 mt10' id="tb_3">
						<colgroup>
							<col width='5%' />
							<col width='15%' />
							<col width='20%' />
						</colgroup>
						<tr>
							<th scope="row"><input name="chkall" id="chkall" type="checkbox" value="" checked onclick="javascript:doChkall();" /></th>
							<th scope="col">주문상품</th>
							<th scope="col">구매URL</th>
							<th scope="col">옵션필드(값)</th>
							<th scope="col">수량</th>
							<th scope="col">단가</th>
							<th scope="col">배송비</th>
							<th scope="col">할인금액</th>
							<th scope="col">합계</th>
							<th scope="col">등록일</th>
						</tr>
						<!--
						<tr>
							<td scope="row" class="ct"><?=$item['ITEMID']?></td>
							<td scope="row" class="ct"><?=$item['PRODUCTNAME']?></td>
							<td scope="row" class="rt"><?=number_format($item['QTY'])?></td>
							<td scope="row" class="rt"><?=number_format($item['PRICE'])?></td>
							<td scope="row" class="ct"><?=number_format($item['SUMPRICE'])?></td>
							<td scope="row" class="rt"><?=number_format($f_price)?></td>
							<td scope="row" class="rt"><?=number_format($l_price)?></td>
							<td scope="row" class="rt"><?=number_format($l_price)?></td>
						</tr>
						-->
					</table>
					
					<div class='center mt20'>
						<input type='button' value='뒤로' class='btn_b02 w80' onclick="javascript:doCancel();"/>
						<input type='button' value='완료' class='btn_b01 w80' onclick="javascript:doProc();"/>
					</div>
					
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