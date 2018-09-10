<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSession($url = '/_admin/');
	
	$_MENU1 = '5';		// 상단메뉴
	$_MENU2 = '2';		// 왼쪽메뉴

	$_NAVITITLE = "제품관리 > 제품관리";	

	$arr_date = array();
	array_push($arr_date, date('Y-m-d'));
	array_push($arr_date, date('Y-m-d', strtotime('-7 day')) );
	array_push($arr_date, date('Y-m-d', strtotime('-15 day')) );
	array_push($arr_date, date('Y-m-d', strtotime('-1 month')) );
	array_push($arr_date, date('Y-m-d', strtotime('-3 month')) );
	
	//=====================================================
	//== 도움말 - Get Parameters
	//=====================================================
	$cal_1 		= $cFnc->getReq('cal_1', date('Y-m-d', strtotime('-3 month')));
	$cal_2 		= $cFnc->getReq('cal_2', date('Y-m-d'));
	$pname  	= $cFnc->getReq('pname', '');
	$status  	= $cFnc->getReq('status', '');
	$order_cont = $cFnc->getReq('order_cont', 'A.PRODUCT_SEQ');
	$order_asc 	= $cFnc->getReq('order_asc', 'DESC');
	$search_list1 = $cFnc->getReq('search_list1', '');
	
	//=====================================================
	//== 도움말 - Set Variables
	//=====================================================
	$cPdo = new cPdo($ARR_DB_INFO, true);
	
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
	
	// =====================================================
	// Set Parameters (Get Types)
	// =====================================================
	$gStr = "";
	$gStr = $cFnc->GetStr( $gStr, "cal_1", $cal_1 );
	$gStr = $cFnc->GetStr( $gStr, "cal_2", $cal_2 );
	$gStr = $cFnc->GetStr( $gStr, "pname", $pname );
	$gStr = $cFnc->GetStr( $gStr, "status", $status );
	$gStr = $cFnc->GetStr( $gStr, "order_cont", $order_cont );
	$gStr = $cFnc->GetStr( $gStr, "order_asc", $order_asc );
	$gStr = $cFnc->GetStr( $gStr, "search_list1", $search_list1 );

	$gStr2 = "";
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_1", $cal_1 );
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_2", $cal_2 );
	$gStr2 = $cFnc->GetStr( $gStr2, "pname", $pname );
	$gStr2 = $cFnc->GetStr( $gStr2, "status", $status );
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
	$qryWhere = "WHERE A.REG_DT BETWEEN ? AND ?";
	array_push($arParam, $cal_1 .' 00:00:00');
	array_push($arParam, $cal_2 .' 23:59:59');

	if($pname != ''){
		$qryWhere .= " AND A.PNAME like ?";
		array_push($arParam, '%'. $pname .'%');		
	}
	if($status != ''){
		$qryWhere .= " AND A.P_STATUS = ?";
		array_push($arParam, $status);
	}
	
	$qry = "
		SELECT A.PRODUCT_SEQ, A.MANUFACTURE_SEQ, A.PRODUCTID, A.PNAME, A.REG_DT, A.MOD_DT
		, A.MANUFACTURE, A.STOCK_YN, A.STORAGE_SEQ, A.LINKURL, A.P_STATUS, A.CATEGORY_SEQ, A.PINVOICENAME
		, IFNULL(SUM(B.QTY), 0) AS QTY
		, DATE_FORMAT(A.REG_DT, '%Y-%m-%d') AS REG_DT_STR
		, DATE_FORMAT(A.MOD_DT, '%Y-%m-%d') AS REG_DT_STR						
		, (SELECT MANUFACTURENAME FROM TB_MANUFACTURE TB_M WHERE TB_M.MANUFACTURE_SEQ = A.MANUFACTURE_SEQ) AS MANUFACTURENAME
		, (SELECT CATEGORY_NM FROM TB_CATEGORY TB_C WHERE TB_C.CATEGORY_SEQ = A.CATEGORY_SEQ) AS CATEGORY_NM
		, (SELECT STORAGE_NM FROM TB_STORAGE TB_S WHERE TB_S.STORAGE_SEQ = B.STORAGE_SEQ) AS STORAGE_NM
		FROM TB_PRODUCT A
		LEFT OUTER JOIN TB_PRODUCT_STOCK B ON B.PRODUCT_SEQ = A.`PRODUCT_SEQ`
		". $qryWhere ."
		GROUP BY A.PRODUCT_SEQ, A.MANUFACTURE_SEQ, A.PRODUCTID, A.PNAME, A.REG_DT, A.MOD_DT
		, A.MANUFACTURE, A.STOCK_YN, A.STORAGE_SEQ, A.LINKURL, A.P_STATUS, A.CATEGORY_SEQ, A.PINVOICENAME
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


	// 수정
	function goUpdate(product_seq){
		location.href = "productInput.php?product_seq=" + product_seq + "&<?=$gStr2?>";
	}

	// 입력
	function goInput(){
		location.href = "productInput.php?<?=$gStr2?>";
	}
	
	// 삭제
	function doDelete(){
		if($('input[name="chkitem"]').is(':checked') == ''){alert("체크박스를 체크해 주세요"); return false;}

		if(confirm("선택한 게시물을 삭제하시겠습니까?")){
			var arrChk = new Array();
			$('input[name="chkitem"]:checked').each(function(){
				arrChk.push($(this).val());
			});

			$('#pageaction').val("DELETE");
			$('#seq').val(arrChk);

			$.ajax({
				type:"POST",
				dataType : 'json',
				url: "productProc.php",
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
	

	// 삭제
	function goDelete(product_seq){
		if(confirm("선택한 게시물을 삭제하시겠습니까?")){
			$('#pageaction').val("DELETE");			
			$('#product_seq').val(product_seq);

			$.ajax({
				type:"POST",
				dataType : 'json',
				url: "productProc.php",
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
				}
			});
		}
	}

	// 상세
	function goDetail(product_seq){
		location.href = "productView.php?product_seq="+ product_seq +"&<?=$gStr2?>";
	}
	

	// 인쇄
	function doPrint(){
		AREA_PRINT(".content");
	}
	
	// 엑셀저장
	function doExcel(){
		location.href = "adminExcel.php?<?=$gStr?>";
	}
	
	function set_date(v1, v2){
		$('#cal_1').val(v1);
		$('#cal_2').val(v2);
	}

</script>

	<form method="post" name="frmProc" id="frmProc" action="">
		<input type="hidden" name="pageaction" id="pageaction" value="">
		<input type="hidden" name="product_seq" id="product_seq" value="">
		<input type="hidden" name="USE_YN" id="USE_YN" value="">
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
						<th scope="row">제품명</th>
						<td class='lf'>
							<input name="pname" id="pname" type="text" class='w200' value="<?=$pname?>" onkeypress="javascript:doEnter();" />
						</td>
						<th scope="row">제품상태</th>
						<td class='lf'>
							<select name="status" id="status">
								<option value="">전체</option>
								<option value="O" <?=$cFnc->CodeString($status, 'O', 'selected', '')?>>정상</option>
								<option value="R" <?=$cFnc->CodeString($status, 'R', 'selected', '')?>>품절</option>
								<option value="S" <?=$cFnc->CodeString($status, 'S', 'selected', '')?>>판매중지</option>
							</select>						
						</td>
					</tr>					
				</table>
				</form>

				<div class='mt10'>
					<p class='fr'>
						<input type="button" name="btn_search" id="btn_search" value='조회' class='btn_01 w80' onclick="javascript:doSearch();" />
					</p>					
				</div>

				<div class="both"></div>

				<div class='mt10'>				
					<input type='button' value='인쇄' class='btn_02 w60' onclick="javascript:doPrint();"/>
					<!-- <input type='button' value='액셀저장' class='btn_02 w80' onclick="javascript:doExcel();"/>					 -->
				</div>

				<table width="100%" border="0" cellspacing="0" cellpadding="0"	class='adtb_01 mt10'>
					<colgroup>
						<col width='5%' />
						<col width='5%' />
						<col width='15%' />
					</colgroup>
					<tr>
						<th scope="col"><input name="chkall" id="chkall" type="checkbox" value="" onclick="javascript:doChkall();" /></th>
						<th scope="col">NO</th>
						<th scope="col">제품코드</th>
						<th scope="col">제품명</th>
						<th scope="col">제조사</th>						
						<th scope="col">카테고리</th>
						<th scope="col">제품상태</th>
						<th scope="col">재고</th>
						<th scope="col">수량</th>
						<th scope="col">등록일</th>
						<th scope="col">ACTION </th>						
					</tr>
<?
	if($nTotalCnt > 0){
		foreach($rsList as $i=>$ds){
			$PageS = $nTotalCnt - $i - (($nowPage - 1) * $nListCnt);
			$stock_str = '';
			
			if($ds['STORAGE_NM'] != ''){
				$stock_str = "(". $ds['STORAGE_NM'] .":". $ds['QTY'] .")";
			}
?>
					<tr>
						<td><input name="chkitem" id="chkitem_<?=$ds['PRODUCT_SEQ']?>" type="checkbox" value="<?=$ds['PRODUCT_SEQ']?>" /></td>
						<td><?=$PageS?></td>												
						<td><a href="javascript:goDetail('<?=$ds['PRODUCT_SEQ']?>');"><?=$ds['PRODUCTID']?></a></td>
						<td><?=$ds['PNAME']?></td>						
						<td><?=$ds['MANUFACTURENAME']?></td>
						<td><?=$ds['CATEGORY_NM']?></td>						
						<td><?=CODE_PRODUCT_STATUS($ds['P_STATUS'])?></td>
						<td><?=CODE_PRODUCT_STOCK($ds['STOCK_YN'])?><br><?=$stock_str?></td>
						<td><?=$ds['QTY']?></td>
						<td><?=$ds['REG_DT_STR']?></td>
						<td>							
							<input type='button' value='수정' class='btn_02 w40' onclick="javascript:goUpdate('<?=$ds['PRODUCT_SEQ']?>');"/>
							<input type='button' value='삭제' class='btn_02 w40' onclick="javascript:goDelete('<?=$ds['PRODUCT_SEQ']?>');"/>
						</td>						
					</tr>
<?
		}
	}
	else{
?>
					<tr>
						<td colspan="11" height="200">검색 결과가 없습니다</td>
					</tr>
<?
	}
?>
				</table>

				<div class='mt10'>
					<p class='fr'>
						<input type='button' value='입력' class='btn_02 w60' onclick="javascript:goInput();"/>
					</p>
				</div>

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