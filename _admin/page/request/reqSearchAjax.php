<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSessionAjax($url = '/_admin/');
	
	//=====================================================
	//== 도움말 - Get Parameters
	//=====================================================
	$pname = $cFnc->getReq('pname', '');
	$manufacturename = $cFnc->getReq('manufacturename', '');

	// =====================================================
	// Set Variables
	// =====================================================
	$cPdo = new cPdo($ARR_DB_INFO);

	// =====================================================
	// Start Tran
	// =====================================================
	// 제품검색리스트
	$arParam = array();
	$qryWhere = "WHERE B.USE_YN = 'Y'";
	
	if($pname != ''){
		$qryWhere .= " AND A.PNAME LIKE ?";
		array_push($arParam, '%'. $pname .'%');
	}
	if($manufacturename != ''){
		$qryWhere .= " AND B.`MANUFACTURENAME` LIKE ?";
		array_push($arParam, '%'. $manufacturename .'%');
	}
	
	$qry = "
		SELECT B.`MANUFACTURENAME`, A.`MANUFACTURE_SEQ`, A.`PRODUCT_SEQ`, A.`PRODUCTID`, A.`PNAME`, A.LINKURL, A.`P_STATUS`, A.CATEGORY_SEQ, C.`CATEGORY_NM`, A.`PINVOICENAME`
		FROM TB_PRODUCT A
		INNER JOIN TB_MANUFACTURE B ON B.`MANUFACTURE_SEQ` = A.`MANUFACTURE_SEQ`
		INNER JOIN TB_CATEGORY C ON C.`CATEGORY_SEQ` = A.`CATEGORY_SEQ`
		". $qryWhere ."
		ORDER BY A.`PNAME`
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('list', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$rs = $result['data'];
	
	if(is_object($cPdo))$cPdo->close();
?>

<script>
	// 전체체크
	function onChkAll(){
		if($('#chkall').is(':checked') == true){
			$('input[name="chkitem"]').prop('checked', true);
		}
		else{
			$('input[name="chkitem"]').prop('checked', false);
		}
	}
	
	// 적용
	function doApply(){
		if($('input[name="chkitem"]').is(':checked') == false){alert("적용할 항목을 체크해 주세요"); return false;}
		
		$('input[name="chkitem"]:checked').each(function(i){
			var linkurl = $(this).data('linkurl');
			var category_seq = $(this).data('category_seq');
			var pname = $(this).data('pname');
			var pinvoicename = $(this).data('pinvoicename');
			
			addItem(linkurl, category_seq, pname, pinvoicename);
		});
		
		CLOSE_LAYER_POPUP('#search_product');
	}
</script>

		<div id='pop'>
			<div class='title'>
				<h2>제품검색</h2>
				<a href="javascript:;" class='pop_close' onclick="javascript:CLOSE_LAYER_POPUP('#search_product');">닫기</a>
			</div>
			<table width="100%" border="0" cellspacing="0" cellpadding="0" class='tb_01_non mt10'>
				<colgroup>
					<col width='10%' />
					<col width='25%' />
					<col width='10%' />
					<col width='25%' />
					<col width='10%' />
				</colgroup>
				<tr>
					<th scope="row">제품명</th>
					<td class='lf2'><input type="text" name="pname" id="pname" value="<?=$pname?>"></td>
					<th>제조사명</th>
					<td class='lf2'><input type="text" name="manufacturename" id="manufacturename" value="<?=$manufacturename?>"></td>
					<td><input type='button' value='검색' class='btnp_03 w80' onclick="javascript:getProductList();" /></td>
				</tr>
			</table>
			<table width="100%" border="0" cellspacing="0" cellpadding="0" class='tb_01' id="pop_req_table">
				<colgroup>
					<col width='5%' />
					<col width='10%' />
					<col width='10%' />
					<col width='10%' />
					<col width='10%' />
					<col width='35%' />
					<col width='10%' />
					<col width='10%' />
				</colgroup>
				<tr>
					<th scope="col"><input type="checkbox" name="chkall" id="chkall" onclick="javascript:onChkAll();"></th>
					<th scope="col">제품코드</th>
					<th scope="col">제품명</th>
					<th scope="col">제조사명</th>
					<th scope="col">인보이스명</th>
					<th scope="col">링크URL</th>
					<th scope="col">카테고리</th>
					<th scope="col">상태</th>
				</tr>
<?
	foreach($rs as $i=>$ds){
?>
				<tr>
					<td>
						<input type="checkbox" name="chkitem" id="chkitem_<?=$ds['PRODUCT_SEQ']?>" value="<?=$ds['PRODUCT_SEQ']?>"
						data-linkurl="<?=$ds['LINKURL']?>" data-category_seq="<?=$ds['CATEGORY_SEQ']?>" data-pname="<?=$ds['PNAME']?>" data-pinvoicename="<?=$ds['PINVOICENAME']?>">
					</td>
					<td><?=$ds['PRODUCTID']?></td>
					<td><?=$ds['PNAME']?></td>
					<td><?=$ds['MANUFACTURENAME']?></td>
					<td><?=$ds['PINVOICENAME']?></td>
					<td><?=$ds['LINKURL']?></td>
					<td><?=$ds['CATEGORY_NM']?></td>
					<td><?=CODE_PRODUCT_STATUS($ds['P_STATUS'])?></td>
				</tr>
<?
	}
?>
			</table>
			<div class='center mt20'>
				<input type='button' value='적용' class='btnp_01 w80' onclick="javascript:doApply();" />
			</div>
		</div>