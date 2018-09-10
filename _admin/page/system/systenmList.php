<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSession($url = '/_admin/');
	
	$_MENU1 = '1';		// 상단메뉴
	$_MENU2 = '1';		// 왼쪽메뉴
	
	//=====================================================
	//== 도움말 - Get Parameters
	//=====================================================
	$cal_1 = $cFnc->getReq('cal_1', date('Y-m-d', strtotime('-3 month')));
	$cal_2 = $cFnc->getReq('cal_2', date('Y-m-d'));
	$search_type = $cFnc->getReq('search_type', '');
	$search_text = $cFnc->getReq('search_text', '');
	$order_cont = $cFnc->getReq('order_cont', 'SEQ');
	$order_asc = $cFnc->getReq('order_asc', 'DESC');
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
	$gStr = $cFnc->GetStr( $gStr, "search_type", $search_type );
	$gStr = $cFnc->GetStr( $gStr, "search_text", $search_text );
	$gStr = $cFnc->GetStr( $gStr, "order_cont", $order_cont );
	$gStr = $cFnc->GetStr( $gStr, "order_asc", $order_asc );
	$gStr = $cFnc->GetStr( $gStr, "search_list1", $search_list1 );

	$gStr2 = "";
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_1", $cal_1 );
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_2", $cal_2 );
	$gStr2 = $cFnc->GetStr( $gStr2, "search_type", $search_type );
	$gStr2 = $cFnc->GetStr( $gStr2, "search_text", $search_text );
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
	$qryWhere = "WHERE 1 = 1";
	
	if($search_list1_val != ''){
		$qryWhere .= " AND ". $search_list1_type ." = ?";
		array_push($arParam, $search_list1_val);
	}
	
	if($search_type != '' && $search_text != ''){
		$qryWhere .= " AND ". $search_type ." LIKE ?";
		array_push($arParam, '%'. $search_text .'%');
	}
	
	$qry = "
		SELECT SEQ, ADM_ID, ADM_PW, ADM_NM, SYSADMIN_YN, USE_YN, REG_DT, MOD_DT
		, CASE WHEN SYSADMIN_YN = 'Y' THEN '시스템관리자' ELSE '일반관리자' END AS SYSADMIN_STR
		FROM TMP_ADM_BACK
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

	// 입력
	function goInput(){
		location.href = "adminInput.php?<?=$gStr2?>";
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
				url: "adminProc.php",
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
		location.href = "adminView.php?seq="+ seq +"&<?=$gStr2?>";
	}
	
	// 파일업로드
	function doUploadFile(){
		$('#FILE').trigger('click');
	}
	
	// 파일업로드 파일선택 후
	function onFileUpload(){
		$('#FILE_TEXT').val($('#FILE').val());
	}
