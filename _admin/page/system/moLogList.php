<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSession($url = '/_admin/');
	
	$_MENU1 = '1';		// 상단메뉴
	$_MENU2 = '9';		// 왼쪽메뉴

	$_NAVITITLE = "시스템관리 > 메시지로그";

	$arr_date = array();
	array_push($arr_date, date('Y-m-d'));
	array_push($arr_date, date('Y-m-d', strtotime('-7 day')) );
	array_push($arr_date, date('Y-m-d', strtotime('-15 day')) );
	array_push($arr_date, date('Y-m-d', strtotime('-1 month')) );
	array_push($arr_date, date('Y-m-d', strtotime('-3 month')) );
	//=====================================================
	//== 도움말 - Get Parameters
	//=====================================================
	$cal_1 = $cFnc->getReq('cal_1', date('Y-m-d', strtotime('-3 month')));
	$cal_2 = $cFnc->getReq('cal_2', date('Y-m-d'));
	$body  = $cFnc->getReq('body', '');
	$from_number = $cFnc->getReq('from_number', '');
	$to_number = $cFnc->getReq('to_number', '');
	$order_cont = $cFnc->getReq('order_cont', 'A.ID');
	$order_asc = $cFnc->getReq('order_asc', 'DESC');
	$search_list1 = $cFnc->getReq('search_list1', '');
	
	//=====================================================
	//== 도움말 - Set Variables
	//=====================================================
	$cPdo = new cPdo($ARR_DB_INFO_MO, true);
	
	// 페이징 세팅
	$nPageCnt = $cFnc->getReq('nPageCnt', 10);		// 블럭당 페이지 수
	$nListCnt = $cFnc->getReq('nListCnt', 10);		// 페이지당 리스트 수
	$nowPage = $cFnc->getReq('nowPage', 1);			// 현재 페이지
	$cPdo->setPagingInfo($nListCnt, $nPageCnt);
	
	// 리스트 정렬 화살표
	$arrow_{$order_cont} = "▼";
	if($order_asc == 'ASC') $arrow_{$order_cont} = "▲";
	
	// 리스트 드롭다운
	$arr_search_list1 = explode(',', $search_list1);
	$search_list1_type = $arr_search_list1[0];
	$search_list1_val = $arr_search_list1[1];
	
	// 날짜검색 TIMESTAMP형식으로
	$cal_1_ts = strtotime($cal_1 .' 00:00:00');
	$cal_2_ts = strtotime($cal_2 .' 23:59:59');
	
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
	
	//=====================================================
	//== 도움말 - Start Tran
	//=====================================================
	$arParam = Array();
	$qryOrder = "ORDER BY ". $order_cont ." ". $order_asc;
	$qryWhere = "WHERE A.TIMESTAMP BETWEEN ? AND ?";
	array_push($arParam, $cal_1_ts);
	array_push($arParam, $cal_2_ts);


	if($body != ''){
		$qryWhere .= " AND A.BODY like ?";
		array_push($arParam, '%'. $body .'%');
	}
	
	if($from_number != ''){
		$qryWhere .= " AND A.FROM_NUMBER LIKE ?";
		array_push($arParam, '%'. $from_number .'%');
	}
	if($to_number != ''){
		$qryWhere .= " AND A.TO_NUMBER LIKE ?";
		array_push($arParam, '%'. $to_number .'%');
	}
	
	$qry = "
		SELECT A.ID, A.MSG_ID, A.TYPE, A.FROM_NUMBER, A.FROM_TELCO, A.TO_NUMBER, A.TO_TELCO
		, A.SUBJECT, A.BODY, A.ATTACH_COUNT, A.STATUS, A.TIMESTAMP
		, B.ID AS ATTACHMENT_ID, B.ATTACH_ID, B.ATTACH_NAME, B.`ATTACH_MIME`, B.`ATTACH_CONTENT`, B.`INSERT_TIME`
		FROM MOA_MSG A
		LEFT OUTER JOIN MOA_ATTACHMENT B ON B.MSG_ID = A.MSG_ID AND A.TYPE = 'MMS'
		". $qryWhere ."
		". $qryOrder ."
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
		$rsList = $result['data'];
	}
	
	
	include $_SERVER["DOCUMENT_ROOT"]. "/_admin/h.php";
?>

<script type="text/javascript" src="/js/jquery.form.js"></script>

