<?
	if($S_GRADE == 'U'){
?>

<div id='lnb'>
	<h1><a href='/_admin/page/request/reqInput_vt.php'><img src='/_admin/images/logo.png' /></a></h1>
<?
		if($_MENU1 == '3'){
?>
	<h2>OrderMng</h2>
    <ul>
        <li class='<?=$_MENU2 == '2' ? 'sel' : ''?>'><a href='reqInput_vt.php'>OrderRequest</a></li>
        
    </ul>
<?
		}
?>
</div>

<?
	}
	else{
?>

<div id='lnb'>
	<h1><a href='/_admin/main.php'><img src='/_admin/images/logo.png' /></a></h1>
<?
		if($_MENU1 == ''){
?>
	<dl class='member_info'>
    	<dt><span><?=$S_ADM_NM?></span> 님의 정보</dt>
        <dd>
        	<ul>
            	<li>아이디 : <span><?=$S_ADM_ID?></span></li>
                <li>이름 : <span><?=$S_ADM_NM?></span></li>
            </ul>
        </dd>
    </dl>
<?
		}
		else if($_MENU1 == '1'){
?>
	<h2>시스템관리</h2>
    <ul>
    	<li class='<?=$_MENU2 == '1' ? 'sel' : ''?>'><a href='systemList.php'>코드관리</a></li>
    	<li class='<?=$_MENU2 == '2' ? 'sel' : ''?>'><a href='depositList.php'>디파짓관리</a></li>
        <li class='<?=$_MENU2 == '3' ? 'sel' : ''?>'><a href='rateList.php'>환율관리</a></li> 
        <li class='<?=$_MENU2 == '4' ? 'sel' : ''?>'><a href='deliverList.php'>배송비관리</a></li>
		<li class='<?=$_MENU2 == '5' ? 'sel' : ''?>'><a href='deliverUnitList.php'>배송비단위관리</a></li>
        <!-- <li class='<?=$_MENU2 == '6' ? 'sel' : ''?>'><a href='#'>사서함번호관리</a></li> -->
        <li class='<?=$_MENU2 == '7' ? 'sel' : ''?>'><a href='storageList.php'>재고창고관리</a></li>
		<li class='<?=$_MENU2 == '8' ? 'sel' : ''?>'><a href='hscodeList.php'>HSCODE관리</a></li>
		<li class='<?=$_MENU2 == '9' ? 'sel' : ''?>'><a href='moLogList.php'>메시지로그</a></li>
    </ul>
<?
		}
		else if($_MENU1 == '2'){
?>
    <h2>담당자관리</h2>
    <ul>
        <li class='<?=$_MENU2 == '1' ? 'sel' : ''?>'><a href='adminList.php'>계정관리</a></li>
		<!--
        <li class='<?=$_MENU2 == '2' ? 'sel' : ''?>'><a href='#'>권한관리</a></li>
        <li class='<?=$_MENU2 == '3' ? 'sel' : ''?>'><a href='#'>메뉴관리</a></li>
		-->
        <li class='<?=$_MENU2 == '4' ? 'sel' : ''?>'><a href='salesList.php'>영업담당자관리</a></li>  
        
    </ul>
<?
		}
		else if($_MENU1 == '3'){
?>
    <h2>신청관리</h2>
    <ul>
        <li class='<?=$_MENU2 == '1' ? 'sel' : ''?>'><a href='reqList.php'>신청현황</a></li> 
        <li class='<?=$_MENU2 == '2' ? 'sel' : ''?>'><a href='reqInput.php'>주문서생성</a></li>
        
    </ul>
<?
		}
		else if($_MENU1 == '4'){
?>
    <h2>주문관리</h2>
    <ul>
        <li class='<?=$_MENU2 == '1' ? 'sel' : ''?>'><a href='orderAccept.php'>주문접수</a></li>
        <li class='<?=$_MENU2 == '2' ? 'sel' : ''?>'><a href='orderPay.php'>결제확인</a></li>
		<li class='<?=$_MENU2 == '3' ? 'sel' : ''?>'><a href='orderGoodsReady.php'>상품준비</a></li>
		<li class='<?=$_MENU2 == '4' ? 'sel' : ''?>'><a href='orderEnterReady.php'>입고대기</a></li>
		<li class='<?=$_MENU2 == '5' ? 'sel' : ''?>'><a href='orderEnterFin.php'>입고완료</a></li>
		<li class='<?=$_MENU2 == '6' ? 'sel' : ''?>'><a href='orderOutReq.php'>출고요청</a></li>
		<li class='<?=$_MENU2 == '7' ? 'sel' : ''?>'><a href='orderOutFin.php'>출고완료</a></li>
		<li class='<?=$_MENU2 == '8' ? 'sel' : ''?>'><a href='orderOK.php'>구매확인완료</a></li>
		<li class='<?=$_MENU2 == '9' ? 'sel' : ''?>'><a href='orderAll.php'>통합주문현황</a></li>
		<li class='<?=$_MENU2 == '10' ? 'sel' : ''?>'><a href='orderPurchase.php'>구매현황</a></li>
		<li class='<?=$_MENU2 == '11' ? 'sel' : ''?>'><a href='orderPrint.php'>COD & 면장</a></li>
		<li class='<?=$_MENU2 == '12' ? 'sel' : ''?>'><a href='orderDeliverPrice.php'>CJ실중량입력</a></li>
		<li class='<?=$_MENU2 == '13' ? 'sel' : ''?>'><a href='orderPaymentInfo.php'>결제승인일입력</a></li>
    </ul>
<?
		}
		else if($_MENU1 == '5'){
?>
    <h2>제품관리</h2>
    <ul>
        <li class='<?=$_MENU2 == '1' ? 'sel' : ''?>'><a href='manufactureList.php'>제조사관리</a></li>         
        <li class='<?=$_MENU2 == '2' ? 'sel' : ''?>'><a href='productList.php'>상품현황</a></li>         
    </ul>
<?
		}
		else if($_MENU1 == '6'){
?>
    <h2>카테고리관리</h2>
    <ul>
        <li class='<?=$_MENU2 == '1' ? 'sel' : ''?>'><a href='categoryList.php'>카테고리관리</a></li>         
    </ul>
<?
		}    
        else if($_MENU1 == '7'){
?>
    <h2>정산현황</h2>
    <ul>
        <li class='<?=$_MENU2 == '1' ? 'sel' : ''?>'><a href='adjustment.php'>정산관리</a></li>         
    </ul>
<?
        }            
		else if($_MENU1 == '8'){
?>
    <h2>회원관리</h2>
    <ul>
        <li class='<?=$_MENU2 == '1' ? 'sel' : ''?>'><a href='memberList.php'>회원관리</a></li>         
    </ul>
<?
		}
?>
</div>

<?
	}
?>