</script>

	<form method="post" name="frmProc" id="frmProc" action="">
		<input type="hidden" name="pageaction" id="pageaction" value="">
		<input type="hidden" name="seq" id="seq" value="">
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

				<h2>관리자</h2>

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
							<input type="button" value="오늘" class='btn_s_03 w60' onclick="javascript:OPEN_LAYER_POPUP('#popup1');">
							<input type="button" value="7일" class='btn_s_03 w60' onclick="javascript:OPEN_LAYER_POPUP('#popup1');">
							<input type="button" value="15일" class='btn_s_03 w60' onclick="javascript:OPEN_LAYER_POPUP('#popup1');">
							<input type="button" value="1개월" class='btn_s_03 w60' onclick="javascript:OPEN_LAYER_POPUP('#popup1');">
							<input type="button" value="3개월" class='btn_s_03 w60' onclick="javascript:OPEN_LAYER_POPUP('#popup1');">
						</td>
					</tr>					
					<tr>
						<th scope="row">시스템명</th>
						<td class='lf'>
							<input name="system_nm" id="system_nm" type="text" class='w200' value="<?=$search_text?>" onkeypress="javascript:doEnter();" />
						</td>
						<th scope="row">사용여부</th>
						<td class='lf'>
							<select name="use_yn" id="use_yn">
								<option value="">항목</option>
								<option value="Y" <?=$cFnc->CodeString($use_yn, 'Y', 'selected', '')?>>사용</option>
								<option value="N" <?=$cFnc->CodeString($use_yn, 'N', 'selected', '')?>>미사용</option>
							</select>						
						</td>
					</tr>
				</table>


				<div class='mt10'>
					<p class='fr'>
						<input type="button" name="btn_search" id="btn_search" value='조회' class='btn_01 w80' onclick="javascript:doSearch();" />
					</p>					
				</div>

				<div class="both"></div>


				<form method="get" name="frmSearch" id="frmSearch" action="<?=$_SERVER['PHP_SELF']?>">
				<input type="hidden" name="search_list1" id="search_list1" value="<?=$search_list1?>">
				<input type="hidden" name="order_cont" id="order_cont" value="<?=$order_cont?>">
				<input type="hidden" name="order_asc" id="order_asc" value="<?=$order_asc?>">
				<div class='box_blue'>
					<p class='fr'>
						<input name="cal_1" id="cal_1" type="text" class='w100' value='<?=$cal_1?>' readonly /> ~ <input name="cal_2" id="cal_2" type="text" class='w100' value='<?=$cal_2?>' readonly />
						<select name="search_type" id="search_type">
							<option value="">항목</option>
							<option value="TITLE" <?=$cFnc->CodeString($search_type, 'TITLE', 'selected', '')?>>아이디</option>
							<option value="REG_ID" <?=$cFnc->CodeString($search_type, 'REG_ID', 'selected', '')?>>이름</option>
						</select>
						<input name="search_text" id="search_text" type="text" class='w100' value="<?=$search_text?>" onkeypress="javascript:doEnter();" />
						<input type="button" name="btn_search" id="btn_search" value='조회' class='btn_01 w80' onclick="javascript:doSearch();" />
					</p>
				</div>
				</form>


				<div class='mt20'>
					<input type='button' value='입력' class='btn_02 w60' onclick="javascript:goInput();"/>
					<input type='button' value='삭제' class='btn_02 w60' onclick="javascript:doDelete();"/>
					<p class='stat_mgn'>
						<span class='ml5'>사용 <em class='current_num ml5'><?=$dsStatCnt['USE_Y']?></em></span>
						<span class='ml10'>미사용 <em class='current_num ml5'><?=$dsStatCnt['USE_N']?></em></span>
					</p>
				</div>

				<table width="100%" border="0" cellspacing="0" cellpadding="0"	class='adtb_01 mt10'>
					<colgroup>
						<col width='5%' />
						<col width='5%' />
						<col width='20%' />
						<col width='20%' />
						<col width='20%' />
						<col width='10%' />
						<col width='20%' />
					</colgroup>
					<tr>
						<th scope="col"><input name="chkall" id="chkall" type="checkbox" value="" onclick="javascript:doChkall();" /></th>
						<th scope="col">NO</th>
						<th scope="col">아이디 <a href="javascript:doOrder('ADM_ID');"><?=$cFnc->CodeString($order_cont, 'ADM_ID', $arrow_{$order_cont}, '▼')?></a></th>
						<th scope="col">이름 <a href="javascript:doOrder('ADM_NM');"><?=$cFnc->CodeString($order_cont, 'ADM_NM', $arrow_{$order_cont}, '▼')?></a></th>
						<th scope="col">관리자유형</th>
						<th scope="col">
						상태 <a href='javascript:;' class='btn_arrow item_use'></a>
							<div class='sel_use th_sel'>
								<ul>
									<li><a href="javascript:doSearch('USE_YN', '');">전체</a></li>
									<li><a href="javascript:doSearch('USE_YN', 'Y');">사용</a></li>
									<li><a href="javascript:doSearch('USE_YN', 'N');">중지</a></li>
								</ul>
							</div>
						</th>
						<th scope="col">등록일 <a href="javascript:doOrder('SEQ');"><?=$cFnc->CodeString($order_cont, 'SEQ', $arrow_{$order_cont}, '▼')?></a></th>
					</tr>
<?
	if($nTotalCnt > 0){
		foreach($rsList as $i=>$ds){
			$PageS = $nTotalCnt - $i - (($nowPage - 1) * $nListCnt);
			
			$ds['REG_DT_STR'] = substr($ds['REG_DT'], 0, 10);
?>
					<tr>
						<td><input name="chkitem" id="chkitem_<?=$ds['SEQ']?>" type="checkbox" value="<?=$ds['SEQ']?>" /></td>
						<td><?=$PageS?></td>
						<td class='bg_01'><a href="javascript:goDetail('<?=$ds['SEQ']?>');"><?=$ds['ADM_ID']?></a></td>
						<td><?=$ds['ADM_NM']?></td>
						<td><?=$ds['SYSADMIN_STR']?></td>
						<td><?=CODE_USE_YN($ds['USE_YN'])?></td>
						<td><?=$ds['REG_DT_STR']?></td>
					</tr>
<?
		}
	}
	else{
?>
					<tr>
						<td colspan="7" height="200">검색 결과가 없습니다</td>
					</tr>
<?
	}
