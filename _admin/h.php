<?
	// 스크립트, CSS INCLUDE 갱신
	$strRenew = $cFnc->GenerateRanStr(10, $type = '');
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
<meta name="format-detection" content="telephone=no">
<title>NS9 Vt 유통솔루션 관리자</title>
<link rel="stylesheet" href="/css/jquery-ui-1.10.3.css">
<link rel="stylesheet" href="/css/font.css">
<link rel="stylesheet" href="/css/common.css?<?=$strRenew?>">
<link rel="stylesheet" href="/css/mcall.css?<?=$strRenew?>">
<link rel="stylesheet" href="/_admin/css/admin.css?<?=$strRenew?>">

<script type="text/javascript" src="/js/jquery-1.11.3.min.js"></script>
<script type="text/javascript" src="/js/jquery-migrate-1.2.1.min.js"></script>
<script type="text/javascript" src="/js/jquery.animate.min.js"></script>

<script type="text/javascript" src="/js/jquery-ui-1.10.3.js"></script>
<script type="text/javascript" src="/js/jquery-ui-datepicker-ko.js"></script>

<script type="text/javascript" src="/js/jquery.bpopup.js"></script>
<script type="text/javascript" src="/js/printThis.js"></script>

<script type="text/javascript" src="/js/common.js?<?=$strRenew?>"></script>

<script>
	$(document).ready(function(){
		// IE9 이하 버전 업그레이드 경고창
		if( $.browser.msie == true && $.browser.version <= 9){
			alert('IE10 버전 이상으로 업데이트해주세요.');
		}
	});
	
	// 특정영역인쇄[공통]
	function AREA_PRINT(selector){
		$(selector).printThis({
			debug: false,               // show the iframe for debugging
			importCSS: true,            // import page CSS
			importStyle: false,         // import style tags
			printContainer: true,       // grab outer container as well as the contents of the selector
			loadCSS: ["/css/common.css","/_admin/css/admin.css"],  // path to additional css file - use an array [] for multiple
			pageTitle: "",              // add title to print page
			removeInline: false,        // remove all inline styles from print elements
			printDelay: 333,            // variable print delay; depending on complexity a higher value may be necessary
			header: null,               // prefix to html
			footer: null,               // postfix to html
			base: false ,               // preserve the BASE tag, or accept a string for the URL
			formValues: true,           // preserve input/form values
			canvas: false,              // copy canvas elements (experimental)
			doctypeString: "...",       // enter a different doctype for older markup
			removeScripts: false        // remove script tags from print content
		});
	}
</script>

<!--[if lt IE 9]>
<script type="text/javascript" src="/js/html5.js"></script>
<![endif]-->

<!--[if lt IE 8]>
<style type="text/css">
	#ie67{display:block !important}
</style>
<![endif]-->

</head>

<body>

	<div id="loading" style="display:none">
		<div class="loading">처리중입니다. 잠시만 기다려주십시오.</div>
	</div>
