<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	chkSessionAjax($url = '/_admin/');
	
	//=====================================================
	//== 도움말 - Get Parameters
	//=====================================================
	$user_id = $cFnc->getReq('user_id', '');
	$user_nm = $cFnc->getReq('user_nm', '');

	// =====================================================
	// Set Variables
	// =====================================================
	$cPdo = new cPdo($ARR_DB_INFO);

	// =====================================================
	// Start Tran
	// =====================================================
	// DB리스트조회
	$arParam = array();
	$qryWhere = "WHERE USE_YN = 'Y'";
	
	if($user_id != ''){
		$qryWhere .= " AND USER_ID LIKE ?";
		array_push($arParam, '%'. $user_id .'%');
	}
	if($user_nm != ''){
		$qryWhere .= " AND USER_NM LIKE ?";
		array_push($arParam, '%'. $user_nm .'%');
	}
	
	$qry = "
		SELECT USER_SEQ, USER_ID, USER_NM, TEL
		FROM TB_USER
		". $qryWhere ."
		ORDER BY USER_NM
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('list', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$rs = $result['data'];
	
	if(is_object($cPdo))$cPdo->close();
?>

<script>
	// 전체체크
	function onChkAll(){
		if($('#chkall').is(':checked') == true){
			$('input[name="chkitem"]').prop('checked', true);
		}
		else{
			$('input[name="chkitem"]').prop('checked', false);
		}
	}
	
	// 적용
	function doApply(user_seq){
		var user_id = $('#btn_apply_'+ user_seq).data('user_id');
		var user_nm = $('#btn_apply_'+ user_seq).data('user_nm');
		var tel = $('#btn_apply_'+ user_seq).data('tel');
		
		$('#user_seq').val(user_seq);
		$('#email').val(user_id);
		$('#name').val(user_nm);
		$('#tel').val(tel);
		
		CLOSE_LAYER_POPUP('#search_user');
	}
</script>

		<div id='pop'>
			<div class='title'>
				<h2>회원검색</h2>
				<a href="javascript:;" class='pop_close' onclick="javascript:CLOSE_LAYER_POPUP('#search_user');">닫기</a>
			</div>
			<table width="100%" border="0" cellspacing="0" cellpadding="0" class='tb_01_non mt10'>
				<colgroup>
					<col width='10%' />
					<col width='25%' />
					<col width='10%' />
					<col width='25%' />
					<col width='10%' />
				</colgroup>
				<tr>
					<th scope="row">회원ID</th>
					<td class='lf2'><input type="text" name="user_id" id="user_id" value="<?=$user_id?>"></td>
					<th>회원명</th>
					<td class='lf2'><input type="text" name="user_nm" id="user_nm" value="<?=$user_nm?>"></td>
					<td><input type='button' value='검색' class='btnp_03 w80' onclick="javascript:getUserList();" /></td>
				</tr>
			</table>
			<table width="100%" border="0" cellspacing="0" cellpadding="0" class='tb_01' id="pop_req_table">
				<colgroup>
					<col width='20%' />
					<col width='20%' />
					<col width='20%' />
					<col width='20%' />
					<col width='20%' />
				</colgroup>
				<tr>
					<th scope="col">회원번호</th>
					<th scope="col">회원ID</th>
					<th scope="col">회원명</th>
					<th scope="col">연락처</th>
					<th scope="col">선택</th>
				</tr>
<?
	foreach($rs as $i=>$ds){
?>
				<tr>
					<td><?=$ds['USER_SEQ']?></td>
					<td><?=$ds['USER_ID']?></td>
					<td><?=$ds['USER_NM']?></td>
					<td><?=$ds['TEL']?></td>
					<td>
						<input type="button" value="선택" id="btn_apply_<?=$ds['USER_SEQ']?>" class="btnp_03 w80" onclick="javascript:doApply('<?=$ds['USER_SEQ']?>');" data-user_seq="<?=$ds['USER_SEQ']?>" data-user_id="<?=$ds['USER_ID']?>" data-user_nm="<?=$ds['USER_NM']?>" data-tel="<?=$ds['TEL']?>">
					</td>
				</tr>
<?
	}
?>
			</table>
		</div>