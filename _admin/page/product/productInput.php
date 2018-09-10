<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSession($url = '/_admin/');

	$_MENU1 = '5';		// 상단메뉴
	$_MENU2 = '2';		// 왼쪽메뉴

	$_NAVITITLE = "제품관리 > 유통사관리";		

	//=====================================================
	//== 도움말 - Get Parameters
	//=====================================================
	$product_seq 	= $cFnc->getReq('product_seq', '');

	$cal_1 			= $cFnc->getReq('cal_1', date('Y-m-d', strtotime('-3 month')));
	$cal_2 			= $cFnc->getReq('cal_2', date('Y-m-d'));
	$pname  		= $cFnc->getReq('pname', '');
	$order_cont 	= $cFnc->getReq('order_cont', 'PRODUCT_SEQ');
	$order_asc 		= $cFnc->getReq('order_asc', 'DESC');
	$search_list1 	= $cFnc->getReq('search_list1', '');
	
	
	$nPageCnt 		= $cFnc->getReq('nPageCnt', 10);		// 블럭당 페이지 수
	$nListCnt 		= $cFnc->getReq('nListCnt', 10);		// 페이지당 리스트 수
	$nowPage 		= $cFnc->getReq('nowPage', 1);			// 현재 페이지

	// =====================================================
	// Set Variables
	// =====================================================
	$cPdo = new cPdo($ARR_DB_INFO);

	$pageaction = "INSERT";

	// =====================================================
	// Set Parameters (Get Types)
	// =====================================================
	$gStr = "";
	$gStr = $cFnc->GetStr( $gStr, "cal_1", $cal_1 );
	$gStr = $cFnc->GetStr( $gStr, "cal_2", $cal_2 );
	$gStr = $cFnc->GetStr( $gStr, "pname", $pname );
	$gStr = $cFnc->GetStr( $gStr, "use_yn", $use_yn );
	$gStr = $cFnc->GetStr( $gStr, "order_cont", $order_cont );
	$gStr = $cFnc->GetStr( $gStr, "order_asc", $order_asc );
	$gStr = $cFnc->GetStr( $gStr, "search_list1", $search_list1 );

	$gStr2 = "";
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_1", $cal_1 );
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_2", $cal_2 );
	$gStr2 = $cFnc->GetStr( $gStr2, "pname", $pname );
	$gStr2 = $cFnc->GetStr( $gStr2, "use_yn", $use_yn );
	$gStr2 = $cFnc->GetStr( $gStr2, "order_cont", $order_cont );
	$gStr2 = $cFnc->GetStr( $gStr2, "order_asc", $order_asc );
	$gStr2 = $cFnc->GetStr( $gStr2, "search_list1", $search_list1 );
	$gStr2 = $cFnc->GetStr( $gStr2, "nListCnt", $nListCnt );
	$gStr2 = $cFnc->GetStr( $gStr2, "nowPage", $nowPage );
	$gStr2 = $cFnc->GetStr( $gStr2, "nPageCnt", $nPageCnt );
	

	// =====================================================
	// Start Tran
	// =====================================================
	if($product_seq != ''){
		$pageaction = "UPDATE";

		$arParam = array();
		array_push($arParam, $product_seq);
		$qry = "SELECT * FROM TB_PRODUCT WHERE PRODUCT_SEQ = ?";
		$rslt = $cPdo->execQuery('data', $qry, $arParam);
		$ds = $rslt['data'];
		
		$qry = "SELECT * FROM TB_PRODUCT_STOCK WHERE PRODUCT_SEQ = ?";
		$rslt = $cPdo->execQuery('list', $qry, $arParam);
		$dsStock = $rslt['data'];
	}

	// Manufacture 리스트		
	$qry = "SELECT MANUFACTURE_SEQ, MANUFACTURENAME FROM TB_MANUFACTURE WHERE USE_YN = 'Y'";
	$rslt = $cPdo->execQuery('list', $qry, $arParam);
	$rsManufacture = $rslt['data'];

	// Category 리스트		
	$qry = "SELECT CATEGORY_SEQ, CATEGORY_NM FROM TB_CATEGORY WHERE DEPTH = 1 AND USE_YN = 'Y'";
	$rslt = $cPdo->execQuery('list', $qry, $arParam);
	$rsCategory = $rslt['data'];	


	// Storage 리스트		
	$qry = "SELECT STORAGE_SEQ, STORAGE_NM FROM TB_STORAGE WHERE USE_YN = 'Y'";
	$rslt = $cPdo->execQuery('list', $qry, $arParam);
	$rsStorage = $rslt['data'];

	include $_SERVER["DOCUMENT_ROOT"]. "/_admin/h.php";
