// 콤마제거
function UNCOMMIFY(n){
	var n = String(n);
	return n.replace(/(,)/g, '');
}

// 천단위 콤마 찍기
function COMMIFY(n){
	var n = String(n);
	return n.replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,');
}//end of COMMIFY()


// 입력시 천단위 콤마 찍기
function INPUT_COMMIFY(n){
	if( n == -1 || n == '-' ) return -1;
	return n.match(/[0-9]/g).join("").replace(/(\d)(?=(\d\d\d)+$)/g , '$1,');
}//end of INPUT_COMMIFY()

// 모든 체크박스 체크
function CHKBOX_ALL_CK(all_ck_name, t_name){

	if( $("input:checkbox[name='" + all_ck_name + "']").is(":checked") || $("input:checkbox[id='" + all_ck_name + "']").is(":checked") )	var ck_val = true;
	else																																	var ck_val = false;

	for( var i=0; i < $("input:checkbox[name='" + t_name + "']").length; i++ ){
		$("input:checkbox[name='" + t_name + "']").attr("checked", ck_val);
	}

}//end of ALL_CK()

/**
 * 시스템팝업창 화면 가운데 띄우기
 * @param pUrl
 * @param pName
 * @param pOption
 * @returns
 */
function OPEN_SYSTEM_POPUP(pUrl, pName, pWidth, pHeight){
	var screenWidth = screen.availWidth;
	var screenHeight = screen.availHeight;
	var nLeft = (screenWidth - pWidth) / 2;
	var nTop = (screenHeight - pHeight) / 2;

	var features = "width=" + pWidth;
	features += ",height=" + pHeight;
	features += ",left=" + nLeft;
	features += ",top=" + nTop;
	features += ",resizable=1";
	features += ",scrollbars=1";
	features += ",toolbar=0";
	features += ",status=1";
	features += ",menubar=0";

	var win = window.open(pUrl, pName, features);
	win.focus();
}

/**
  * 문자열 길이 체크 (한글까지 고려하여 계산됨)
  * @param String	문자열
  * @return int 문자열의 길이
 */
function GET_STR_LEN(value) {
	var byteLength = 0;

	for (var inx = 0; inx < value.length; inx++) {
		var oneChar = escape(value.charAt(inx));

		if ( oneChar.length == 1 ) {
			byteLength ++;
		}
		else if (oneChar.indexOf("%u") != -1) {
			byteLength += 2;
		}
		else if (oneChar.indexOf("%") != -1) {
			byteLength += oneChar.length/3;
		}
	}

	return byteLength;
}

// 숫자만 입력
// desc: onkeyup이벤트에 사용
function INPUT_ONLY_NUMBER(el){
	if($(el).val().match(/[^0-9]/g) != null){
		alert("숫자만 입력 가능합니다.");
		$(el).val($(el).val().replace(/[^0-9]/g, ''));
		return false;
	}
}

// 10보다 작으면 앞에 0 붙임 [시간 구할 때]
function ADD_ZERO(value){
	if(value < 10){
		value = "0" + value;
	}

	return value;
}

// 다이얼로그 팝업 열기
function OPEN_LAYER_POPUP(el){
	$(el).bPopup({
		modalClose: false
		, escClose: false
	});
}

// 다이얼로그 팝업 닫기
function CLOSE_LAYER_POPUP(el){
	$(el).bPopup().close();
}

// 로딩중 열기
function OPEN_LOADING(){
	$('#loading').show();
}

// 로딩중 닫기
function CLOSE_LOADING(){
	$('#loading').hide();
}

// 휴대폰번호 형식
function FORMAT_PHONE(num, type){
	var formatNum = '';
	
	if(num != null){
		if(num.length==11){
			if(type == 0){
				formatNum = num.replace(/(\d{3})(\d{4})(\d{4})/, '$1-****-$3');
			}
			else{
				formatNum = num.replace(/(\d{3})(\d{4})(\d{4})/, '$1-$2-$3');
			}
		}
		else if(num.length == 8){
			formatNum = num.replace(/(\d{4})(\d{4})/, '$1-$2');
		}
		else{
			if(num.indexOf('02') == 0){
				if(type==0){
					formatNum = num.replace(/(\d{2})(\d{4})(\d{4})/, '$1-****-$3');
				}
				else{
					formatNum = num.replace(/(\d{2})(\d{4})(\d{4})/, '$1-$2-$3');
				}
			}
			else{
				if(type == 0){
					formatNum = num.replace(/(\d{3})(\d{3})(\d{4})/, '$1-***-$3');
				}
				else{
					formatNum = num.replace(/(\d{3})(\d{3})(\d{4})/, '$1-$2-$3');
				}
			}
		}
	}
	
	return formatNum;
}

// 이메일형식 체크
function VALIDATION_EMAIL(email){
	var regex=/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/;
	var bResult = false;
	
	if(regex.test(email) === false) {
		bResult = false;
	}
	else {
		bResult = true;
	}
	
	return bResult;
}

// 전화번호, 휴대폰 형식 체크
function VALIDATION_PHONE(phone){
	var regTel = /^(01[016789]{1}|070|02|0[3-9]{1}[0-9]{1})[0-9]{3,4}[0-9]{4}$/;
	//var regTel = /^(01[016789]{1}|070|02|0[3-9]{1}[0-9]{1})-[0-9]{3,4}-[0-9]{4}$/;
	var bResult = false;
	
	if(!regTel.test(phone)) {
		bResult = false;
	}
	else{
		bResult = true;
	}
	
	return bResult;
}

// 날짜구하기
function GET_DATE_YMD(){
	var date = new Date();
	
	var year = date.getFullYear();
	var month = ADD_ZERO(date.getMonth() + 1);
	var day = ADD_ZERO(date.getDate());
	var rtn = String(year) + String(month) + String(day);
	
	return rtn;
}

// 날짜이동
function GET_MOVE_DATE(ymd, gbn, num){
	var y = ymd.substring(0, 4);
	var m = ymd.substring(4, 6);
	var d = ymd.substring(6, 8);
	
	if(m.substring(0, 1) == '0') m = m.substring(1, 2);
	if(d.substring(0, 1) == '0') d = d.substring(1, 2);
	
	var date = new Date(y, Number(m) - 1, d);
	
	if(gbn == 'year'){
		if(num != '') date = new Date(date.getFullYear() + Number(num), date.getMonth(), date.getDate());
	}
	else if(gbn == 'month'){
		if(num != '') date = new Date(date.getFullYear(), date.getMonth() + Number(num), date.getDate());
	}
	else if(gbn == 'day'){
		if(num != '') date = new Date(date.getFullYear(), date.getMonth(), date.getDate() + Number(num));
	}
	
	var year = date.getFullYear();
	var month = ADD_ZERO(date.getMonth() + 1);
	var day = ADD_ZERO(date.getDate());
	var rtn = String(year) + String(month) + String(day);
	
	return rtn;
}
