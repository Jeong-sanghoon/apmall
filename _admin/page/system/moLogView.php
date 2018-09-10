<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSession($url = '/_admin/');

	$_MENU1 = '1';		// 상단메뉴
	$_MENU2 = '9';		// 왼쪽메뉴
	
	$_NAVITITLE = "시스템관리 > 메시지로그";

	// ===================================================
	// get parameter
	// ===================================================
	$id = $cFnc->getReq('id', '');
	
	$cal_1 = $cFnc->getReq('cal_1', date('Y-m-d', strtotime('-3 month')));
	$cal_2 = $cFnc->getReq('cal_2', date('Y-m-d'));
	$body  = $cFnc->getReq('body', '');
	$from_number = $cFnc->getReq('from_number', '');
	$to_number = $cFnc->getReq('to_number', '');
	$order_cont = $cFnc->getReq('order_cont', 'A.ID');
	$order_asc = $cFnc->getReq('order_asc', 'DESC');
	$search_list1 = $cFnc->getReq('search_list1', '');

	$nPageCnt = $cFnc->getReq('nPageCnt', 10);		// 블럭당 페이지 수
	$nListCnt = $cFnc->getReq('nListCnt', 10);		// 페이지당 리스트 수
	$nowPage = $cFnc->getReq('nowPage', 1);			// 현재 페이지

	// =====================================================
	// Set Variables
	// =====================================================
	$cPdo = new cPdo($ARR_DB_INFO_MO);
	
	
	// =====================================================
	// Set Parameters (Get Types)
	// =====================================================
	$gStr = "";
	$gStr = $cFnc->GetStr( $gStr, "cal_1", $cal_1 );
	$gStr = $cFnc->GetStr( $gStr, "cal_2", $cal_2 );
	$gStr = $cFnc->GetStr( $gStr, "body", $body );
	$gStr = $cFnc->GetStr( $gStr, "from_number", $from_number );
	$gStr = $cFnc->GetStr( $gStr, "to_number", $to_number );
	$gStr = $cFnc->GetStr( $gStr, "order_cont", $order_cont );
	$gStr = $cFnc->GetStr( $gStr, "order_asc", $order_asc );
	$gStr = $cFnc->GetStr( $gStr, "search_list1", $search_list1 );

	$gStr2 = "";
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_1", $cal_1 );
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_2", $cal_2 );
	$gStr2 = $cFnc->GetStr( $gStr2, "body", $body );
	$gStr2 = $cFnc->GetStr( $gStr2, "from_number", $from_number );
	$gStr2 = $cFnc->GetStr( $gStr2, "to_number", $to_number );
	$gStr2 = $cFnc->GetStr( $gStr2, "order_cont", $order_cont );
	$gStr2 = $cFnc->GetStr( $gStr2, "order_asc", $order_asc );
	$gStr2 = $cFnc->GetStr( $gStr2, "search_list1", $search_list1 );
	$gStr2 = $cFnc->GetStr( $gStr2, "nListCnt", $nListCnt );
	$gStr2 = $cFnc->GetStr( $gStr2, "nowPage", $nowPage );
	$gStr2 = $cFnc->GetStr( $gStr2, "nPageCnt", $nPageCnt );

	// =====================================================
	// Start Tran
	// =====================================================
	$arParam = array();
	array_push($arParam, $id);
	$qry = "
		SELECT A.ID, A.MSG_ID, A.TYPE, A.FROM_NUMBER, A.FROM_TELCO, A.TO_NUMBER, A.TO_TELCO
		, A.SUBJECT, A.BODY, A.ATTACH_COUNT, A.STATUS, A.TIMESTAMP
		, B.ID AS ATTACHMENT_ID, B.ATTACH_ID, B.ATTACH_NAME, B.`ATTACH_MIME`, B.`ATTACH_CONTENT`, B.`INSERT_TIME`
		FROM MOA_MSG A
		LEFT OUTER JOIN MOA_ATTACHMENT B ON B.MSG_ID = A.MSG_ID AND A.TYPE = 'MMS'
		WHERE A.ID = ?
	";
	$rslt = $cPdo->execQuery('data', $qry, $arParam);
	$ds = $rslt['data'];
	$ds['RECEIVE_TIME'] = date('Y-m-d H:i:s', $ds['TIMESTAMP']);
	

	include $_SERVER["DOCUMENT_ROOT"]. "/_admin/h.php";
?>
<script>
	// 목록
	function goList(){
		history.back();
	}
	
	// 파일다운로드
	function doDownload(file_ori, file){
		location.href = "/_lib/Private/pFiledown.php?file_ori="+ file_ori +"&file="+ file;
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

					<table border="0" width="100%" cellspacing="0" cellpadding="0" class='adtb_02 '>
						<colgroup>
							<col width='20%' />
							<col width='80%' />
						</colgroup>
						<tr>
							<th scope="row">메시지번호</th>
							<td class='lf'><?=$ds['ID']?></td>
						</tr>
						<tr>
							<th scope="row">메시지종류</th>
							<td class='lf'><?=$ds['TYPE']?></td>
						</tr>
						<tr>
							<th scope="row">발신번호</th>
							<td class='lf'><?=$cFnc->MaskingTelNo($ds['FROM_NUMBER'])?></td>
						</tr>
						<tr>
							<th scope="row">수신번호</th>
							<td class='lf'><?=$cFnc->MaskingTelNo($ds['TO_NUMBER'])?></td>
						</tr>
						<tr>
							<th scope="row">내용</th>
							<td class='lf'><?=nl2br($ds['BODY'])?></td>
						</tr>
						<tr>
							<th scope="row">수신일</th>
							<td class='lf'><?=$ds['RECEIVE_TIME']?></td>
						</tr>
					</table>

					<div class='fr mt10'>
						<span class='button w60 h_22'><a href='javascript:;' class='deep_grey' onclick="javascript:goList();">목록</a></span>
					</div>

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