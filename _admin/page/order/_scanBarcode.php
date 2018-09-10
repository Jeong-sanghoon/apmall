<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSession($url = '/_admin/');

	$_MENU1 = '4';		// 상단메뉴
	$_MENU2 = '';		// 왼쪽메뉴

	//=====================================================
	//== 도움말 - Get Parameters
	//=====================================================
	$pageaction = $cFnc->getReq('type', '');
	//$barcodeid = $cFnc->getReq('barcodeid', '');
	//$cFnc->echoArr($_REQUEST); exit;
	// =====================================================
	// Set Variables
	// =====================================================
	$cPdo = new cPdo($ARR_DB_INFO);
	$PAGENAME = '입고처리';
	$KEYWORD = '입고';
	if($pageaction == 'out'){
		$PAGENAME = '출고처리';
		$KEYWORD = '출고';
	}
	
	$_NAVITITLE = "주문관리 > ". $PAGENAME;
	
	// =====================================================
	// Set Parameters (Get Types)
	// =====================================================
	$gStr = "";
	$gStr = $cFnc->GetStr( $gStr, "type", $type );

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
		window.close();
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
		var url = "_scanBarcodeAjax.php";
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
						var item_seq = $(this).val();
						var chk_seq = 0;
						
						if(pageaction == 'enter') chk_seq = ds.ITEM_SEQ;
						else if(pageaction == 'out') chk_seq = ds.DELIVERY_SEQ;
						
						if(item_seq == chk_seq){
							bChk = false;
						}
					});
					
					if(bChk){
						if(pageaction == 'enter'){
							str_html += '<tr>';
							str_html += '	<td scope="row" class="ct"><input type="checkbox" name="chkitem" id="chkitem_'+ ds.ITEM_SEQ +'" value="'+ ds.ITEM_SEQ +'" checked></td>';
							str_html += '	<td scope="row" class="ct">'+ ds.ID +'</td>';
							str_html += '	<td scope="row" class="ct">'+ ds.PRODUCTNAME +'</td>';
							str_html += '	<td scope="row" class="ct">'+ ds.OPTFIELD +' / '+ ds.OPTVALUE +'</td>';
							str_html += '	<td scope="row" class="ct">'+ ds.WEARRD_DT +'</td>';
							str_html += '	<td scope="row" class="ct">'+ ds.NAME +'</td>';
							str_html += '	<td scope="row" class="ct">'+ COMMIFY(ds.QTY) +'</td>';
							str_html += '	<td scope="row" class="rt">'+ COMMIFY(ds.PRICE) +'</td>';
							str_html += '	<td scope="row" class="rt">'+ COMMIFY(ds.SUMPRICE) +'</td>';
							str_html += '	<td scope="row" class="ct">'+ COMMIFY(ds.STATUS_STR) +'</td>';
							str_html += '</tr>';
						}
						else if(pageaction == 'out'){
							str_html += '<tr>';
							str_html += '	<td scope="row" class="ct"><input type="checkbox" name="chkitem" id="chkitem_'+ ds.DELIVERY_SEQ +'" value="'+ ds.DELIVERY_SEQ +'" checked></td>';
							str_html += '	<td scope="row" class="ct">'+ ds.ID +'</td>';
							str_html += '	<td scope="row" class="ct">'+ COMMIFY(ds.W_PRICE) +'</td>';
							str_html += '	<td scope="row" class="ct">'+ ds.WEIGHT +'</td>';
							str_html += '	<td scope="row" class="ct">'+ ds.GOODSRD_DT_STR +'</td>';
							str_html += '	<td scope="row" class="ct">'+ COMMIFY(ds.QTY) +'</td>';
							str_html += '	<td scope="row" class="rt">'+ COMMIFY(ds.PRICE) +'</td>';
							str_html += '	<td scope="row" class="rt">'+ COMMIFY(ds.SUMPRICE) +'</td>';
							str_html += '	<td scope="row" class="ct">'+ COMMIFY(ds.STATUS_STR) +'</td>';
							str_html += '</tr>';
						}
						
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
	
	// 출고처리
	function doProc(){
		if($('input[name="chkitem"]').is(':checked') == ''){alert("체크박스를 체크해 주세요"); return false;}

		if(confirm("선택한 항목의 상태를 변경하시겠습니까?")){
			var arrChk = new Array();
			$('input[name="chkitem"]:checked').each(function(){
				arrChk.push($(this).val());
			});
			
			if(pageaction == 'enter'){
				$('#pageaction').val('enter_fin');
				$('#arr_item_seq').val(arrChk);
				$('#status').val('E');
			}
			else{
				$('#pageaction').val('out_fin');
				$('#arr_delivery_seq').val(arrChk);
				$('#status').val('G');
			}

			$.ajax({
				type:"POST",
				dataType : 'json',
				url: "orderProc.php",
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
				<div class='w100p'><!-- 컨텐츠 가로길이 줄임-->
					<div class='mt20'>
						<h2>1. <?=$cFnc->CodeString($pageaction, 'enter', '아이템ID', '출고ID')?></h2>
					</div>
					<form method="post" name="frm" id="frm" onsubmit="return false;">
					<input name="type" id="type" type="hidden" value="<?=$pageaction?>">
					<input type="hidden" name="pageaction" id="pageaction" value="">
					<input type="hidden" name="arr_item_seq" id="arr_item_seq" value="">
					<input type="hidden" name="arr_delivery_seq" id="arr_delivery_seq" value="">
					<input type="hidden" name="status" id="status" value="">
					<table border="0" width="100%" cellspacing="0" cellpadding="0" class='adtb_02 mt10'>
						<colgroup>
							<col width='20%' />
							<col width='80%' />
						</colgroup>
						<tr>
							<th scope="row"><?=$cFnc->CodeString($pageaction, 'enter', '아이템ID', '출고ID')?></th>
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
							<col width='20%' />
						</colgroup>
						<tr>
							<th scope="row"><input name="chkall" id="chkall" type="checkbox" value="" checked onclick="javascript:doChkall();" /></th>
							<th scope="row"><?=$cFnc->CodeString($pageaction, 'enter', '아이템ID', '출고ID')?></th>
<?
	if($pageaction == 'enter'){
?>
							<th scope="row">주문상품</th>
							<th scope="row">옵션</th>
							<th scope="row">입고요청일</th>
							<th scope="row">주문자</th>
							<th scope="row">수량</th>
							<th scope="row">단가</th>
							<th scope="row">합계</th>
							<th scope="row">상태</th>
<?
	}
	else{
?>
							<th scope="row">운송비</th>
							<th scope="row">무게</th>
							<th scope="row">출고요청일</th>
							<th scope="row">수량</th>
							<th scope="row">단가</th>
							<th scope="row">합계</th>
							<th scope="row">상태</th>
<?
	}
?>
							
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
						<input type='button' value='닫기' class='btn_b02 w80' onclick="javascript:doCancel();"/>
						<input type='button' value='완료' class='btn_b01 w80' onclick="javascript:doProc();"/>
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