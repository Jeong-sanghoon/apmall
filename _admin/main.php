<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";

	chkSession($url = '/_admin/');
	
	$_MENU1 = '';
	$_MENU2 = '';
	
	// =====================================================
	// Set Variables
	// =====================================================
	$cPdo = new cPdo($ARR_DB_INFO, true);
	
	// =====================================================
	// Start Tran
	// =====================================================
	// 주문처리현황
	$arParam = array();
	$qry = "
		SELECT COUNT(MAIN.ORDER_SEQ) AS STAT_A
		FROM (
			SELECT A.ORDER_SEQ
			FROM TB_ORDER A
			INNER JOIN TB_ORDER_ITEM B ON B.ORDER_SEQ = A.ORDER_SEQ
			WHERE A.CANCEL_YN <> 'Y'
			AND B.STATUS = 'A'
			GROUP BY A.ORDER_SEQ
		) MAIN
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('data', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$stat_a = $result['data']['STAT_A'];
	
	$arParam = Array();
	$qry = "
		SELECT SUM(CASE WHEN `STATUS` = 'B' THEN 1 ELSE 0 END) AS STAT_B
		, SUM(CASE WHEN `STATUS` = 'C' THEN 1 ELSE 0 END) AS STAT_C
		, SUM(CASE WHEN `STATUS` = 'D' THEN 1 ELSE 0 END) AS STAT_D
		, SUM(CASE WHEN `STATUS` = 'E' THEN 1 ELSE 0 END) AS STAT_E
		FROM TB_ORDER_ITEM A
		INNER JOIN TB_ORDER B ON B.ORDER_SEQ = A.ORDER_SEQ
		WHERE B.CANCEL_YN <> 'Y'
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('data', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$dsOrder = $result['data'];
	
	$arParam = Array();
	$qry = "
		SELECT SUM(CASE WHEN MAIN.STATUS = 'F' THEN 1 ELSE 0 END) AS STAT_F
		, SUM(CASE WHEN MAIN.STATUS = 'G' AND GOODSCF_YN = 'N' THEN 1 ELSE 0 END) AS STAT_G
		FROM (
			SELECT D.DELIVERY_SEQ, A.STATUS, A.GOODSCF_YN, COUNT(D.DELIVERY_SEQ) AS CNT
			FROM TB_ORDER_ITEM A
			INNER JOIN TB_ORDER B ON B.ORDER_SEQ = A.ORDER_SEQ
			INNER JOIN TB_DELIVERY_ITEM C ON C.ITEM_SEQ = A.ITEM_SEQ AND C.`ITEM_ROWID` = A.`ITEM_ROWID`
			INNER JOIN TB_DELIVERY D ON D.`DELIVERY_SEQ` = C.`DELIVERY_SEQ`
			WHERE B.CANCEL_YN <> 'Y'
			GROUP BY D.`DELIVERY_SEQ`, A.STATUS, A.GOODSCF_YN
		) MAIN
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('data', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$dsOrder2 = $result['data'];
	
	$dsOrder['STAT_A'] = $stat_a;
	$dsOrder['STAT_F'] = $dsOrder2['STAT_F'];
	$dsOrder['STAT_G'] = $dsOrder2['STAT_G'];
	
	
	// 회원가입현황
	$arParam = Array();
	$qry = "
		SELECT COUNT(USER_ID) AS TOTAL_CNT
		, SUM(CASE WHEN DATE(REG_DT) = DATE(NOW()) THEN 1 ELSE 0 END) AS TODAY_CNT
		FROM TB_USER
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('data', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$dsUser = $result['data'];
	
	
	// 제품현황
	$arParam = Array();
	$qry = "
		SELECT SUM(CASE WHEN P_STATUS <> 'S' THEN 1 ELSE 0 END) AS SALE_ON
		, SUM(CASE WHEN P_STATUS = 'S' THEN 1 ELSE 0 END) AS SALE_OFF
		FROM TB_PRODUCT
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('data', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$dsSale = $result['data'];
	
	
	// TODAY
	$arParam = Array();
	$qry = "
		SELECT COUNT(USERMSTID) AS CNT
		FROM TB_ORDER_REQ
		WHERE DATE(REG_DT) = DATE(NOW())
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('data', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$today_cnt1 = $result['data']['CNT'];
	
	$arParam = Array();
	$qry = "
		SELECT COUNT(ITEM_SEQ) AS CNT
		FROM TB_ORDER_ITEM
		WHERE DATE(DEPOSIT_DT) = DATE(NOW())
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('data', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$today_cnt2 = $result['data']['CNT'];
	
	
	include $_SERVER["DOCUMENT_ROOT"]. "/_admin/h.php";
?>

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
			<div class='content w960'> <!-- 원하는 컨텐츠 넓이를 추가 w960 클래스는 width:960px란 의미 -->

			   <!-- 가입업체-->
			   <div class='m_dan'>
					<h2 class='bg_01'>주문처리현황</h2>
					
					<table width="100%" border="0" cellspacing="3" cellpadding="0" class='m_tb'>
					<colgroup>
						<col width='30%' />
						<col width='70%' />
					</colgroup>
					  <tr>
						<th scope="row"><p>주문접수</p></th>
						<td><?=$dsOrder['STAT_A']?> <a href='/_admin/page/order/orderAccept.php'>바로가기</a></td>
					  </tr>
					  <tr>
						<th scope="row"><p>결제확인</p></th>
						<td><?=$dsOrder['STAT_B']?> <a href='/_admin/page/order/orderPay.php'>바로가기</a></td>
					  </tr>
					  <tr>
						<th scope="row"><p>상품준비</p></th>
						<td><?=$dsOrder['STAT_C']?> <a href='/_admin/page/order/orderGoodsReady.php'>바로가기</a></td>
					  </tr>
					  <tr>
						<th scope="row"><p>입고대기</p></th>
						<td><?=$dsOrder['STAT_D']?> <a href='/_admin/page/order/orderEnterReady.php'>바로가기</a></td>
					  </tr>
					  <tr>
						<th scope="row"><p>입고완료</p></th>
						<td><?=$dsOrder['STAT_E']?> <a href='/_admin/page/order/orderEnterFin.php'>바로가기</a></td>
					  </tr>
					  <tr>
						<th scope="row"><p>출고요청</p></th>
						<td><?=$dsOrder['STAT_F']?> <a href='/_admin/page/order/orderOutReq.php'>바로가기</a></td>
					  </tr>
					  <tr>
						<th scope="row"><p>출고완료</p></th>
						<td><?=$dsOrder['STAT_G']?> <a href='/_admin/page/order/orderOutFin.php'>바로가기</a></td>
					  </tr>
					</table>
			   </div>
			   <!--// 가입업체-->
			   
			   <!-- 오늘의 퀵-->
			   <div class='m_dan'>
					<h2 class='bg_02'>회원가입현황</h2>
					
					<table width="100%" border="0" cellspacing="3" cellpadding="0" class='m_tb'>
					<colgroup>
						<col width='30%' />
						<col width='70%' />
					</colgroup>
					  <tr>
						<th scope="row"><p>오늘신규</p></th>
						<td><?=$dsUser['TODAY_CNT']?> </td>
					  </tr>
					  <tr>
						<th scope="row"><p>누적현황</p></th>
						<td><?=$dsUser['TOTAL_CNT']?> </td>
					  </tr>					  
					</table>
			   </div>
			   <!--// 오늘의 퀵-->
			   
			   <!-- 이달의 정산-->
			   <div class='m_dan'>
					<h2 class='bg_03'>제품현황</h2>
					
					<table width="100%" border="0" cellspacing="3" cellpadding="0" class='m_tb'>
					<colgroup>
						<col width='30%' />
						<col width='70%' />
					</colgroup>
					  <tr>
						<th scope="row"><p>판매중</p></th>
						<td><?=$dsSale['SALE_ON']?> </td>
					  </tr>
					  <tr>
						<th scope="row"><p>판매중지</p></th>
						<td><?=$dsSale['SALE_OFF']?> </td>
					  </tr>					  
					</table>
			   </div>
			   <!--// 이달의 정산-->
			   
			   <!-- Q&A --> 
			 	<div class='m_dan'>
					<h2 class='bg_04'>TODAY</h2>
					
					<table width="100%" border="0" cellspacing="3" cellpadding="0" class='m_tb'>
					<colgroup>
						<col width='30%' />
						<col width='70%' />
					</colgroup>
					  <tr>
						<th scope="row"><p>오늘주문접수</p></th>
						<td><?=$today_cnt1?> </td>
					  </tr>
					  <tr>
						<th scope="row"><p>오늘결제확인</p></th>
						<td><?=$today_cnt2?> </td>
					  </tr>
					  <!--
					  <tr>
						<th scope="row"><p>오늘매출</p></th>
						<td><?=$dsQnA['REPL_N']?> </td>
					  </tr>
					  -->
					</table>
			   </div>
			   <!--// Q&A-->
				
			</div>
			<!--// content -->
			
		</div>
		<!--// 전체 100% CONTENTS	 -->
		
	</div>
	<!--//contain-->
	
	</div>
	<!--//wrap-->

<?include $_SERVER["DOCUMENT_ROOT"]. "/_admin/f.php";?>
