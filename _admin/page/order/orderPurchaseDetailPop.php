<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSession($url = '/_admin/');

	$_MENU1 = '4';		// 상단메뉴
	$_MENU2 = '';		// 왼쪽메뉴

	$_NAVITITLE = "주문관리 > 구매현황상세";	

	//=====================================================
	//== 도움말 - Get Parameters
	//=====================================================
	$purchase_seq = $cFnc->getReq('purchase_seq', '');

	// =====================================================
	// Set Variables
	// =====================================================
	$cPdo = new cPdo($ARR_DB_INFO);
	
	// =====================================================
	// Set Parameters (Get Types)
	// =====================================================
	$gStr = "";
	$gStr = $cFnc->GetStr( $gStr, "purchase_seq", $purchase_seq );
	
	// =====================================================
	// Start Tran
	// =====================================================
	// 주문아이템
	$arParam = array();
	array_push($arParam, $purchase_seq);
	$qry = "
		SELECT B.ITEM_SEQ, B.ITEM_ROWID, B.ITEMID, B.PRODUCTNAME
		, A.PURCHASE_SEQ, A.P_LINKURL, A.P_QTY, A.P_PRICE, A.P_DELIVERYFEE, A.P_PRICESUM, A.P_OPTFIELD, A.`P_OPTVALUE`, A.`P_MEMO`, A.P_STATUS, A.REG_DT, A.MOD_DT, A.P_DISCOUNT
		, A.APPROVAL_DT, A.APPROVAL_NO, A.DELIVER_NO, A.PAYMENT_TP
		, (SELECT ADM_NM FROM TB_ADM WHERE ADM_SEQ = A.ADM_SEQ) AS ADM_NM
		, (SELECT ADM_ID FROM TB_ADM WHERE ADM_SEQ = A.ADM_SEQ) AS ADM_ID
		, C.ORDERID
		, D.050_NO
		FROM TB_ORDER_PURCHASE A
		INNER JOIN TB_ORDER_ITEM B ON B.ITEM_SEQ = A.ITEM_SEQ
		INNER JOIN TB_ORDER C ON C.ORDER_SEQ = B.ORDER_SEQ
		LEFT OUTER JOIN TB_ORDER_PURCHASE_050 D ON D.PURCHASE_SEQ = A.PURCHASE_SEQ
		WHERE A.PURCHASE_SEQ = ?
		ORDER BY B.ITEM_SEQ ASC, A.PURCHASE_SEQ DESC
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('data', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$ds = $result['data'];
	
	
	// 구매히스토리
	$arParam = array();
	array_push($arParam, $purchase_seq);
	$qry = "
		SELECT A.*
		, C.ADM_ID, C.ADM_NM
		FROM TB_ORDER_PURCHASE_STATUS A
		INNER JOIN TB_ORDER_PURCHASE B ON B.PURCHASE_SEQ = A.PURCHASE_SEQ
		INNER JOIN TB_ADM C ON C.ADM_SEQ = A.ADM_SEQ
		WHERE A.PURCHASE_SEQ = ?
		ORDER BY A.PURCHASE_SEQ ASC, A.STATUS_SEQ ASC
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('list', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$rsStatus = $result['data'];
	
	
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
</script>
	
	<div id='contain'>
		<!-- 전체 100% CONTENTS -->
		<div id='content_wrap'> <!-- 100%의 전체 컨텐츠 영역-->
		
			<!-- content -->
			<div class='content w100p'> <!-- 원하는 컨텐츠 넓이를 추가 w960 클래스는 width:960px란 의미 -->

				<h2><?=$_NAVITITLE?></h2>
				<div class='w100p'><!-- 컨텐츠 가로길이 줄임-->
					
					<div class="both"></div>
					
					<table border="0" width="100%" cellspacing="0" cellpadding="0" class='adtb_02 mt10'>
						<colgroup>
							<col width='20%' />
							<col width='80%' />
						</colgroup>
						<tr>
							<th scope="row">구매코드</th>
							<td class='lf'>
								<?=$ds['PURCHASE_SEQ']?>
							</td>
						</tr>
						<tr>
							<th scope="row">주문상품</th>
							<td class='lf'>
								<?=$ds['PRODUCTNAME']?>
							</td>
						</tr>
						<tr>
							<th scope="row">구매URL</th>
							<td class='lf'>
								<?=$ds['P_LINKURL']?>
							</td>
						</tr>
						<tr>
							<th scope="row">수량</th>
							<td class='lf'>
								<?=number_format($ds['P_QTY'])?>
							</td>
						</tr>
						<tr>
							<th scope="row">단가</th>
							<td class='lf'>
								<?=number_format($ds['P_PRICE'])?>
							</td>
						</tr>
						<tr>
							<th scope="row">배송비</th>
							<td class='lf'>
								<?=number_format($ds['P_DELIVERYFEE'])?>
							</td>
						</tr>
						<tr>
							<th scope="row">할인금액</th>
							<td class='lf'>
								<?=number_format($ds['P_DISCOUNT'])?>
							</td>
						</tr>
						<tr>
							<th scope="row">합계</th>
							<td class='lf'>
								<?=number_format($ds['P_PRICESUM'])?>
							</td>
						</tr>
						<tr>
							<th scope="row">옵션필드</th>
							<td class='lf'>
								<?=$ds['P_OPTFIELD']?>
							</td>
						</tr>
						<tr>
							<th scope="row">옵션값</th>
							<td class='lf'>
								<?=$ds['P_OPTVALUE']?>
							</td>
						</tr>
						<tr>
							<th scope="row">메모</th>
							<td class='lf'>
								<?=nl2br($ds['P_MEMO'])?>
							</td>
						</tr>
						<tr>
							<th scope="row">상태</th>
							<td class='lf'>
								<?=CODE_PURCHASE_STATUS($ds['P_STATUS'])?>
							</td>
						</tr>
						<tr>
							<th scope="row">050번호</th>
							<td class='lf'>
								<?=$cFnc->MaskingTelNo($ds['050_NO'])?>
							</td>
						</tr>
						<tr>
							<th scope="row">결제종류</th>
							<td class='lf'>
								<?=CODE_PAYMENT_TP($ds['PAYMENT_TP'])?>
							</td>
						</tr>
						<tr>
							<th scope="row">결제승인일</th>
							<td class='lf'>
								<?=$ds['APPROVAL_DT']?>
							</td>
						</tr>
						<tr>
							<th scope="row">결제승인번호</th>
							<td class='lf'>
								<?=$ds['APPROVAL_NO']?>
							</td>
						</tr>
						<tr>
							<th scope="row">운송장번호</th>
							<td class='lf'>
								<?=$ds['DELIVER_NO']?>
							</td>
						</tr>
						<tr>
							<th scope="row">관리자</th>
							<td class='lf'>
								<?=$ds['ADM_NM']?> (<?=$ds['ADM_ID']?>)
							</td>
						</tr>
					</table>
					
					<div class='mt20'>
						<h2>구매정보이력</h2>
					</div>
					<table border="0" width="100%" cellspacing="0" cellpadding="0" class='adtb_03 mt10'>
						<colgroup>
							<col width='8%' />
							<col width='17%' />
							<col width='12%' />
						</colgroup>
						<tr>
							<th scope="row">상태</th>
							<th scope="row">일자</th>
							<th scope="row">담당자</th>
							<th scope="row">메모</th>
						</tr>
<?
	foreach($rsStatus as $i=>$ds){
		$status_str = CODE_PURCHASE_STATUS($ds['STATUS']);
		$bgcolor_str = '';
		
		if($ds['STATUS'] == 'P'){
			$bgcolor_str = '#FFDAB9';
		}
?>
						<tr>
							<td style="background-color:<?=$bgcolor_str?>;" scope="row" class="ct"><?=$status_str?></td>
							<td style="background-color:<?=$bgcolor_str?>;" scope="row" class="ct"><?=$ds['REG_DT']?></td>
							<td style="background-color:<?=$bgcolor_str?>;" scope="row" class="ct"><?=$ds['ADM_NM']?>(<?=$ds['ADM_ID']?>)</td>
							<td style="background-color:<?=$bgcolor_str?>;" scope="row" class="ct"><?=nl2br($ds['MEMO'])?></td>
						</tr>
<?
	}
?>
					</table>
					
					<div class='center mt20' style="clear:both;">
						<input type='button' value='닫기' class='btn_b02 w80' onclick="javascript:doCancel();"/>
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