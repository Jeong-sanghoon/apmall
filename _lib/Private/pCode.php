<?
	// 사용, 중지
	function CODE_USE_YN($code){
		$rtn = '';
		
		switch($code){
			case 'Y'	: $rtn = '사용'; break;
			case 'N'	: $rtn = '미사용'; break;
			default		: $rtn = ''; break;
		}
		
		return $rtn;
	}
	
	// 전체관리자여부
	function CODE_SYSADMIN_YN($code){
		$rtn = '';
		
		switch($code){
			case 'Y'	: $rtn = '전체관리자'; break;
			case 'N'	: $rtn = '일반관리자'; break;
			default		: $rtn = ''; break;
		}
		
		return $rtn;
	}

	// 관리자 등급
	function CODE_ADM_GRADE($code){
		$rtn = '';
		
		switch($code){
			case 'S'	: $rtn = '시스템관리자'; break;
			case 'A'	: $rtn = '관리자'; break;
			case 'U'	: $rtn = '일반관리자'; break;
			default		: $rtn = ''; break;
		}
		
		return $rtn;	
	}

	// 배송구분
	function CODE_DELIVERY_COST_TYPE($code){
		$rtn = '';
		
		switch($code){
			case 'O'	: $rtn = '구매대행'; break;
			case 'D'	: $rtn = '배송대행'; break;
			default		: $rtn = ''; break;
		}
		
		return $rtn;	
	}
	
	// 배송비 적용대상
	function CODE_DELIVERY_OBJ_TYPE($code){
		$rtn = '';
		
		switch($code){
			case 'P'	: $rtn = '고객'; break;
			case 'C'	: $rtn = '배송사'; break;
			default		: $rtn = ''; break;
		}
		
		return $rtn;	
	}

	// 제품상태
	function CODE_PRODUCT_STATUS($code){
		$rtn = '';
		
		switch($code){
			case 'O'	: $rtn = '정상'; break;
			case 'R'	: $rtn = '품절'; break;
			case 'S'	: $rtn = '판매중지'; break;
			default		: $rtn = ''; break;
		}
		return $rtn;
	}

	// 노출, 미노출
	function CODE_PRODUCT_USE_YN($code){
		$rtn = '';
		
		switch($code){
			case 'Y'	: $rtn = '노출'; break;
			case 'N'	: $rtn = '미노출'; break;
			default		: $rtn = ''; break;
		}
		
		return $rtn;
	}

	// 재고여부
	function CODE_PRODUCT_STOCK($code){
		$rtn = '';
		
		switch($code){
			case 'F'	: $rtn = '재고해당없음'; break;
			case 'Y'	: $rtn = '재고있음'; break;
			case 'N'	: $rtn = '재고없음'; break;
			default		: $rtn = ''; break;
		}
		
		return $rtn;
	}
	
	// 회원가입유형
	function CODE_MEMBER_JOIN_TP($code){
		$rtn = '';
		
		switch($code){
			case 'S'	: $rtn = '이메일'; break;
			case 'A'	: $rtn = '페이스북'; break;
			case 'U'	: $rtn = '트위터'; break;
			default		: $rtn = ''; break;
		}
		
		return $rtn;
	}
	
	// 신청상태
	function CODE_REQ_STATUS($code){
		$rtn = '';
		
		switch($code){
			case 'R'	: $rtn = '주문신청'; break;
			case 'E'	: $rtn = '신청완료'; break;
			default		: $rtn = ''; break;
		}
		
		return $rtn;
	}
	
	// 주문상태
	function CODE_ORDER_STATUS($code){
		$rtn = '';
		
		switch($code){
			case 'A'	: $rtn = '주문접수'; break;
			case 'B'	: $rtn = '결제확인'; break;
			case 'C'	: $rtn = '상품준비'; break;
			case 'D'	: $rtn = '입고대기'; break;
			case 'E'	: $rtn = '입고완료'; break;
			case 'F'	: $rtn = '출고요청'; break;
			case 'G'	: $rtn = '출고완료'; break;
			default		: $rtn = ''; break;
		}
		
		return $rtn;
	}
	
	// 주문상품입고상태
	function CODE_PURCHASE_STATUS($code){
		$rtn = '';
		
		switch($code){
			case 'P'	: $rtn = '구매중'; break;
			case 'R'	: $rtn = '입고대기'; break;
			case 'E'	: $rtn = '입고확인'; break;
			default		: $rtn = ''; break;
		}
		
		return $rtn;
	}
	
	// 주문취소여부
	function CODE_ORDER_CANCEL_YN($code){
		$rtn = '';
		
		switch($code){
			case 'Y'	: $rtn = '주문취소'; break;
			case 'N'	: $rtn = '주문중'; break;
			default		: $rtn = ''; break;
		}
		
		return $rtn;
	}
	
	// 결제방식
	function CODE_PAYMENT_TP($code){
		$rtn = '';
		
		switch($code){
			case 'CREDIT'	: $rtn = '신용카드'; break;
			case 'CHECK'	: $rtn = '체크카드'; break;
			default		: $rtn = ''; break;
		}
		
		return $rtn;
	}
?>