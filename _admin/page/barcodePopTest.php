<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	include $_SERVER["DOCUMENT_ROOT"]. "/_admin/h.php";
?>
<style>
	.test_tb_01{border: 1px solid #ccd3db; table-layout: fixed; width:50%; float:left;}
	.test_mt_10{margin-top:10px;}
	.test_tb_01 th {background:#d5e0eb;border-right:1px solid #ccd3db;border-bottom:1px solid #ccd3db;text-align:center; padding:8px 0; font-size:12px}
	.test_tb_01 td{background:#fff; text-align:center; padding:2px 0;font-size:12px;word-break:break-all;}
	.test_tb_01 td.lf2 {text-align:left; padding:6px 10px; border-bottom:1px solid #ccd3db;}
</style>

<script>

	// 입고명세서인쇄
	function doPrintBarcode(){
		AREA_PRINT("#barcode_tb");
	}

</script>
<div id="barcode_pop" class="dialog-popup" style="width:800px; display:block;">
	<div id='pop'>
		<div class='title'>
			<h2>입고명세서</h2>
			<a href="javascript:;" class='pop_close' onclick="javascript:CLOSE_LAYER_POPUP('#barcode_pop');">닫기</a>
		</div>
		<!--<p class="today_date"><span>입고일시</span> </p>-->
		<div class="sel_barcode">
			<input type="radio" name="barcode_type" id="sel_code39" value="code39"> Code39
			<input type="radio" name="barcode_type" id="sel_code128" value="code128" checked> Code128
		</div>
		<div class='center mt20'>
			<input type='button' value='바코드생성' class='btnp_01 w80' onclick="javascript:doMakeBarcode();" />
			<input type='button' value='바코드인쇄' class='btnp_01 w80' onclick="javascript:doPrintBarcode();" />
		</div>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" class='' id="barcode_tb">
			<tr>
				<td>
					<table border="0" cellspacing="0" cellpadding="0" class="test_tb_01 test_mt_10">
						<colgroup>
							<col width='20%' />
							<col width='80%' />
						</colgroup>
						<tr>
							<th scope="row">주문번호</th>
							<td class="lf2">OD23498127459812754</td>
						</tr>
						<tr>
							<th scope="row">아이템번호</th>
							<td class="lf2">OI23498127459812754</td>
						</tr>
						<tr>
							<td colspan="2">
								<img src="/_upload/barcode/201806/OI201806051448500900_code128.jpg" />
							</td>
						</tr>
					</table>
					<table border="0" cellspacing="0" cellpadding="0" class="test_tb_01 test_mt_10">
						<colgroup>
							<col width='20%' />
							<col width='80%' />
						</colgroup>
						<tr>
							<th scope="row">주문번호</th>
							<td class="lf2">OD23498127459812754</td>
						</tr>
						<tr>
							<th scope="row">아이템번호</th>
							<td class="lf2">OI23498127459812754</td>
						</tr>
						<tr>
							<td colspan="2">
								<img src="/_upload/barcode/201806/OI201806051448500900_code128.jpg" />
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td>
					<table border="0" cellspacing="0" cellpadding="0" class="test_tb_01 test_mt_10">
						<colgroup>
							<col width='20%' />
							<col width='80%' />
						</colgroup>
						<tr>
							<th scope="row">주문번호</th>
							<td class="lf2">OD23498127459812754</td>
						</tr>
						<tr>
							<th scope="row">아이템번호</th>
							<td class="lf2">OI23498127459812754</td>
						</tr>
						<tr>
							<td colspan="2">
								<img src="/_upload/barcode/201806/OI201806051448500900_code128.jpg" />
							</td>
						</tr>
					</table>
					<table border="0" cellspacing="0" cellpadding="0" class="test_tb_01 test_mt_10">
						<colgroup>
							<col width='20%' />
							<col width='80%' />
						</colgroup>
						<tr>
							<th scope="row">주문번호</th>
							<td class="lf2">OD23498127459812754</td>
						</tr>
						<tr>
							<th scope="row">아이템번호</th>
							<td class="lf2">OI23498127459812754</td>
						</tr>
						<tr>
							<td colspan="2">
								<img src="/_upload/barcode/201806/OI201806051448500900_code128.jpg" />
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</div>
</div>

<?
	include $_SERVER["DOCUMENT_ROOT"]. "/_admin/f.php";
?>