<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSession($url = '/_admin/');

	$_MENU1 = '5';		// 상단메뉴
	$_MENU2 = '2';		// 왼쪽메뉴

	$_NAVITITLE = "제품관리 > 제품관리";	

	// ===================================================
	// get parameter
	// ===================================================
	$product_seq 		= $cFnc->getReq('product_seq', '');

	$cal_1 				= $cFnc->getReq('cal_1', date('Y-m-d', strtotime('-3 month')));
	$cal_2 				= $cFnc->getReq('cal_2', date('Y-m-d'));
	$manufacturename  	= $cFnc->getReq('manufacturename', '');
	$order_cont 		= $cFnc->getReq('order_cont', 'MANUFACTURE_SEQ');
	$order_asc 			= $cFnc->getReq('order_asc', 'DESC');
	$search_list1 		= $cFnc->getReq('search_list1', '');

	$nPageCnt = $cFnc->getReq('nPageCnt', 10);		// 블럭당 페이지 수
	$nListCnt = $cFnc->getReq('nListCnt', 10);		// 페이지당 리스트 수
	$nowPage = $cFnc->getReq('nowPage', 1);			// 현재 페이지

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
	$gStr = $cFnc->GetStr( $gStr, "pname", $pname );
	$gStr = $cFnc->GetStr( $gStr, "use_yn", $use_yn );
	$gStr = $cFnc->GetStr( $gStr, "order_cont", $order_cont );
	$gStr = $cFnc->GetStr( $gStr, "order_asc", $order_asc );
	$gStr = $cFnc->GetStr( $gStr, "search_list1", $search_list1 );

	$gStr2 = "";
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_1", $cal_1 );
	$gStr2 = $cFnc->GetStr( $gStr2, "cal_2", $cal_2 );
	$gStr2 = $cFnc->GetStr( $gStr2, "pname", $pname );
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
	array_push($arParam, $product_seq);
	$qry = "SELECT A.*
			, ( SELECT MANUFACTURENAME FROM TB_MANUFACTURE TB_M WHERE TB_M.MANUFACTURE_SEQ = A.MANUFACTURE_SEQ) AS MANUFACTURENAME
			, ( SELECT CATEGORY_NM FROM TB_CATEGORY TB_C WHERE TB_C.CATEGORY_SEQ = A.CATEGORY_SEQ) AS CATEGORY_NM	
			FROM TB_PRODUCT A
			WHERE A.PRODUCT_SEQ = ? ";
	$rslt = $cPdo->execQuery('data', $qry, $arParam);
	$ds = $rslt['data'];


	$qry = "SELECT * FROM TB_PRODUCT_STOCK WHERE PRODUCT_SEQ = ?";
	$rslt = $cPdo->execQuery('list', $qry, $arParam);
	$dsStock = $rslt['data'];

	include $_SERVER["DOCUMENT_ROOT"]. "/_admin/h.php";
?>
<script>
	// 수정
	function goUpdate(){
		location.href = "productInput.php?product_seq=<?=$product_seq?>&<?=$gStr2?>";
	}
	
	// 목록
	function goList(){
		location.href = "productList.php?<?=$gStr2?>";
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
							<th scope="row">제품코드</th>
							<td class='lf'><?=$ds['PRODUCTID']?></td>
						</tr>
						<tr>
							<th scope="row">제품명</th>
							<td class='lf'><?=$ds['PNAME']?></td>
						</tr>
						<tr>
							<th scope="row">인보이스명</th>
							<td class='lf'><?=$ds['PINVOICENAME']?></td>
						</tr>
						<tr>
							<th scope="row">유통사</th>
							<td class='lf'><?=$ds['MANUFACTURENAME']?></td>
						</tr>
						<tr>
							<th scope="row">카테고리</th>
							<td class='lf'><?=$ds['CATEGORY_NM']?></td>
						</tr>
						<tr>
							<th scope="row">상태</th>
							<td class='lf'><?=CODE_PRODUCT_STATUS($ds['P_STATUS'])?></td>
						</tr>
						<tr>
							<th scope="row">재고</th>
							<td class='lf con'>
								<?=CODE_PRODUCT_STOCK($ds['STOCK_YN'])?>
							<?
							if($ds['STOCK_YN'] == "Y"){

							?>
								<div class="content skip" id="divStock" name='divStock'>
									<table border=0 width="100%" cellpadding="0" cellpadding="0" class="adtb_02">
										<colgroup>
											<col width='100%' />
										</colgroup>											

<?
			foreach($dsStock as $k=>$dsStock){
?>
										<tr>
											<td class='lf'>창고설정 : <?=$dsStock['STORAGE_NM']?> 수량 : <?=$dsStock['QTY']?></td>
										</tr>
<?			
			}

?>
									</table>
								</div>
							<?
							}
							?>
							</td>
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