<script type="text/javascript">
	$(document).ready(function() {
		// 달력 플러그인
		$('#cal_1, #cal_2').datepicker({
			changeYear: false,
			changeMonth: false,
			minDate: '<?=date("Y-m-d", strtotime("-12 month"))?>',
			maxDate: '<?=date("Y-m-d")?>',
			numberOfMonths: 1,
			showOn:"button",
			buttonImage:"/_admin/images/icon_calendar.png",
			buttonImageOnly:true
		});

		// 리스트 드롭다운 버튼
		$(".item_use").click(function(){
			$(".sel_use").toggle();
		});

		$('.th_sel').css({display:'none'});
		// 리스트 드롭다운 버튼
	});

	// 검색
	function doSearch(type, value){
		if(type != undefined){
			$('#search_list1').val(type +','+ value);
		}

		$('#frmSearch').submit();
	}

	// 정렬
	function doOrder(cont){
		var order_asc = $('#order_asc').val();
		var order_cont = $('#order_cont').val();

		if(cont == order_cont){
			if(order_asc == 'DESC') order_asc = 'ASC';
			else if(order_asc == 'ASC') order_asc = 'DESC';
		}
		else{
			order_asc = 'DESC';
		}

		$('#order_cont').val(cont);
		$('#order_asc').val(order_asc);
		$('#frmSearch').submit();
	}

	// 엔터키이벤트
	function doEnter(){
		if(event.keyCode == 13){
			doSearch();
		}
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

	// 상세
	function goDetail(id){
		location.href = "moLogView.php?id="+ id +"&<?=$gStr2?>";
	}
	
	function set_date(v1, v2){
		$('#cal_1').val(v1);
		$('#cal_2').val(v2);
	}
</script>

	<form method="post" name="frmProc" id="frmProc" action="">
		<input type="hidden" name="pageaction" id="pageaction" value="">
		<input type="hidden" name="hscode_seq" id="hscode_seq" value="">
		<input type="hidden" name="use_yn" id="use_yn" value="">
	</form>
	
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
				<form method="get" name="frmSearch" id="frmSearch" action="<?=$_SERVER['PHP_SELF']?>">
				<input type="hidden" name="search_list1" id="search_list1" value="<?=$search_list1?>">
				<input type="hidden" name="order_cont" id="order_cont" value="<?=$order_cont?>">
				<input type="hidden" name="order_asc" id="order_asc" value="<?=$order_asc?>">
				<table border="0" width="100%" cellspacing="0" cellpadding="0" class='adtb_02 '>
					<colgroup>
						<col width='20%' />
						<col width='30%' />
						<col width='20%' />
						<col width='30%' />
					</colgroup>
					<tr>
						<th scope="row">기간검색</th>
						<td class='lf' colspan=3>
							<input name="cal_1" id="cal_1" type="text" class='w100' value='<?=$cal_1?>' readonly /> ~ <input name="cal_2" id="cal_2" type="text" class='w100' value='<?=$cal_2?>' readonly />
							<input type="button" value="오늘" class='btn_s_03 w60' onclick="javascript:set_date('<?=$arr_date[0]?>','<?=$arr_date[0]?>');">
							<input type="button" value="7일" class='btn_s_03 w60' onclick="javascript:set_date('<?=$arr_date[1]?>','<?=$arr_date[0]?>');">
							<input type="button" value="15일" class='btn_s_03 w60' onclick="javascript:set_date('<?=$arr_date[2]?>','<?=$arr_date[0]?>');">
							<input type="button" value="1개월" class='btn_s_03 w60' onclick="javascript:set_date('<?=$arr_date[3]?>','<?=$arr_date[0]?>');">
							<input type="button" value="3개월" class='btn_s_03 w60' onclick="javascript:set_date('<?=$arr_date[4]?>','<?=$arr_date[0]?>');">
						</td>
					</tr>					
					<tr>
						<th scope="row">내용</th>
						<td class='lf'>
							<input name="body" id="body" type="text" class='w200' value="<?=$body?>" onkeypress="javascript:doEnter();" />
						</td>
						<th scope="row">발신번호</th>
						<td class='lf'>
							<input name="from_number" id="from_number" type="text" class='w200' value="<?=$from_number?>" onkeypress="javascript:doEnter();" />
						</td>
					</tr>
					<tr>
						<th scope="row">수신번호</th>
						<td class='lf'>
							<input name="to_number" id="to_number" type="text" class='w200' value="<?=$to_number?>" onkeypress="javascript:doEnter();" />
						</td>
						<th scope="row"></th>
						<td class='lf'></td>
					</tr>
				</table>
				</form>
				
				
				<div class='mt10'>
					<p class='fr'>
						<input type="button" name="btn_search" id="btn_search" value='조회' class='btn_01 w80' onclick="javascript:doSearch();" />
					</p>					
				</div>
				
				<div class="both"></div>
				
				<table width="100%" border="0" cellspacing="0" cellpadding="0"	class='adtb_01 mt10'>
					<colgroup>
						<col width='5%' />
						<col width='5%' />
						<col width='10%' />
						<col width='10%' />
						<col width='*' />
						<col width='12%' />
					</colgroup>
					<tr>
						<th scope="col">NO</th>
						<th scope="col">종류</th>
						<th scope="col">발신번호</th>
						<th scope="col">수신번호</th>
						<th scope="col">내용</th>
						<th scope="col">수신일</th>
					</tr>
<?
	if($nTotalCnt > 0){
		foreach($rsList as $i=>$ds){
			$PageS = $nTotalCnt - $i - (($nowPage - 1) * $nListCnt);
			
			$ds['RECEIVE_TIME'] = date('Y-m-d H:i:s', $ds['TIMESTAMP']);
?>
					<tr>
						<td><?=$PageS?></td>
						<td><?=$ds['TYPE']?></td>
						<td><?=$cFnc->MaskingTelNo($ds['FROM_NUMBER'])?></td>
						<td><?=$cFnc->MaskingTelNo($ds['TO_NUMBER'])?></td>
						<td class='bg_01'><a href="javascript:goDetail('<?=$ds['ID']?>');"><?=nl2br($ds['BODY'])?></a></td>
						<td><?=$ds['RECEIVE_TIME']?></td>
					</tr>
<?
		}
	}
	else{
?>
					<tr>
						<td colspan="6" height="200">검색 결과가 없습니다</td>
					</tr>
<?
	}
?>
				</table>
				
				<!-- paging -->
				<?=$cFnc->getAdmPaging( $nPageCnt, $nowPage, $nTotalPage, $gStr )?>
				<!-- //paging -->
				
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