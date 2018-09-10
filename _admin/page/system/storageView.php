<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSession($url = '/_admin/');

	$_MENU1 = '1';		// 상단메뉴
	$_MENU2 = '7';		// 왼쪽메뉴

	$_NAVITITLE = "시스템관리 > 재고창고관리";

	// ===================================================
	// get parameter
	// ===================================================
	$storage_seq = $cFnc->getReq('storage_seq', '');

	$cal_1 = $cFnc->getReq('cal_1', date('Y-m-d', strtotime('-3 month')));
	$cal_2 = $cFnc->getReq('cal_2', date('Y-m-d'));
	$storage_nm  = $cFnc->getReq('storage_nm', '');
	$manager_nm  = $cFnc->getReq('manager_nm', '');
	$use_yn = $cFnc->getReq('use_yn', '');
	$order_cont = $cFnc->getReq('order_cont', 'STORAGE_SEQ');
	$order_asc = $cFnc->getReq('order_asc', 'DESC');
	$search_list1 = $cFnc->getReq('search_list1', '');
	// =====================================================
	// Set Variables
	// =====================================================
	$cPdo = new cPdo($ARR_DB_INFO);
	
	
	// =====================================================
	// Set Parameters (Get Types)
	// =====================================================
	$gStr = "";
	$gStr = $cFnc->GetStr( $gStr, "cal_1", $cal_1 );
	$gStr = $cFnc->GetStr( $gStr, "cal_2", $cal_2 );
	$gStr = $cFnc->GetStr( $gStr, "storage_nm", $storage_nm );
	$gStr = $cFnc->GetStr( $gStr, "manager_nm", $manager_nm );
	$gStr = $cFnc->GetStr( $gStr, "use_yn", $use_yn );
	$gStr = $cFnc->GetStr( $gStr, "order_cont", $order_cont );
	$gStr = $cFnc->GetStr( $gStr, "order_asc", $order_asc );
	$gStr = $cFnc->GetStr( $gStr, "search_list1", $search_list1 );

	$gStr2 = "";
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_1", $cal_1 );
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_2", $cal_2 );
	$gStr2 = $cFnc->GetStr( $gStr2, "storage_nm", $storage_nm );
	$gStr2 = $cFnc->GetStr( $gStr2, "manager_nm", $manager_nm );
	$gStr2 = $cFnc->GetStr( $gStr2, "use_yn", $use_yn );
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
	array_push($arParam, $storage_seq);
	$qry = "SELECT * FROM TB_STORAGE WHERE STORAGE_SEQ = ?";
	$rslt = $cPdo->execQuery('data', $qry, $arParam);
	$ds = $rslt['data'];


	include $_SERVER["DOCUMENT_ROOT"]. "/_admin/h.php";
?>
<script>
	// 수정
	function goUpdate(){
		location.href = "storageInput.php?storage_seq=<?=$storage_seq?>&<?=$gStr2?>";
	}
	
	// 목록
	function goList(){
		location.href = "storageList.php?<?=$gStr2?>";
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
							<th scope="row">분류</th>
							<td class='lf'><?=$ds['CATEGORY']?></td>
						</tr>
						<tr>
							<th scope="row">창고코드</th>
							<td class='lf'><?=$ds['CODE_SEQ']?></td>
						</tr>						
						<tr>
							<th scope="row">창고명</th>
							<td class='lf'><?=$ds['STORAGE_NM']?></td>
						</tr>						
						<tr>
							<th scope="row">주소</th>
							<td class='lf'>
							<?
								$addr_str = '['.$ds['POST_NO'].'] '.$ds['ADDR'].' '.$ds['ADDR_DETAIL'];
								echo $addr_str;
							?>								
							</td>
						</tr>						
						<tr>
							<th scope="row">담당자명</th>
							<td class='lf'><?=$ds['MANAGER_NM']?></td>
						</tr>						
						<tr>
							<th scope="row">휴대폰</th>
							<td class='lf'><?=$ds['TEL']?></td>
						</tr>												
						<tr>
							<th scope="row">이메일</th>
							<td class='lf'><?=$ds['EMAIL']?></td>
						</tr>																		
						<tr>
							<th scope="row">사용여부</th>
							<td class='lf'><?=CODE_USE_YN($ds['USE_YN'])?></td>
						</tr>
						<tr>
							<th scope="row">등록일</th>
							<td class='lf'><?=$ds['REG_DT']?></td>
						</tr>
						<tr>
							<th scope="row">수정일</th>
							<td class='lf'><?=$ds['MOD_DT']?></td>
						</tr>
												
					</table>

					<div class='fr mt10'>
						<span class='button w60 h_22'><a href='javascript:;' class='dark_blue' onclick="javascript:goUpdate();">수정</a></span>
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