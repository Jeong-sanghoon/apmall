<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSession($url = '/_admin/');

	$_MENU1 = '3';		// 상단메뉴
	$_MENU2 = '2';		// 왼쪽메뉴

	$_NAVITITLE = "신청관리 > 주문서생성";	

	//=====================================================
	//== 도움말 - Get Parameters
	//=====================================================
	$usermstid = $cFnc->getReq('usermstid', '');

	// =====================================================
	// Set Variables
	// =====================================================
	$cPdo = new cPdo($ARR_DB_INFO);
	
	$orderid = "OD". date('YmdHis') . strtoupper($cFnc->GenerateRanStr(4, 'number'));		// 주문번호생성
	//$ord_dt = date('Y-m-d H:i:s');			// 주문생성일 [신청서 없을때]

	// =====================================================
	// Set Parameters (Get Types)
	// =====================================================
	$gStr = "";
	$gStr = $cFnc->GetStr( $gStr, "cal_1", $cal_1 );

	// =====================================================
	// Start Tran
	// =====================================================
	if($usermstid != ''){
		/* 고객이 작성한 신청서 조회 */
		$arParam = array();
		array_push($arParam, $usermstid);
		$qry = "
			SELECT A.`USERMSTID`, A.`USERORDID`, A.REG_DT, A.`NAME`, A.`EMAIL`, A.`TEL`, A.`PRICESUM`, A.DEPOSITFEE, A.`DST_ADDR`, A.`MEMO`, A.`QTY`, A.USER_ID, A.USER_SEQ
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
	else{
		/* 임시저장데이터 있을때 */
		$arParam = array();
		array_push($arParam, $S_SEQ);
		$qry = "
			SELECT A.*, B.*, B.ROWID AS USERITEMID, B.PRODUCTNAME AS ITEM_NM
			FROM TB_ORDER_TEMP A
			LEFT OUTER JOIN TB_ORDER_ITEM_TEMP B ON B.ORDER_SEQ = A.ORDER_SEQ
			WHERE A.ADM_SEQ = ?
		";
		$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
		$result = $cPdo->execQuery('list', $qry, $arParam);
		if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
		$rs = $result['data'];
		
		//echo json_encode($rs);exit;
	}
	
	
	// 카테고리리스트
	$arParam = array();
	$qry = "
		SELECT CATEGORY_SEQ, CATEGORY_NM, USE_YN, ORDER_NO, REG_DT, MOD_DT, PARENT_SEQ, DEPTH, HSCODE_SEQ
		FROM TB_CATEGORY
		WHERE USE_YN = 'Y'
		ORDER BY `CATEGORY_NM`
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('list', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$rsCate = $result['data'];
	
	$htmlCate = "";
	foreach($rsCate as $iCate=>$dsCate){
		$htmlCate .= "<option value=\"". $dsCate['CATEGORY_SEQ'] ."\">". $dsCate['CATEGORY_NM']. "</option>";
	}
	
	
	// HSCODE리스트
	$arParam = array();
	$qry = "
		SELECT HSCODE_SEQ, HSCODE_NM, HSCODE_VALUE, USE_YN
		FROM TB_HSCODE
		WHERE USE_YN = 'Y'
		ORDER BY HSCODE_NM
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('list', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$rsHscode = $result['data'];
	
	$htmlHscode = "";
	foreach($rsHscode as $iHs=>$dsHs){
		$htmlHscode .= "<option value=\"". $dsHs['HSCODE_SEQ'] ."\">". $dsHs['HSCODE_NM']. "</option>";
	}
	
	
	// 영업담당자
	$arParam = array();
	$qry = "
		SELECT SALES_SEQ, SALESNAME, PER
		FROM TB_SALES
		WHERE USE_YN = 'Y'
		ORDER BY SALESNAME
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('list', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$rsSales = $result['data'];
	
	
	// 디파짓피 퍼센트
	$arParam = array();
	$qry = "
		SELECT DEPOSIT_SEQ, RATE
		FROM TB_DEPOSIT
		WHERE USE_YN = 'Y'
		ORDER BY DEPOSIT_SEQ DESC
		LIMIT 1
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('data', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$dsSales = $result['data'];
	$per = $dsSales['RATE'];
	
	
	include $_SERVER["DOCUMENT_ROOT"]. "/_admin/h.php";
?>

<script type="text/javascript" src="/js/jquery.form.js"></script>

<script>
	var usermstid = "<?=$usermstid?>";
	var arr_cate = <?=json_encode($rsCate)?>;
	var arr_hscode = <?=json_encode($rsHscode)?>;
	
	$('document').ready(function(){
		if(usermstid != ''){
			calSumArea();
		}
	});
	
	// 취소
	function doCancel(){
		window.close();
	}
	
	// 추가
	function addItem(linkurl, category_seq, productname, invoicename){
		var idx = 0;
		if($('.tr_item').length > 0){
			idx = $('.tr_item:last()').data('idx') + 1;
		}
		
		var str = "";
		str += '<tr class="tr_item" id="tr_item_'+ idx +'" data-idx="'+ idx +'">';
		str += '	<td scope="row" class="lf"><input type="text" class="w95p" name="linkurl[]" id="linkurl_'+ idx +'" value="'+ linkurl +'"></td>';
		str += '	<td scope="row" class="lf">';
		str += '		<select name="category_seq[]" id="category_seq_'+ idx +'" class="w95p" onchange="javascript:onChangeCategory(\''+ idx +'\');">';
		
		for(var i = 0; i < arr_cate.length; i++){
			var sel = '';
			if(arr_cate[i].CATEGORY_SEQ == category_seq) sel = 'selected';
			str += '<option value="'+ arr_cate[i].CATEGORY_SEQ +'" '+ sel +' data-hscode_seq="'+ arr_cate[i].HSCODE_SEQ +'">'+ arr_cate[i].CATEGORY_NM +'</option>';
		}
		
		str += '		</select>';
		str += '	</td>';
		str += '	<td scope="row" class="lf">';
		str += '		<select name="hscode[]" id="hscode_'+ idx +'" class="w95p">';
		
		for(var i = 0; i < arr_hscode.length; i++){
			var sel = '';
			str += '<option value="'+ arr_hscode[i].HSCODE_SEQ +'" '+ sel +'>'+ arr_hscode[i].HSCODE_NM +' ('+ arr_hscode[i].HSCODE_VALUE +')</option>';
		}
		
		str += '		</select>';
		str += '	</td>';
		str += '	<td scope="row" class="lf"><input type="text" class="w95p" name="productname[]" id="productname_'+ idx +'" value="'+ productname +'"></td>';
		str += '	<td scope="row" class="lf"><input type="text" class="w95p" name="invoicename[]" id="invoicename_'+ idx +'" value="'+ invoicename +'"></td>';
		str += '	<td scope="row" class="lf"><input type="text" class="w95p" name="optfield[]" id="optfield_'+ idx +'" value=""></td>';
		str += '	<td scope="row" class="lf"><input type="text" class="w95p" name="optvalue[]" id="optvalue_'+ idx +'" value=""></td>';
		str += '	<td scope="row" class="lf"><input type="text" class="w95p" name="qty[]" id="qty_'+ idx +'" value="" onkeyup="javascript:INPUT_ONLY_NUMBER(\'#qty_'+ idx +'\'); calSumArea();"></td>';
		str += '	<td scope="row" class="lf"><input type="text" class="w95p" name="price[]" id="price_'+ idx +'" value="" onkeyup="javascript:INPUT_ONLY_NUMBER(\'#price_'+ idx +'\'); calSumArea();"></td>';
		str += '	<td scope="row" class="lf"><input type="text" class="w95p" name="sumprice[]" id="sumprice_'+ idx +'" value="" onkeyup="javascript:INPUT_ONLY_NUMBER(\'#sumprice_'+ idx +'\'); calSumArea();"></td>';
		str += '	<td scope="row" class="lf"><input type="text" class="w95p" name="depositfee[]" id="depositfee_'+ idx +'" value="" onkeyup="javascript:INPUT_ONLY_NUMBER(\'#depositfee_'+ idx +'\'); calSumArea();"></td>';
		str += '	<td scope="row" class="ct"><input type="button" value="삭제" class="btn_02 w40" onclick="javascript:doDelete(\''+ idx +'\');"></td>';
		str += '</tr>';
		
		$('#tb_3').append(str);
		calSumArea();
	}
	
	// 합계부분계산
	function calSumArea(){
		$('#tr_sum').remove();
		
		var depositper = $('#depositper').val();
		
		var sum_qty = 0;
		var sum_price = 0;
		var sum_sumprice = 0;
		var sum_depositfee = 0;
		
		for(var i = 0; i < $('input[name="linkurl[]"]').length; i++){
			sum_qty = sum_qty + Number($('input[name="qty[]"]:eq('+ i +')').val());
			sum_price = sum_price + Number($('input[name="price[]"]:eq('+ i +')').val());
			
			sumprice = Number($('input[name="qty[]"]:eq('+ i +')').val()) * Number($('input[name="price[]"]:eq('+ i +')').val());
			$('input[name="sumprice[]"]:eq('+ i +')').val(sumprice);		// 항목별합계
			
			$('input[name="depositfee[]"]:eq('+ i +')').val(sumprice * Number(depositper) / 100);
			
			sum_sumprice = sum_sumprice + sumprice;
			sum_depositfee = sum_depositfee + Number($('input[name="depositfee[]"]:eq('+ i +')').val());
		}
		
		var str = "";
		str += '<tr id="tr_sum">';
		str += '	<td scope="row" class="lf" colspan="7">합 계</td>';
		str += '	<td scope="row" class="lf"><input type="text" class="w95p" name="qty_sum" id="qty_sum" value="'+  sum_qty +'"></td>';
		str += '	<td scope="row" class="lf"><input type="text" class="w95p" name="price_sum" id="price_sum" value="'+  sum_price +'"></td>';
		str += '	<td scope="row" class="lf"><input type="text" class="w95p" name="sumprice_sum" id="sumprice_sum" value="'+  sum_sumprice +'"></td>';
		str += '	<td scope="row" class="lf"><input type="text" class="w95p" name="depositfee_sum" id="depositfee_sum" value="'+  sum_depositfee +'"></td>';
		str += '	<td scope="row" class="lf"></td>';
		str += '</tr>';
		
		$('#pricesum').val(sum_sumprice);
		$('#tb_3').append(str);
		
		calOrderPrice();
	}
	
	// 주문금액에 따른 선금, 잔금, 디파짓피 계산
	function calOrderPrice(){
		var depositper = $('#depositper').val();
		
		var pricesum = $('#pricesum').val();
		var price_f = Number(pricesum) * 40 / 100;
		var price_b = Number(pricesum) - price_f;
		var depositfee = Number(pricesum) * Number(depositper) / 100;
		
		$('#price_f').val(price_f);
		$('#price_b').val(price_b);
		$('#depositfeesum').val(depositfee);
	}
	
	// 디파짓피 기본요율로 변경
	function doDepositInit(){
		$('#depositper').val('<?=$per?>');
		calSumArea();
	}
	
	// 최종항목복사
	function addCopyItem(){
		var idx = 0;
		var last_idx = 0;
		if($('.tr_item').length > 0){
			idx = $('.tr_item:last()').data('idx') + 1;
			last_idx = $('.tr_item:last()').data('idx');
		}
		
		var linkurl = $('#linkurl_'+ last_idx).val();
		var category_seq = $('#category_seq_'+ last_idx +' option:selected').val();
		var hscode_seq = $('#hscode_'+ last_idx +' option:selected').val();
		var productname = $('#productname_'+ last_idx).val();
		var invoicename = $('#invoicename_'+ last_idx).val();
		var optfield = $('#optfield_'+ last_idx).val();
		var optvalue = $('#optvalue_'+ last_idx).val();
		var qty = $('#qty_'+ last_idx).val();
		var price = $('#price_'+ last_idx).val();
		var sumprice = $('#sumprice_'+ last_idx).val();
		var depositfee = $('#depositfee_'+ last_idx).val();
		
		var str = "";
		str += '<tr class="tr_item" id="tr_item_'+ idx +'" data-idx="'+ idx +'">';
		str += '	<td scope="row" class="lf"><input type="text" class="w95p" name="linkurl[]" id="linkurl_'+ idx +'" value="'+ linkurl +'"></td>';
		str += '	<td scope="row" class="lf">';
		str += '		<select name="category_seq[]" id="category_seq_'+ idx +'" class="w95p" onchange="javascript:onChangeCategory(\''+ idx +'\');">';
		
		for(var i = 0; i < arr_cate.length; i++){
			var sel = '';
			if(arr_cate[i].CATEGORY_SEQ == category_seq) sel = 'selected';
			str += '<option value="'+ arr_cate[i].CATEGORY_SEQ +'" '+ sel +' data-hscode_seq="'+ arr_cate[i].HSCODE_SEQ +'">'+ arr_cate[i].CATEGORY_NM +'</option>';
		}
		
		str += '		</select>';
		str += '	</td>';
		str += '	<td scope="row" class="lf">';
		str += '		<select name="hscode[]" id="hscode_'+ idx +'" class="w95p">';
		
		for(var i = 0; i < arr_hscode.length; i++){
			var sel = '';
			if(arr_hscode[i].HSCODE_SEQ == hscode_seq) sel = 'selected';
			str += '<option value="'+ arr_hscode[i].HSCODE_SEQ +'" '+ sel +'>'+ arr_hscode[i].HSCODE_NM +' ('+ arr_hscode[i].HSCODE_VALUE +')</option>';
		}
		
		str += '		</select>';
		str += '	</td>';
		str += '	<td scope="row" class="lf"><input type="text" class="w95p" name="productname[]" id="productname_'+ idx +'" value="'+ productname +'"></td>';
		str += '	<td scope="row" class="lf"><input type="text" class="w95p" name="invoicename[]" id="invoicename_'+ idx +'" value="'+ invoicename +'"></td>';
		str += '	<td scope="row" class="lf"><input type="text" class="w95p" name="optfield[]" id="optfield_'+ idx +'" value="'+ optfield +'"></td>';
		str += '	<td scope="row" class="lf"><input type="text" class="w95p" name="optvalue[]" id="optvalue_'+ idx +'" value="'+ optvalue +'"></td>';
		str += '	<td scope="row" class="lf"><input type="text" class="w95p" name="qty[]" id="qty_'+ idx +'" value="'+ qty +'" onkeyup="javascript:INPUT_ONLY_NUMBER(\'#qty_'+ idx +'\'); calSumArea();"></td>';
		str += '	<td scope="row" class="lf"><input type="text" class="w95p" name="price[]" id="price_'+ idx +'" value="'+ price +'" onkeyup="javascript:INPUT_ONLY_NUMBER(\'#price_'+ idx +'\'); calSumArea();"></td>';
		str += '	<td scope="row" class="lf"><input type="text" class="w95p" name="sumprice[]" id="sumprice_'+ idx +'" value="'+ sumprice +'" onkeyup="javascript:INPUT_ONLY_NUMBER(\'#sumprice_'+ idx +'\'); calSumArea();"></td>';
		str += '	<td scope="row" class="lf"><input type="text" class="w95p" name="depositfee[]" id="depositfee_'+ idx +'" value="'+ depositfee +'" onkeyup="javascript:INPUT_ONLY_NUMBER(\'#depositfee_'+ idx +'\'); calSumArea();"></td>';
		str += '	<td scope="row" class="ct"><input type="button" value="삭제" class="btn_02 w40" onclick="javascript:doDelete(\''+ idx +'\');"></td>';
		str += '</tr>';
		
		$('#tb_3').append(str);
		calSumArea();
	}
	
	// 주문항목 삭제
	function doDelete(id){
		$('#tr_item_'+ id).remove();
		calSumArea();
	}
	
	// 제품검색팝업
	function popSearch(){
		OPEN_LAYER_POPUP('#search_product');
		getProductList();
	}
	
	// 제품검색팝업 리스트
	function getProductList(){
		var url = "reqSearchAjax.php";
		var param = {
			pname: $('#pname').val()
			, manufacturename: $('#manufacturename').val()
		};
		
		$.ajax({
			type:"POST",
			dataType : 'html',
			url: url,
			data: param,
			async: false,
			success: function(rtn){
				$('#search_product').html(rtn);
			},
			error: function(request, status, error){
				alert('Find Error -> '+ status);
				return false;
			}
		});
	}
	
	// 회원검색팝업
	function popUser(){
		OPEN_LAYER_POPUP('#search_user');
		getUserList();
	}
	
	// 회원검색팝업 리스트
	function getUserList(){
		var url = "reqUserAjax.php";
		var param = {
			user_id: $('#user_id').val()
			, user_nm: $('#user_nm').val()
		};
		
		$.ajax({
			type:"POST",
			dataType : 'html',
			url: url,
			data: param,
			async: false,
			success: function(rtn){
				$('#search_user').html(rtn);
			},
			error: function(request, status, error){
				alert('Find Error -> '+ status);
				return false;
			}
		});
	}
	
	// 등록
	function doProc(){
		if(confirm("해당 정보로 등록하시겠습니까?")){
			if($('#name').val() == ''){alert("이름을 입력해 주세요"); $('#name').focus(); return false;}
			if($('#tel').val() == ''){alert("연락처를 입력해 주세요"); $('#tel').focus(); return false;}
			if($('#email').val() == ''){alert("이메일을 입력해 주세요"); $('#email').focus(); return false;}
			if($('#pricesum').val() == ''){alert("주문금액을 입력해 주세요"); $('#pricesum').focus(); return false;}
			if($('#dst_addr').val() == ''){alert("도착지영문주소를 입력해 주세요"); $('#dst_addr').focus(); return false;}
			if($('.tr_item').length < 1){alert("주문항목이 없습니다"); return false;}
			
			$('#pageaction').val('insert');
			
			var url = "reqProc.php";
			var param = $('#frm').serialize();
			
			$.ajax({
				type:"POST",
				dataType : 'json',
				url:url,
				data: param,
				async: false,
				success: function(obj){
					if(obj.status == 0){
						alert(obj.msg);
						return false;
					}
					else{
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
	
	// 영업수수료세팅
	function selSalesPer(){
		var per = $('#sales_seq option:selected').data('per');
		$('#per').val(per);
	}
	
	// 카테고리변경시 HSCODE연동
	function onChangeCategory(idx){
		var hscode_seq = $('#category_seq_'+ idx +' option:selected').data('hscode_seq');
		
		if(hscode_seq != ''){
			$('#hscode_'+ idx).val(hscode_seq).prop('selected', true);
		}
	}
	
	// 임시저장
	function doTempSave(){
		if(confirm("임시저장 하시겠습니까?")){
			$('#pageaction').val('temp');
			
			var url = "reqProc.php";
			var param = $('#frm').serialize();
			
			$.ajax({
				type:"POST",
				dataType : 'json',
				url:url,
				data: param,
				async: false,
				success: function(obj){
					if(obj.status == 0){
						alert(obj.msg);
						return false;
					}
					else{
						alert(obj.msg);
						//location.reload();
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
					<input type="hidden" name="user_seq" id="user_seq" value="<?=$rs[0]['USER_SEQ']?>">
					<div class='mt20'>
						<h2>1. 기본정보</h2>
						<p class="fr">
							<input type='button' value='회원검색' class='btn_02 w100' onclick="javascript:popUser();"/>
						</p>
					</div>

					<div class="both"></div>

					<table border="0" width="100%" cellspacing="0" cellpadding="0" class='adtb_02 mt10'>
						<colgroup>
							<col width='10%' />
							<col width='40%' />
							<col width='10%' />
							<col width='40%' />
						</colgroup>
						<tr>
							<th scope="row">주문번호</th>
							<td class='lf'>
								<input type="text" name="orderid" id="orderid" class="w95p" value="<?=$orderid?>" readonly>
							</td>
							<th scope="row">이름</th>
							<td class='lf'>
								<input type="text" name="name" id="name" class="w95p" value="<?=$rs[0]['NAME']?>">
							</td>
						</tr>
						<tr>
							<th scope="row">연락처</th>
							<td class='lf'><input name="tel" id="tel" type="text" class='w95p' value="<?=$rs[0]['TEL']?>" maxlength="11" onkeyup="javascript:INPUT_ONLY_NUMBER('#tel');" ></td>
							<th scope="row">이메일</th>
							<td class='lf'>
								<input type="text" name="email" id="email" class="w95p" value="<?=$rs[0]['EMAIL']?>">
							</td>
						</tr>
						<tr>
							<th scope="row">주문신청일</th>
							<td class='lf'><input name="ord_dt" id="ord_dt" type="text" class='w95p' value="<?=$ord_dt?>" placeholder="주문서생성시 자동입력" readonly></td>
							<th scope="row">주문금액</th>
							<td class='lf'>
								<input type="text" name="pricesum" id="pricesum" class="w95p" value="<?=$rs[0]['PRICESUM']?>" onkeyup="javascript:INPUT_ONLY_NUMBER('#tel'); calOrderPrice();">
							</td>
						</tr>
						<tr>
							<th scope="row">선금</th>
							<td class='lf'><input name="price_f" id="price_f" type="text" class='w95p' value="<?=$rs[0]['PRICE_F']?>" readonly></td>
							<th scope="row">디파짓피</th>
							<td class='lf'>
								<input type="text" name="depositfeesum" id="depositfeesum" class="w30p" value="<?=$rs[0]['DEPOSITFEE']?>">
								요율설정 : <input type="text" name="depositper" id="depositper" class="w30p" value="<?=$per?>" onkeyup="javascript:calSumArea();">
								<input type='button' value='초기화' class='btn_b01 w80' onclick="javascript:doDepositInit();"/>
							</td>
						</tr>
						<tr>
							<th scope="row">잔금</th>
							<td class='lf'><input name="price_b" id="price_b" type="text" class='w95p' value="<?=$rs[0]['PRICE_B']?>" readonly></td>
							<th scope="row">원주문번호</th>
							<td class='lf'>
								<input type="text" name="userordid" id="userordid" class="w95p" value="<?=$rs[0]['USERORDID']?>" readonly>
							</td>
						</tr>
						<tr>
							<th scope="row">영업담당자</th>
							<td class='lf'>
								<select name="sales_seq" id="sales_seq" onchange="javascript:selSalesPer();">
									<option value="">선택안함</option>
<?
			foreach($rsSales as $iSales=>$dsSales){
?>
									<option value="<?=$dsSales['SALES_SEQ']?>" data-per="<?=$dsSales['PER']?>" <?=$cFnc->CodeString($dsSales['SALES_SEQ'], $rs[0]['SALES_SEQ'], 'selected', '')?>><?=$dsSales['SALESNAME']?></option>
<?
			}
?>
								</select>
							</td>
							<th scope="row">영업수수료</th>
							<td class='lf'>
								<input type="text" name="per" id="per" class="w10p" value="<?=$rs[0]['PER_DECIMAL']?>">%
							</td>
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
							<td class='lf'>
								<input type="text" name="dst_addr" id="dst_addr" class="w95p" value="<?=$rs[0]['DST_ADDR']?>">
							</td>
						</tr>
						<tr>
							<th scope="row">기타요청사항</th>
							<td class='lf'>
								<textarea name="memo" id="memo" class="w95p" rows="5"><?=$rs[0]['MEMO']?></textarea>
							</td>
						</tr>
					</table>
					
					<div class='mt20'>
						<h2 class="fl">3. 주문항목</h2>
						<p class="fr">
							<input type='button' value='제품검색' class='btn_02 w100' onclick="javascript:popSearch();"/>
						</p>
<?
	if($usermstid == ''){
?>
						<p class="fr mr10">
							<input type="button" value="추가" class='btn_02 w40' onclick="javascript:addItem('', '', '', '');">
							<input type="button" value="최종항목복사" class='btn_02 w80' onclick="javascript:addCopyItem();">
						</p>
<?
	}
?>
					</div>

					<div class="both"></div>
					
					<table border="0" width="100%" cellspacing="0" cellpadding="0" class='adtb_02 mt10' id="tb_3">
						<colgroup>
							<col width='20%' />
						</colgroup>
						<tr>
							<th scope="row">신청링크</th>
							<th scope="row">카테고리</th>
							<th scope="row">HSCODE</th>
							<th scope="row">제품명</th>
							<th scope="row">인보이스명</th>
							<th scope="row">옵션필드</th>
							<th scope="row">옵션값</th>
							<th scope="row">수량</th>
							<th scope="row">금액</th>
							<th scope="row">합계</th>
							<th scope="row">디파짓피</th>
							<th scope="row">삭제</th>
						</tr>
<?
	if(count($rs) > 0){
		foreach($rs as $i=>$ds){
?>
						<tr class="tr_item" id="tr_item_<?=$ds['USERITEMID']?>" data-idx="<?=$ds['USERITEMID']?>">
							<td scope="row" class="lf"><input type="text" class="w95p" name="linkurl[]" id="linkurl_<?=$ds['USERITEMID']?>" value="<?=$ds['LINKURL']?>"></td>
							<td scope="row" class="lf">
								<select name="category_seq[]" id="category_seq_<?=$ds['USERITEMID']?>" class="w95p" onchange="javascript:onChangeCategory('<?=$ds['USERITEMID']?>');">
									<!-- category db -->
<?
			foreach($rsCate as $iCate=>$dsCate){
?>
									<option value="<?=$dsCate['CATEGORY_SEQ']?>" data-hscode_seq="<?=$dsCate['HSCODE_SEQ']?>" <?=$cFnc->CodeString($dsCate['CATEGORY_SEQ'], $ds['CATEGORY_SEQ'], 'selected', '')?>><?=$dsCate['CATEGORY_NM']?></option>
<?
			}
?>
								</select>
							</td>
							<td scope="row" class="lf">
								<select name="hscode[]" id="hscode_<?=$ds['USERITEMID']?>" class="w95p">
									<!-- category db -->
<?
			foreach($rsHscode as $iHs=>$dsHs){
?>
									<option value="<?=$dsHs['HSCODE_SEQ']?>" <?=$cFnc->CodeString($dsHs['HSCODE_SEQ'], $ds['HSCODE_SEQ'], 'selected', '')?>><?=$dsHs['HSCODE_NM']?> (<?=$dsHs['HSCODE_VALUE']?>)</option>
<?
			}
?>
								</select>
							</td>
							<td scope="row" class="lf"><input type="text" class="w95p" name="productname[]" id="productname_<?=$ds['USERITEMID']?>" value="<?=$ds['ITEM_NM']?>"></td>
							<td scope="row" class="lf"><input type="text" class="w95p" name="invoicename[]" id="invoicename_<?=$ds['USERITEMID']?>" value="<?=$ds['INVOICENAME']?>"></td>
							<td scope="row" class="lf"><input type="text" class="w95p" name="optfield[]" id="optfield_<?=$ds['USERITEMID']?>" value="<?=$ds['OPTFIELD']?>"></td>
							<td scope="row" class="lf"><input type="text" class="w95p" name="optvalue[]" id="optvalue_<?=$ds['USERITEMID']?>" value="<?=$ds['OPTVALUE']?>"></td>
							<td scope="row" class="lf"><input type="text" class="w95p" name="qty[]" id="qty_<?=$ds['USERITEMID']?>" value="<?=$ds['QTY']?>" onkeyup="javascript:INPUT_ONLY_NUMBER('#qty_<?=$ds['USERITEMID']?>'); calSumArea();"></td>
							<td scope="row" class="lf"><input type="text" class="w95p" name="price[]" id="price_<?=$ds['USERITEMID']?>" value="<?=$ds['PRICE']?>" onkeyup="javascript:INPUT_ONLY_NUMBER('#price_<?=$ds['USERITEMID']?>'); calSumArea();"></td>
							<td scope="row" class="lf"><input type="text" class="w95p" name="sumprice[]" id="sumprice_<?=$ds['USERITEMID']?>" value="<?=$ds['SUMPRICE']?>" onkeyup="javascript:INPUT_ONLY_NUMBER('#sumprice_<?=$ds['USERITEMID']?>'); calSumArea();"></td>
							<td scope="row" class="lf"><input type="text" class="w95p" name="depositfee[]" id="depositfee_<?=$ds['USERITEMID']?>" value="<?=$ds['DEPOSITFEE']?>" onkeyup="javascript:INPUT_ONLY_NUMBER('#depositfee_<?=$ds['USERITEMID']?>'); calSumArea();"></td>
							<td scope="row" class="ct"><input type="button" value="삭제" class='btn_02 w40' onclick="javascript:doDelete('<?=$ds['USERITEMID']?>');"></td>
						</tr>
<?
		}
	}
?>
					</table>
					
					<div class='center mt20'>
						<input type='button' value='임시저장' class='btn_b01 w80' onclick="javascript:doTempSave();"/>
						<input type='button' value='등록' class='btn_b01 w80' onclick="javascript:doProc();"/>
<?
	if($usermstid != ''){
?>
						<input type='button' value='취소' class='btn_b02 w80' onclick="javascript:doCancel();"/>
<?
	}
?>
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