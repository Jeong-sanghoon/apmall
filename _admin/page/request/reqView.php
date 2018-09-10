<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSession($url = '/_admin/');

	$_MENU1 = '3';		// 상단메뉴
	$_MENU2 = '1';		// 왼쪽메뉴

	$_NAVITITLE = "신청관리 > 신청현황";	

	//=====================================================
	//== 도움말 - Get Parameters
	//=====================================================
	$usermstid = $cFnc->getReq('usermstid', '');

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
	// 고객이 작성한 신청서 조회
	if($usermstid != ''){
		$arParam = array();
		array_push($arParam, $usermstid);
		$qry = "
			SELECT A.`USERMSTID`, A.`USERORDID`, A.REG_DT, A.`NAME`, A.`EMAIL`, A.`TEL`, A.`PRICESUM`, A.DEPOSITFEE AS DEPOSITFEE_SUM, A.`DST_ADDR`, A.`MEMO`, A.`QTY` AS QTY_SUM, A.USER_ID, A.USER_SEQ, A.STATUS
			, B.`USERITEMID`, B.`LINKURL`, B.ITEM_NM, B.`OPTFIELD`, B.`OPTVALUE`, B.`QTY`, B.`PRICE`, B.DEPOSITFEE
			FROM TB_ORDER_REQ A
			INNER JOIN TB_ORDER_REQITEM B ON B.USERMSTID = A.`USERMSTID`
			WHERE A.`USERMSTID` = ?
		";
		$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
		$result = $cPdo->execQuery('list', $qry, $arParam);
		if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
		$rs = $result['data'];
		
		$ord_dt = $rs[0]['REG_DT'];			// 주문생성일 [신청서 작성일]
	}
	
	
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
	
	// 주문서생성
	function goInput(){
		window.open("reqInput.php?usermstid=<?=$usermstid?>");
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
					<form method="post" name="frm" id="frm" action="">
					<input type="hidden" name="pageaction" id="pageaction" value="<?=$pageaction?>">
					<input type="hidden" name="usermstid" id="usermstid" value="<?=$usermstid?>">
					<div class='mt20'>
						<h2>1. 기본정보</h2>
					</div>
					<table border="0" width="100%" cellspacing="0" cellpadding="0" class='adtb_02 mt10'>
						<colgroup>
							<col width='10%' />
							<col width='40%' />
							<col width='10%' />
							<col width='40%' />
						</colgroup>
						<tr>
							<th scope="row">주문번호</th>
							<td class='lf'><?=$rs[0]['USERORDID']?></td>
							<th scope="row">이름</th>
							<td class='lf'><?=$rs[0]['NAME']?></td>
						</tr>
						<tr>
							<th scope="row">연락처</th>
							<td class='lf'><?=$rs[0]['TEL']?></td>
							<th scope="row">이메일</th>
							<td class='lf'><?=$rs[0]['EMAIL']?></td>
						</tr>
						<tr>
							<th scope="row">주문신청일</th>
							<td class='lf'><?=$ord_dt?></td>
							<th scope="row">주문금액</th>
							<td class='lf'><?=number_format($rs[0]['PRICESUM'])?>원</td>
						</tr>
						<tr>
							<th scope="row">디파짓피</th>
							<td class='lf'><?=number_format($rs[0]['DEPOSITFEE_SUM'])?>원</td>
							<th scope="row">처리상태</th>
							<td class='lf'><?=CODE_REQ_STATUS($rs[0]['STATUS'])?></td>
						</tr>
					</table>
					
					<div class='mt20'>
						<h2>2. 배송정보</h2>
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
						<h2>3. 주문항목</h2>
					</div>
					<table border="0" width="100%" cellspacing="0" cellpadding="0" class='adtb_02 mt10' id="tb_3">
						<colgroup>
							<col width='30%' />
						</colgroup>
						<tr>
							<th scope="row">신청링크</th>
							<th scope="row">옵션필드</th>
							<th scope="row">옵션값</th>
							<th scope="row">수량</th>
							<th scope="row">단가</th>
							<th scope="row">구매금액</th>
							<th scope="row">디파짓피</th>
							<th scope="row">합계</th>
						</tr>
<?
	foreach($rs as $i=>$ds){
		$price_tot = $ds['QTY'] * $ds['PRICE'] + $ds['DEPOSITFEE'];
		$buy_price = $ds['QTY'] * $ds['PRICE'];
		$qty_sum += $ds['QTY'];
		$price_sum += $ds['PRICE'];
		$depositfee_sum += $ds['DEPOSITFEE'];
		$price_tot_sum += $price_tot;
		$buy_price_sum += $buy_price;
?>
						<tr>
							<td scope="row" class="lf"><a href="<?=$ds['LINKURL']?>" target="_blank"><?=$ds['LINKURL']?></a></td>
							<td scope="row" class="lf"><?=$ds['OPTFIELD']?></td>
							<td scope="row" class="lf"><?=$ds['OPTVALUE']?></td>
							<td scope="row" class="lf"><?=number_format($ds['QTY'])?></td>
							<td scope="row" class="lf"><?=number_format($ds['PRICE'])?></td>
							<td scope="row" class="lf"><?=number_format($buy_price)?></td>
							<td scope="row" class="lf"><?=number_format($ds['DEPOSITFEE'])?></td>
							<td scope="row" class="lf"><?=number_format($price_tot)?></td>
						</tr>
<?
	}
?>
						<tr>
							<td scope="row" class="lf" colspan="3">합계</td>
							<td scope="row" class="lf"><?=number_format($qty_sum)?></td>
							<td scope="row" class="lf"><?=number_format($price_sum)?></td>
							<td scope="row" class="lf"><?=number_format($buy_price_sum)?></td>
							<td scope="row" class="lf"><?=number_format($depositfee_sum)?></td>
							<td scope="row" class="lf"><?=number_format($price_tot_sum)?></td>
						</tr>
					</table>
					
					<div class='center mt20'>
<?
	if(CODE_REQ_STATUS($rs[0]['STATUS']) == 'R'){
?>
						<input type='button' value='주문서생성' class='btn_b01 w80' onclick="javascript:goInput();"/>
<?
	}
?>
						<input type='button' value='닫기' class='btn_b02 w80' onclick="javascript:doCancel();"/>
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