?>
				</table>

				<!-- paging -->
				<?=$cFnc->getAdmPaging( $nPageCnt, $nowPage, $nTotalPage, $gStr )?>
				<!-- //paging -->

				<table border="0" width="100%" cellspacing="0" cellpadding="0" class='adtb_02 mt20 '>
					<tr>
						<td>
							<input type="button" value="팝업열기" class='btn_s w60' onclick="javascript:OPEN_LAYER_POPUP('#popup1');">
							<input type="button" value="팝업열기" class='btn_s_02 w70' onclick="javascript:OPEN_LAYER_POPUP('#popup1');">
							<input type="button" value="팝업열기" class='btn_s_03 w80' onclick="javascript:OPEN_LAYER_POPUP('#popup1');">
							<input type="button" value="팝업열기" class='btn_01 w60' onclick="javascript:OPEN_LAYER_POPUP('#popup1');">
							<input type="button" value="팝업열기" class='btn_02 w70' onclick="javascript:OPEN_LAYER_POPUP('#popup1');">
							<input type="button" value="팝업열기" class='btn_03 w80' onclick="javascript:OPEN_LAYER_POPUP('#popup1');">
							<input type="button" value="팝업열기" class='btn_print w90' onclick="javascript:OPEN_LAYER_POPUP('#popup1');">
							<input type="button" value="팝업열기" class='btn_excel w90' onclick="javascript:OPEN_LAYER_POPUP('#popup1');">
							<input type="button" value="팝업열기" class='btn_excel_up w90' onclick="javascript:OPEN_LAYER_POPUP('#popup1');">
							<input type="button" value="팝업열기" class='btn_down w90' onclick="javascript:OPEN_LAYER_POPUP('#popup1');">
							<input type="button" value="팝업열기" class='btn_b01 w80' onclick="javascript:OPEN_LAYER_POPUP('#popup1');">
							<input type="button" value="팝업열기" class='btn_b02 w80' onclick="javascript:OPEN_LAYER_POPUP('#popup1');">
							<input type="button" value="팝업열기" class='btnp_01 w80' onclick="javascript:OPEN_LAYER_POPUP('#popup1');">
							<input type="button" value="팝업열기" class='btnp_02 w80' onclick="javascript:OPEN_LAYER_POPUP('#popup1');">
						</td>
					</tr>
					<tr>
						<td class='lf file_bx'>
							<input name="FILE_TEXT" id="FILE_TEXT" type="text" class='w400' readonly />
							<label for='upload_text' onclick="javascript:doUploadFile();">파일 업로드</label>
							<input type='file' id='FILE' name='FILE' class='up_file' onchange="javascript:onFileUpload();">
						</td>
					</tr>
					<tr>
						<td class='lf con'><textarea name="CONTENT" id="CONTENT" cols="" rows="">텍스트에어리어 영역</textarea></td>
					</tr>
				</table>
				
			</div>
			<!--// content -->

		</div>
		<!--// 전체 100% CONTENTS	 -->

	</div>
	<!--//contain-->

	</div>
	<!--//wrap-->
	
	<!-- popup -->
	<div id="popup1" class="dialog-popup" style="width:500px;">
		<div id='pop'>
			<div class='title'>
				<h2>상세보기</h2>
				<a href="javascript:;" class='pop_close' onclick="javascript:CLOSE_LAYER_POPUP('#popup1');">닫기</a>
			</div>
			<table width="100%" border="0" cellspacing="0" cellpadding="0" class='tb_row_01'>
				<colgroup>
					<col width='40%'/>
					<col width='60%'/>			
				</colgroup>
				<tr>
					<th scope="row">NO</th>
					<td class='lf' id="DET_SEQ">1</td>       
				</tr>
				<tr>
					<th scope="row">업로드 시간</th>
					<td class='lf' id="DET_START_DT">2017-01-01 09:00:00</td>       
				</tr>
				<tr>
					<th scope="row">업로드 완료시간</th>
					<td class='lf' id="DET_END_DT">2017-01-01 09:00:00</td>       
				</tr>
				<tr>
					<th scope="row">업로드 파일</th>
					<td class='lf' id="DET_PARSING_FILE">SKT_20170220.xlsx</td>       
				</tr>
				<tr>
					<th scope="row">처리자</th>
					<td class='lf' id="DET_ADM_NM">홍두깨</td>
				</tr>
			</table>
				
			<table width="100%" border="0" cellspacing="0" cellpadding="0" class='tb_row_01 mt10'>
				<colgroup>
					<col width='40%'/>
					<col width='60%'/>			
				</colgroup>
				<tr>
					<th scope="row">업로드 타입</th>
					<td class='lf'>업로드 개수</td>       
				</tr>
				<tr>
					<th scope="row">요금제</th>
					<td class='lf'>3</td>       
				</tr>
				<tr>
					<th scope="row">공시지원금</th>
					<td class='lf'>17</td>       
				</tr>
				<tr>
					<th scope="row">선택약정</th>
					<td class='lf'>24</td>       
				</tr>
			</table>
			
			<div class='center mt20'>
				<input type='button' value='사용' class='btnp_01 w80' onclick="javascript:doProc('Y');" />
				<input type='button' value='중지' class='btnp_02 w80' onclick="javascript:doProc('N');" />
			</div>
		</div>
	</div>
	<!-- //popup -->

<?
	include $_SERVER["DOCUMENT_ROOT"]. "/_admin/f.php";
?>