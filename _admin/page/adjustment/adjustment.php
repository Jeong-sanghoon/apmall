<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";

	chkSession($url = '/_admin/');

	$_MENU1 = '7';		// 상단메뉴
	$_MENU2 = '1';		// 왼쪽메뉴

	$_NAVITITLE = "정산관리 > 정산현황";


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
	$orderid = $cFnc->getReq('orderid', '');
	$name = $cFnc->getReq('name', '');
	$order_cont = $cFnc->getReq('order_cont', 'A.ORDER_SEQ');
	$order_asc = $cFnc->getReq('order_asc', 'DESC');
	$search_list1 = $cFnc->getReq('search_list1', '');

	//=====================================================
	//== 도움말 - Set Variables
	//=====================================================
	$cPdo = new cPdo($ARR_DB_INFO, true);

	// 페이징 세팅
	$nPageCnt = $cFnc->getReq('nPageCnt', 10);		// 블럭당 페이지 수
	$nListCnt = $cFnc->getReq('nListCnt', 500);		// 페이지당 리스트 수
	$nowPage = $cFnc->getReq('nowPage', 1);			// 현재 페이지
	$cPdo->setPagingInfo($nListCnt, $nPageCnt);

	// 리스트 정렬 화살표
	$arrow_{$order_cont} = "▼";
	if($order_asc == 'ASC') $arrow_{$order_cont} = "▲";

	// 리스트 드롭다운
	$arr_search_list1 = explode(',', $search_list1);
	$search_list1_type = $arr_search_list1[0];
	$search_list1_val = $arr_search_list1[1];

	// =====================================================
	// Set Parameters (Get Types)
	// =====================================================
	$gStr = "";
	$gStr = $cFnc->GetStr( $gStr, "cal_1", $cal_1 );
	$gStr = $cFnc->GetStr( $gStr, "cal_2", $cal_2 );
	$gStr = $cFnc->GetStr( $gStr, "orderid", $orderid );
	$gStr = $cFnc->GetStr( $gStr, "name", $name );
	$gStr = $cFnc->GetStr( $gStr, "order_cont", $order_cont );
	$gStr = $cFnc->GetStr( $gStr, "order_asc", $order_asc );
	$gStr = $cFnc->GetStr( $gStr, "search_list1", $search_list1 );

	$gStr2 = "";
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_1", $cal_1 );
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_2", $cal_2 );
	$gStr2 = $cFnc->GetStr( $gStr2, "orderid", $orderid );
	$gStr2 = $cFnc->GetStr( $gStr2, "name", $name );
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
	$qryOrder = "ORDER BY D.ORDER_SEQ, C.DEPOSIT_DT, C.GOODSSC_DT";
	$qryWhere = "WHERE A.REG_DT BETWEEN ? AND ?";

	array_push($arParam, $cal_1 .' 00:00:00');
	array_push($arParam, $cal_2 .' 23:59:59');

	if($orderid != ''){
		$qryWhere .= " AND D.ORDERID = ?";
		array_push($arParam, $orderid);
	}
	if($name != ''){
		$qryWhere .= " AND D.NAME = ?";
		array_push($arParam, $name);
	}

	$qry = "
		SELECT C.REG_DT, C.DEPOSIT_DT, C.GOODSSC_DT, D.ORDERID
		, C.WEARSC_DT, C.PRODUCTNAME, C.OPTVALUE, C.OPTFIELD
		, C.QTY, C.PRICE, OP.P_DELIVERYFEE, OP.P_DISCOUNT, (OP.P_PRICESUM-OP.P_DELIVERYFEE) AS PRODUCTSUM, OP.P_PRICESUM
		, '' AS INVOICE_PRICE
		, IFNULL(C.SUMPRICE_VND,0) AS SUMPRICE_VND
		, A.W_PRICE_P_VND, C.DEPOSITFEE_VND, (C.SUMPRICE_VND-C.DEPOSITFEE_VND) AS B_FEE
		, D.NAME, A.WEIGHT
		, A.REAL_WEIGHT AS CJWEIGHT
		, A.REAL_W_PRICE AS CJPRICE
		, A.HBL_CD AS HBLNO
		, A.MEMO AS MEMO
		, D.MEMO AS MEMOD
		FROM TB_DELIVERY A
		INNER JOIN TB_DELIVERY_ITEM B ON A.DELIVERY_SEQ = B.DELIVERY_SEQ
		INNER JOIN TB_ORDER_ITEM C ON B.ITEM_SEQ = C.ITEM_SEQ
		INNER JOIN TB_ORDER D ON C.ORDER_SEQ = D.ORDER_SEQ
		INNER JOIN (
			SELECT ITEM_SEQ, SUM(P_PRICE) AS P_PRICE, SUM(P_DELIVERYFEE) AS P_DELIVERYFEE, SUM(P_PRICESUM) AS P_PRICESUM, SUM(P_DISCOUNT) AS P_DISCOUNT
			FROM TB_ORDER_PURCHASE OP
			WHERE P_STATUS = 'E'
			GROUP BY OP.ITEM_SEQ
		) OP ON OP.ITEM_SEQ = C.ITEM_SEQ
		".$qryWhere."
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->getCntExec($qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$dsCount = $result['data'];			// 전체카운트

	if(is_array($dsCount)){
		$nTotalCnt = $dsCount["total"];
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


	// 상태변경
	function doProc(){
		if($('input[name="chkitem"]').is(':checked') == ''){alert("체크박스를 체크해 주세요"); return false;}

		if(confirm("선택한 항목의 상태를 변경하시겠습니까?")){
			var arrChk = new Array();
			$('input[name="chkitem"]:checked').each(function(){
				arrChk.push($(this).val());
			});

			$('#pageaction').val('pay');
			$('#arr_order_seq').val(arrChk);
			$('#status').val('B');

			$.ajax({
				type:"POST",
				dataType : 'json',
				url: "orderProc.php",
				data: $('#frmProc').serialize(),
				async: false,
				success: function(obj){
					if(obj.status == 0){
						// 실패
						alert(obj.msg);
					}
					else{
						// 성공
						alert(obj.msg);
						location.reload();
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


	// 상세
	function goDetail(seq){
		window.open("orderDetail.php?order_seq="+ seq);
	}


	// 인쇄
	function doPrint(){
		AREA_PRINT(".content");
	}


	// 엑셀저장
	function doExcel(){
		location.href = "adjustmentExcel.php?<?=$gStr?>";
	}


	// 캘린더세팅(오늘, 7일, 15일등등 버튼)
	function set_date(v1, v2){
		$('#cal_1').val(v1);
		$('#cal_2').val(v2);
	}

</script>

	<form method="post" name="frmProc" id="frmProc" action="">
		<input type="hidden" name="pageaction" id="pageaction" value="">
		<input type="hidden" name="arr_order_seq" id="arr_order_seq" value="">
		<input type="hidden" name="status" id="status" value="">
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
				<table border="0" width="100%" cellspacing="0" cellpadding="0" class='adtb_02'>
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
						<th scope="row">주문번호</th>
						<td class='lf'>
							<input name="orderid" id="orderid" type="text" class='w200' value="<?=$orderid?>" onkeypress="javascript:doEnter();" />
						</td>
						<th scope="row">주문자</th>
						<td class='lf'>
							<input name="name" id="name" type="text" class='w200' value="<?=$name?>" onkeypress="javascript:doEnter();" />
						</td>
						<!--
						<th scope="row">사용여부</th>
						<td class='lf'>
							<select name="use_yn" id="use_yn">
								<option value="">항목</option>
								<option value="Y" <?=$cFnc->CodeString($use_yn, 'Y', 'selected', '')?>>사용</option>
								<option value="N" <?=$cFnc->CodeString($use_yn, 'N', 'selected', '')?>>미사용</option>
							</select>
						</td>
						-->
					</tr>
					<!--
					<tr>
						<th scope="row">이메일</th>
						<td class='lf'>
							<input name="email" id="email" type="text" class='w200' value="<?=$email?>" onkeypress="javascript:doEnter();" />
						</td>
						<th scope="row"></th>
						<td class='lf'></td>
					</tr>
					-->
				</table>
				</form>


				<div class='mt10'>
					<p class='fr'>
						<input type="button" name="btn_search" id="btn_search" value='조회' class='btn_01 w80' onclick="javascript:doSearch();" />
					</p>
				</div>

				<div class="both"></div>

				<div class='mt10'>
					<input type='button' value='인쇄' class='btn_02 w60' onclick="javascript:doPrint();" />
					<input type='button' value='액셀저장' class='btn_02 w80' onclick="javascript:doExcel();" />
				</div>

				<div class='scroll'>		<!-- scroll_sample -->
					<div style='width:3500px;'>		<!-- scroll_sample -->
						<table width="100%" border="0" cellspacing="0" cellpadding="0"	class='adtb_01'>
							<colgroup>
								<!-- <col width='3%' /> -->
								<col width='50px' />
								<col width='80px' />
								<col width='80px' />
								<col width='80px' />
								<col width='160px' />
								<col width='80px' />
								<col width='300px' />
								<col width='160px' />
								<col width='160px' />
								<col width='80px' />
								<col width='80px' />
								<col width='80px' />
								<col width='3%' />
								<col width='3%' />
								<col width='3%' />
								<col width='3%' />
								<col width='3%' />
								<col width='3%' />
								<col width='3%' />
								<col width='3%' />
								<col width='3%' />
								<col width='3%' />
								<col width='3%' />
								<col width='3%' />
								<col width='3%' />
								<col width='3%' />
								<col width='3%' />
							</colgroup>
							<tr>
								<!-- <th scope="col"><input name="chkall" id="chkall" type="checkbox" value="" onclick="javascript:doChkall();" /></th> -->
								<th scope="col">NO</th>
								<th scope="col">주문일자</th>
								<th scope="col">결제일자</th>
								<th scope="col">수출일자</th>
								<th scope="col">주문번호</th>
								<th scope="col">입고날짜</th>
								<th scope="col">품목</th>
								<th scope="col">옵션값</th>
								<th scope="col">옵션필드</th>
								<th scope="col">수량</th>
								<th scope="col">단가</th>
								<th scope="col">택배비</th>
								<th scope="col">할인금액</th>
								<th scope="col">상품구매가</th>
								<th scope="col">결제금액</th>
								<th scope="col">인보이스단가</th>
								<th scope="col">구매가격(VND)</th>
								<th scope="col">운송비</th>
								<th scope="col">선금</th>
								<th scope="col">잔금</th>
								<th scope="col">합계</th>
								<th scope="col">받는사람</th>
								<th scope="col">무게</th>
								<th scope="col">CJ측정무게</th>
								<th scope="col">CJ운송비(won)</th>
								<th scope="col">H,B/LNO</th>
								<th scope="col">수출신고번호</th>
								<th scope="col">비고</th>
							</tr>
<?
	$str = "";
	$l_orderid = "";
	$l_old_orderid = "";
	$rowspan = 1;
	$sumprice_vnd_sum		= 0;
	$w_price_p_vnd_sum		= 0;
	$depositfee_vnd_sum		= 0;
	$b_fee_vnd_sum			= 0;
	$bRow = false;
	if($nTotalCnt > 0){
		foreach($rsList as $i=>$ds){
			//$PageS = $nTotalCnt - $i - (($nowPage - 1) * $nListCnt);
			$PageS = $nTotalCnt - $i;

			// $ds['REG_DT_STR'] = str_replace(' ', '<br>', $ds['REG_DT']);

			if ($l_orderid != $ds["ORDERID"]){
				if($l_orderid == ""){
					$l_old_orderid = 'r_'.$ds["ORDERID"];
				} else {
					$l_old_orderid = 'r_'.$l_orderid;
				}
				$l_orderid = $ds["ORDERID"];
				$l_new_orderid = 'r_'.$ds["ORDERID"];
				$bRow = true;
				if($rowspan >= 1) {
					$b_fee_vnd_sum = floor(($b_fee_vnd_sum + 999) / 1000) * 1000;
					$totalprice_sum = ($depositfee_vnd_sum + $b_fee_vnd_sum) - $w_price_p_vnd_sum;
					$str = str_replace($l_old_orderid.'sumf1', number_format($sumprice_vnd_sum), $str);
					$str = str_replace($l_old_orderid.'sumf2', number_format($w_price_p_vnd_sum), $str);
					$str = str_replace($l_old_orderid.'sumf3', number_format($depositfee_vnd_sum), $str);
					$str = str_replace($l_old_orderid.'sumf4', number_format($b_fee_vnd_sum), $str);
					$str = str_replace($l_old_orderid.'sumf5', number_format($totalprice_sum), $str);
					$str = str_replace($l_old_orderid, $rowspan, $str);
				}
				$rowspan = 1;
			} else {
				$bRow = false;
				$rowspan++;
			}


			if($bRow){
				$ds['REG_DT_STR']		= substr($ds['REG_DT'], 0, 10);
				$ds['DEPOSIT_DT_STR']	= substr($ds['DEPOSIT_DT'], 0, 10);
				$ds['GOODSSC_DT_STR']	= substr($ds['GOODSSC_DT'], 0, 10);
				$ds['WEARSC_DT_STR']	= substr($ds['WEARSC_DT'], 0, 10);

				$sumprice_vnd_sum		= $ds['SUMPRICE_VND'];
				$w_price_p_vnd_sum		= $ds['W_PRICE_P_VND'];
				$depositfee_vnd_sum		= $ds['DEPOSITFEE_VND'];
				$b_fee_vnd_sum			= $ds['W_PRICE_P_VND']+$ds['B_FEE'];
				//$totalprice_sum		= ($ds['DEPOSITFEE_VND'] + $ds['B_FEE']) - $ds['W_PRICE_P_VND'];
			} else {
				$ds['REG_DT_STR']		= "";
				$ds['DEPOSIT_DT_STR']	= "";
				$ds['GOODSSC_DT_STR']	= "";
				$ds['WEARSC_DT_STR']	= "";
				$ds['ORDERID']			= "";
				$ds["NAME"]				= "";
				$ds["W_PRICE_P_VND"]	= "";
				$ds["WEIGHT"]	= "";

				$sumprice_vnd_sum		+= $ds['SUMPRICE_VND'];
				$w_price_p_vnd_sum		+= $ds['W_PRICE_P_VND'];
				$depositfee_vnd_sum		+= $ds['DEPOSITFEE_VND'];
				$b_fee_vnd_sum			+= $ds['B_FEE'];
				$totalprice_sum			+= $ds['DEPOSITFEE_VND'] + $ds['B_FEE'];
			}
			/*
					<td rowspan=$l_old_orderid>".number_format($ds['SUMPRICE_VND'])."</td>
					<td rowspan=$l_old_orderid>".number_format($ds['W_PRICE_P_VND'])."</td>
					<td rowspan=$l_old_orderid>".number_format($ds['DEPOSITFEE_VND'])."</td>
					<td rowspan=$l_old_orderid>".number_format($ds['B_FEE'])."</td>
					<td rowspan=$l_old_orderid>".number_format($ds['TOTALPRICE'])."</td>
			*/

			if($bRow){
				$strRowspan = "
					<td rowspan=$l_new_orderid>".$ds['REG_DT_STR']."</td>
					<td rowspan=$l_new_orderid>".$ds['DEPOSIT_DT_STR']."</td>
					<td rowspan=$l_new_orderid>".$ds['GOODSSC_DT_STR']."</td>
					<td rowspan=$l_new_orderid>".$ds['ORDERID']."</td>
					<td rowspan=$l_new_orderid>".$ds['WEARSC_DT_STR']."</td>
				";
				$strRowspan2 = "
					<td rowspan=$l_new_orderid>".$l_new_orderid."sumf1</td>
					<td rowspan=$l_new_orderid>".$l_new_orderid."sumf2</td>
					<td rowspan=$l_new_orderid>".$l_new_orderid."sumf3</td>
					<td rowspan=$l_new_orderid>".$l_new_orderid."sumf4</td>
					<td rowspan=$l_new_orderid>".$l_new_orderid."sumf5</td>
					<td rowspan=$l_new_orderid>".$ds['NAME']."</td>
					<td rowspan=$l_new_orderid>".$ds['WEIGHT']."</td>
					<td rowspan=$l_new_orderid>".$ds['CJWEIGHT']."</td>
					<td rowspan=$l_new_orderid>".number_format($ds['CJPRICE'])."</td>
					<td rowspan=$l_new_orderid>".$ds['HBLNO']."</td>
					<td rowspan=$l_new_orderid></td>
					<td rowspan=$l_new_orderid>".$ds['MEMO']."</td>
				";
				$strRowspan3 = "

				";
			} else {
				$strRowspan = "";
				$strRowspan2 = "";
			}


			$str .= "
				<tr>
					<td>".$PageS."</td>
					".$strRowspan."
					<td>".$ds['PRODUCTNAME']."</td>
					<td>".$ds['OPTVALUE']."</td>
					<td>".$ds['OPTFIELD']."</td>
					<td>".$ds['QTY']."</td>
					<td>".number_format($ds['PRICE'])."</td>
					<td>".number_format($ds['P_DELIVERYFEE'])."</td>
					<td>".number_format($ds['P_DISCOUNT'])."</td>
					<td>".number_format($ds['PRODUCTSUM'])."</td>
					<td>".number_format($ds['P_PRICESUM'])."</td>
					<td>".number_format($ds['INVOICE_PRICE'])."</td>
					".$strRowspan2."
				</tr>
			";
		}

		$b_fee_vnd_sum = floor(($b_fee_vnd_sum + 999) / 1000) * 1000;
		$totalprice_sum = ($depositfee_vnd_sum + $b_fee_vnd_sum) - $w_price_p_vnd_sum;
		$str = str_replace($l_new_orderid.'sumf1', number_format($sumprice_vnd_sum), $str);
		$str = str_replace($l_new_orderid.'sumf2', number_format($w_price_p_vnd_sum), $str);
		$str = str_replace($l_new_orderid.'sumf3', number_format($depositfee_vnd_sum), $str);
		$str = str_replace($l_new_orderid.'sumf4', number_format($b_fee_vnd_sum), $str);
		$str = str_replace($l_new_orderid.'sumf5', number_format($totalprice_sum), $str);
		$str = str_replace($l_new_orderid, $rowspan, $str);
	}
	else{
?>
							<tr>
								<td colspan="28" height="200">검색 결과가 없습니다</td>
							</tr>
<?
	}


	echo $str;
?>
						</table>
					</div>		<!-- scroll_sample -->
				</div>		<!-- scroll_sample -->

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