?>

<script type="text/javascript" src="/js/jquery.form.js"></script>

<script>
	var pageaction = "<?=$pageaction?>";
	var htmlstorage = "";
	$('document').ready(function(){
		if(pageaction == 'INSERT') {
//			$('#USE_Y').attr('checked', true);
			$('#P_STATUS_O').attr('checked', true);
			$('#STOCK_YN_F').attr('checked', true);
		}
		StockEvent();
	});
	

	// 등록
	function doProc(){
		if($('#PNAME').val() == ''){alert("제품명을 입력해 주세요"); $('#PNAME').focus(); return false;}
		if($('#USERNAME').val() == ''){alert("인보이스명을 입력해 주세요"); $('#PINVOICENAME').focus(); return false;}
		if($('#CATEGORY_SEQ').val() == ''){alert("카테고리명을 입력해 주세요"); $('#CATEGORY_SEQ').focus(); return false;}

		var bStock = true;
		var arrStorageSeq = new Array();
		var arrQty = new Array();
		$("input[name=STOCK_YN]:checked").each(function() {			
		  	if ( $(this).val() == 'Y' ){
				$("#divStock select").each(function(){					
					if($(this).val() == ""){						
						bStock = false;
						return false;					
					}	
					arrStorageSeq.push($(this).val());
				})
				
				$("#divStock input[type=text]").each(function(){
					if($(this).val() == ""){						
						bStock = false;
						return false;		
					}
					arrQty.push($(this).val());	
				})
			} 
		});

		if(!bStock){alert("미입력된 값이 존재합니다."); return false;}

		$('#arrstorage_seq').val(arrStorageSeq);
		$('#arrqty').val(arrQty);

		var url = "productProc.php";
		var param = $('#frm').serialize();
		
		$('#frm').ajaxForm({
			type:"POST",
			dataType : 'json',
			url:url,
			data: param,
			// enctype: "multipart/form-data",
			async: false,
			success: function(obj){
				var strUrl = obj.url;
				if(pageaction == 'UPDATE') strUrl = strUrl +"?product_seq=<?=$product_seq?>&<?=$gStr2?>";
				
				if(obj.status == 0){
					alert(obj.msg);
					if(obj.url != "") location.replace(strUrl);
				}
				else{
					alert(obj.msg);
					location.replace(strUrl);
				}
			},
			error: function(request, status, error){
				alert('Find Error -> '+ status);
				return false;				
			}
		});
		
		$('#frm').submit();
	}
	
	// 취소
	function doCancel(){
		history.back();
	}
	

	function StockEvent(){
		$('input[name="STOCK_YN"]:checked').each(function() {		  				
		  	if ( $(this).val() == 'Y' ){
				$('#divStock').show();
			} else {
				$('#divStock').hide();
			}
		});

	}

	var index;

	// 재고추가 버튼
	function AddStock(){
		var addRow = AddRowSelect();

		var strHtml = " 수량 : <input name='QTY_1' id='QTY_1' type='text' class='w200' value=''/>"
		strHtml += " <input type='button' value='추가' class='btn_b02 w80' onclick='javascript:AddStock();'/>";
		

		$("#divStock .adtb_02").find('tbody')
			.append($('<tr>')
        		.append($('<td class=lf>')
        			.text("창고설정 : ")
        			.append(addRow)
        			.append(strHtml)
        		)
        	);	    
	}


	function AddRowSelect(){
		var index = $("#divStock select").length + 1;

        //Clone the DropDownList
        var ddl = $("#STORAGE_SEQ_1").clone();

        //Set the ID and Name
        ddl.attr("id", "STORAGE_SEQ_" + index);
        ddl.attr("name", "STORAGE_SEQ_" + index);

        //[OPTIONAL] Copy the selected value
        var selectedValue = $("#STORAGE_SEQ_1 option:selected").val();
        ddl.find("option[value = '" + selectedValue + "']").attr("selected", "selected");

        return ddl;
	}


	function checkSel(){
		$("#divStock select").each(function(){
			alert($(this).val());
		})
		$("#divStock input[type=text]").each(function(){
			alert($(this).val());
		})
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
				<div class='w900'><!-- 컨텐츠 가로길이 줄임-->
					<form method="post" name="frm" id="frm" action="manufactureProc.php">
					<input type="hidden" name="pageaction" id="pageaction" value="<?=$pageaction?>">
					<input type="hidden" name="product_seq" id="product_seq" value="<?=$product_seq?>">
					<input type="hidden" name="arrstorage_seq" id="arrstorage_seq" value="" />
					<input type="hidden" name="arrqty" id="arrqty" value="" />
					<table border="0" width="100%" cellspacing="0" cellpadding="0" class='adtb_02 mt20 '>
						<colgroup>
							<col width='20%' />
							<col width='80%' />
						</colgroup>
<?
	if($pageaction == 'UPDATE'){
?>						
						<tr>
							<th scope="row">제품코드</th>
							<td class='lf'><?=$ds['PRODUCTID']?></td>
						</tr>				
<?
	}

?>
						<tr>
							<th scope="row">제품명</th>
							<td class='lf'><input name="PNAME" id="PNAME" type="text" class='w200' value="<?=$ds['PNAME']?>"/></td>
						</tr>
						<tr>
							<th scope="row">인보이스명</th>
							<td class='lf'><input name="PINVOICENAME" id="PINVOICENAME" type="text" class='w200' value="<?=$ds['PINVOICENAME']?>"/></td>
						</tr>
						<tr>
							<th scope="row">유통사</th>
							<td class='lf'>
								<select name="MANUFACTURE_SEQ" id="MANUFACTURE_SEQ">
									<option value="">선택하세요</option>
<?
	foreach($rsManufacture as $j=>$dsrow){
?>
							<option value="<?=$dsrow['MANUFACTURE_SEQ']?>" <?=$cFnc->CodeString($dsrow['MANUFACTURE_SEQ'], $ds['MANUFACTURE_SEQ'], 'selected', '')?> ><?=$dsrow['MANUFACTURENAME']?></option>
<?
	}
?>									
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row">카테고리</th>
							<td class='lf'>
								<select name="CATEGORY_SEQ" id="CATEGORY_SEQ">
									<option value="">선택하세요</option>
<?
	foreach($rsCategory as $j=>$dsrow){
?>
							<option value="<?=$dsrow['CATEGORY_SEQ']?>" <?=$cFnc->CodeString($dsrow['CATEGORY_SEQ'], $ds['CATEGORY_SEQ'], 'selected', '')?> ><?=$dsrow['CATEGORY_NM']?></option>
<?
	}
?>									
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row">상태</th>
							<td class='lf con'>
								<input name="P_STATUS" id="P_STATUS_O" type="radio" value="O" <?=$ds['P_STATUS'] == 'O' ? 'checked' : ''?> /> 정상
								<span class='pl10'><input name="P_STATUS" id="USE_N_R" type="radio" value="R" <?=$ds['P_STATUS'] == 'R' ? 'checked' : ''?> /> 품절 </span>
								<span class='pl10'><input name="P_STATUS" id="USE_N_S" type="radio" value="S" <?=$ds['P_STATUS'] == 'S' ? 'checked' : ''?> /> 판매중지 </span>
							</td>
						</tr>									
						<tr>
							<th scope="row">재고</th>
							<td class='lf'>
								<input name="STOCK_YN" id="STOCK_YN_F" type="radio" value="F" <?=$ds['STOCK_YN'] == 'F' ? 'checked' : ''?> onclick="javascript:StockEvent();"/> 재고해당없음
								<span class='pl10'><input name="STOCK_YN" id="STOCK_YN_Y" type="radio" value="Y" <?=$ds['STOCK_YN'] == 'Y' ? 'checked' : ''?> onclick="javascript:StockEvent();"/> 재고있음 </span>
								<span class='pl10'><input name="STOCK_YN" id="STOCK_YN_N" type="radio" value="N" <?=$ds['STOCK_YN'] == 'N' ? 'checked' : ''?> onclick="javascript:StockEvent();"/> 재고없음 </span>
								<div class="content skip" id="divStock">
									<table border=0 width="100%" cellpadding="0" cellpadding="0" class="adtb_02">
										<colgroup>
											<col width='100%' />
										</colgroup>			


<?
		if( $pageaction == "UPDATE" && !empty($dsStock) ){
			$kk = 1;
			foreach($dsStock as $k=>$dsStock){
?>
										<tr>
											<td class='lf'>창고설정 : 
												<select name="STORAGE_SEQ_<?=$kk?>" id="STORAGE_SEQ_<?=$kk?>">
<?
				foreach($rsStorage as $j=>$dsrow){
?>
							<option value="<?=$dsrow['STORAGE_SEQ']?>" <?=$cFnc->CodeString($dsrow['STORAGE_SEQ'], $dsStock['STORAGE_SEQ'], 'checked', '')?> ><?=$dsrow['STORAGE_NM']?></option>
<?
				}
?>																							

												</select>
												수량 : <input name="QTY_<?=$kk?>" id="QTY_<?=$kk?>" type="text" class='w200' value="<?=$dsStock['QTY']?>"/>
												<input type='button' value='추가' class='btn_b02 w80' onclick="javascript:AddStock();"/>
											</td>
										</tr>
<?			
			}
		} else {
?>

										<tr>
											<td class='lf'>창고설정 : 
												<select name="STORAGE_SEQ_1" id="STORAGE_SEQ_1">
<?
				foreach($rsStorage as $j=>$dsrow){
?>
							<option value="<?=$dsrow['STORAGE_SEQ']?>" <?=$cFnc->CodeString($dsrow['STORAGE_SEQ'], $ds['STORAGE_SEQ'], 'checked', '')?> ><?=$dsrow['STORAGE_NM']?></option>
<?
				}
?>																							

												</select>
												수량 : <input name="QTY_1" id="QTY_1" type="text" class='w200' value="<?=$ds['QTY']?>"/>
												<input type='button' value='추가' class='btn_b02 w80' onclick="javascript:AddStock();"/>
											</td>
										</tr>
<?
		}
?>										
									</table>
								</div>
							</td>
						</tr>					
						<tr>
							<th scope="row">LinkURL</th>
							<td class='lf'><input name="LINKURL" id="LINKURL" type="text" class='w600' value="<?=$ds['LINKURL']?>"/></td>
						</tr>												
<?
	if($pageaction == 'UPDATE'){
?>
						<tr>
							<th scope="row">등록일</th>
							<td class='lf'><?=$ds['REG_DT']?></td>
						</tr>
						<tr>
							<th scope="row">수정일</th>
							<td class='lf'><?=$ds['MOD_DT']?></td>
						</tr>
<?
	}
?>
					</table>
					<div class='center mt20'>
						<input type='button' value='등록' class='btn_b01 w80' onclick="javascript:doProc();"/>
						<input type='button' value='취소' class='btn_b02 w80' onclick="javascript:doCancel();"/>						
					</div>
					</form>
				</div>

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