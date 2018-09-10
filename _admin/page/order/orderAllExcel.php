<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_lib/PHPExcel.php";

	chkSession($url = '/_admin/');

	// ===================================================
	// get parameter
	// ===================================================
	$cal_1 = $cFnc->getReq('cal_1', date('Y-m-d', strtotime('-3 month')));
	$cal_2 = $cFnc->getReq('cal_2', date('Y-m-d'));
	$orderid = $cFnc->getReq('orderid', '');
	$itemid = $cFnc->getReq('itemid', '');
	$deliveryid = $cFnc->getReq('deliveryid', '');
	$productname = $cFnc->getReq('productname', '');
	$status = $cFnc->getReq('status', '');
	$name = $cFnc->getReq('name', '');
	$cancel_yn = $cFnc->getReq('cancel_yn', '');
	$order_cont = $cFnc->getReq('order_cont', 'B.ITEM_SEQ');
	$order_asc = $cFnc->getReq('order_asc', 'DESC');
	$search_list1 = $cFnc->getReq('search_list1', '');

	//=====================================================
	//== 도움말 - Set Variables
	//=====================================================
	$cPdo = new cPdo($ARR_DB_INFO, true);
	
	//=====================================================
	//== 도움말 - Start Tran
	//=====================================================
	$arParam = Array();
	$qryOrder = "ORDER BY ". $order_cont ." ". $order_asc;
	$qryWhere = "
		WHERE A.REG_DT BETWEEN ? AND ?
	";
	array_push($arParam, $cal_1 .' 00:00:00');
	array_push($arParam, $cal_2 .' 23:59:59');


	if($orderid != ''){
		$qryWhere .= " AND A.ORDERID like ?";
		array_push($arParam, '%'. $orderid .'%');		
	}
	if($itemid != ''){
		$qryWhere .= " AND B.ITEMID like ?";
		array_push($arParam, '%'. $itemid .'%');		
	}
	if($deliveryid != ''){
		$qryWhere .= " AND DLV.DELIVERYID like ?";
		array_push($arParam, '%'. $deliveryid .'%');		
	}
	
	if($productname != ''){
		$qryWhere .= " AND B.PRODUCTNAME like ?";
		array_push($arParam, '%'. $productname .'%');		
	}
	if($name != ''){
		$qryWhere .= " AND A.NAME like ?";
		array_push($arParam, '%'. $name .'%');		
	}
	if($status != ''){
		$qryWhere .= " AND B.STATUS = ?";
		array_push($arParam, $status);
		
		if($status == 'END'){
			$qryWhere .= " AND B.GOODSCF_YN = 'Y'";
		}
		else{
			$qryWhere .= " AND B.GOODSCF_YN = 'N'";
		}
	}
	if($cancel_yn != ''){
		$qryWhere .= " AND A.CANCEL_YN = ?";
		array_push($arParam, $cancel_yn);
	}
	
	$qry = "
		SELECT A.ORDER_SEQ, A.ORDERID, A.CANCEL_YN, B.ITEM_SEQ, B.ITEMID, DLV.DELIVERY_SEQ, DLV.DELIVERYID
		, B.`PRODUCTNAME`, B.OPTFIELD, B.OPTVALUE, A.`NAME`, B.QTY, B.PRICE, B.SUMPRICE, B.STATUS, B.GOODSCF_YN
		, B.REG_DT, B.DEPOSIT_DT, B.READY_DT, B.WEARRD_DT, B.WEARSC_DT, B.GOODSRD_DT, B.GOODSSC_DT, B.GOODSCF_DT
		, DLV.REAL_WEIGHT, DLV.REAL_W_PRICE, DLV.HBL_CD
		, OP.`PURCHASE_SEQ`, OP.P_QTY, OP.P_PRICE, OP.P_DELIVERYFEE, OP.P_DISCOUNT, OP.P_OPTFIELD, OP.P_OPTVALUE, OP.P_STATUS, OP.APPROVAL_DT, OP.APPROVAL_NO
		FROM TB_ORDER A
		INNER JOIN TB_ORDER_ITEM B ON B.ORDER_SEQ = A.`ORDER_SEQ`
		LEFT OUTER JOIN (
			SELECT D.DELIVERY_SEQ, D.DELIVERYID, DI.ITEM_SEQ, D.REAL_WEIGHT, D.REAL_W_PRICE, D.HBL_CD
			FROM TB_DELIVERY D
			INNER JOIN TB_DELIVERY_ITEM DI ON DI.DELIVERY_SEQ = D.DELIVERY_SEQ
		) DLV ON DLV.ITEM_SEQ = B.ITEM_SEQ
		LEFT OUTER JOIN TB_ORDER_PURCHASE OP ON OP.`ITEM_SEQ` = B.`ITEM_SEQ`
		". $qryWhere ."
		ORDER BY A.ORDER_SEQ DESC, B.ITEM_SEQ ASC, OP.PURCHASE_SEQ ASC
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('list', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$rsList = $result['data'];
	//echo json_encode($rsList);exit;
	
	
	$objPHPExcel = new PHPExcel();
	$sheet = $objPHPExcel->getActiveSheet();
	
	// 셀넓이
	$arr_c = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P');
	for($i = 0; $i < count($arr_c); $i++){
		$objPHPExcel->getActiveSheet()->getColumnDimension($arr_c[$i])->setWidth(20);
	}
	
	
	// 글꼴
	$sheet->getDefaultStyle()->getFont()->setName('맑은 고딕');
	$objPHPExcel->setActiveSheetIndex(0);
	
	// 제목
	$sheet->getRowDimension(1)->setRowHeight(18);
	$sheet->getStyle("A1:P1")->getFont()->setSize(11)->setBold(true)->getColor()->setRGB('FFFFFF');
	$sheet->getStyle("A1:P1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet->getStyle('A1:P1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('708090');
	
	$sheet->setCellValue('A1', '주문번호');
	$sheet->setCellValue('B1', '아이템번호');
	$sheet->setCellValue('C1', '주문상품');
	$sheet->setCellValue('D1', '옵션필드(값)');
	$sheet->setCellValue('E1', '주문자');
	$sheet->setCellValue('F1', '수량');
	$sheet->setCellValue('G1', '단가');
	$sheet->setCellValue('H1', '결제금액');
	$sheet->setCellValue('I1', '결제승인일');
	$sheet->setCellValue('J1', '승인번호(카드)');
	$sheet->setCellValue('K1', '출고번호');
	$sheet->setCellValue('L1', '실제중량');
	$sheet->setCellValue('M1', '실제운송비');
	$sheet->setCellValue('N1', 'H.B/L NO');
	$sheet->setCellValue('O1', '상태');
	$sheet->setCellValue('P1', '취소여부');
	
	
	$l_orderid = "";
	$l_old_row_orderid = 0;
	$l_new_row_orderid = 0;
	$rowspan = 0;
	$bRow = false;
	
	$l_itemid = "";
	$l_old_row_itemid = 0;
	$l_new_row_itemid = 0;
	$rowspan2 = 0;
	$bRow2 = false;
	
	$l_deliveryid = "";
	$l_old_row_deliveryid = 0;
	$l_new_row_deliveryid = 0;
	$rowspan3 = 0;
	$bRow3 = false;
	
	$real_weight = 0;
	$real_w_price = 0;
	$hbl_cd = '';
	foreach($rsList as $i=>$ds){
		$row = $i + 2;
		
		$sheet->getStyle('D'.$row)->getAlignment()->setWrapText(true);
		
		$sheet->getStyle('A'.$row . ':P'.$row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		
		$sheet->getStyle('C'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
		$sheet->getStyle('D'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle('E'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle('F'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle('G'.$row . ':H'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$sheet->getStyle('I'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle('J'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle('K'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle('L'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle('M'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$sheet->getStyle('N'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle('O'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle('P'.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
		$p_status = CODE_ORDER_STATUS($ds['STATUS']);
		if($ds['STATUS'] == 'G' && $ds['GOODSCF_YN'] == 'Y'){
			$p_status = '구매확인완료';
		}
		
		// order단위
		if ($l_orderid != $ds["ORDERID"]){
			if($l_orderid == ""){
				$l_new_row_orderid = $row;
			}
			else {
				$l_new_row_orderid = $l_old_row_orderid + $rowspan;
			}
			
			if($rowspan >= 1) {
				$sheet->mergeCells('A'. $l_old_row_orderid .':A'. $l_new_row_orderid);
			}
			
			$l_orderid = $ds["ORDERID"];
			$l_old_row_orderid = $row;
			$bRow = true;
			
			$rowspan = 0;
		}
		else {
			$bRow = false;
			$rowspan++;
		}
		
		if(!$bRow){
			$ds['ORDERID'] = '';
		}
		
		
		// item단위
		if ($l_itemid != $ds["ITEMID"]){
			if($l_itemid == ""){
				$l_new_row_itemid = $row;
			}
			else {
				$l_new_row_itemid = $l_old_row_itemid + $rowspan2;
			}
			
			if($rowspan2 >= 1) {
				$sheet->mergeCells('B'. $l_old_row_itemid .':B'. $l_new_row_itemid);
				$sheet->mergeCells('F'. $l_old_row_itemid .':F'. $l_new_row_itemid);
				$sheet->mergeCells('G'. $l_old_row_itemid .':G'. $l_new_row_itemid);
				$sheet->mergeCells('H'. $l_old_row_itemid .':H'. $l_new_row_itemid);
			}
			
			$l_itemid = $ds["ITEMID"];
			$l_old_row_itemid = $row;
			$bRow2 = true;
			
			$rowspan2 = 0;
		}
		else {
			$bRow2 = false;
			$rowspan2++;
		}
		
		if(!$bRow2){
			$ds['ITEMID'] = '';
			$ds['QTY'] = '';
			$ds['PRICE'] = '';
			$ds['SUMPRICE'] = '';
		}
		
		
		// delivery단위
		if($ds['DELIVERYID'] != ''){
			if ($l_deliveryid != $ds["DELIVERYID"]){
				if($l_deliveryid == ""){
					$l_new_row_deliveryid = $row;
				}
				else {
					$l_new_row_deliveryid = $l_old_row_deliveryid + $rowspan3;
				}
				
				if($rowspan3 >= 1) {
					$sheet->mergeCells('K'. $l_old_row_deliveryid .':K'. $l_new_row_deliveryid);
					$sheet->mergeCells('L'. $l_old_row_deliveryid .':L'. $l_new_row_deliveryid);
					$sheet->mergeCells('M'. $l_old_row_deliveryid .':M'. $l_new_row_deliveryid);
					$sheet->mergeCells('N'. $l_old_row_deliveryid .':N'. $l_new_row_deliveryid);
				}
				
				$l_deliveryid = $ds["DELIVERYID"];
				$l_old_row_deliveryid = $row;
				$bRow3 = true;
				
				$rowspan3 = 0;
			}
			else {
				$bRow3 = false;
				$rowspan3++;
			}
		}
		
		if($bRow3){
			$real_weight = $ds['REAL_WEIGHT'];
			$real_w_price = number_format($ds['REAL_W_PRICE']);
			$hbl_cd = $ds['HBL_CD'];
			
			if($real_weight == '') $real_weight = 0;
		}
		else {
			$ds['DELIVERYID'] = '';
			$real_weight = 0;
			$real_w_price = 0;
			$hbl_cd = '';
		}
		
		$sheet->setCellValue('A'.$row, $ds['ORDERID']);
		$sheet->setCellValue('B'.$row, $ds['ITEMID']);
		$sheet->setCellValue('C'.$row, $ds['PRODUCTNAME']);
		$sheet->setCellValue('D'.$row, $ds['OPTFIELD'] . chr(10) .'('.  $ds['OPTVALUE'] .')');
		$sheet->setCellValue('E'.$row, $ds['NAME']);
		$sheet->setCellValue('F'.$row, number_format($ds['QTY']));
		$sheet->setCellValue('G'.$row, number_format($ds['PRICE']));
		$sheet->setCellValue('H'.$row, number_format($ds['SUMPRICE']));
		$sheet->setCellValue('I'.$row, $ds['APPROVAL_DT']);
		$sheet->setCellValue('J'.$row, $ds['APPROVAL_NO']);
		$sheet->setCellValue('K'.$row, $ds['DELIVERYID']);
		$sheet->setCellValue('L'.$row, $real_weight);
		$sheet->setCellValue('M'.$row, $real_w_price);
		$sheet->setCellValue('N'.$row, $hbl_cd);
		$sheet->setCellValue('O'.$row, $p_status);
		$sheet->setCellValue('P'.$row, CODE_ORDER_CANCEL_YN($ds['CANCEL_YN']));
	}
	
	if($rowspan >= 1) {
		$l_new_row_orderid = $l_old_row_orderid + $rowspan;
		$sheet->mergeCells('A'. $l_old_row_orderid .':A'. $l_new_row_orderid);
	}
	
	if($rowspan2 >= 1) {
		$l_new_row_itemid = $l_old_row_itemid + $rowspan2;
		$sheet->mergeCells('B'. $l_old_row_itemid .':B'. $l_new_row_itemid);
		$sheet->mergeCells('F'. $l_old_row_itemid .':F'. $l_new_row_itemid);
		$sheet->mergeCells('G'. $l_old_row_itemid .':G'. $l_new_row_itemid);
		$sheet->mergeCells('H'. $l_old_row_itemid .':H'. $l_new_row_itemid);
	}
	
	if($rowspan3 >= 1) {
		$l_new_row_deliveryid = $l_old_row_deliveryid + $rowspan3;
		$sheet->mergeCells('K'. $l_old_row_deliveryid .':K'. $l_new_row_deliveryid);
		$sheet->mergeCells('L'. $l_old_row_deliveryid .':L'. $l_new_row_deliveryid);
		$sheet->mergeCells('M'. $l_old_row_deliveryid .':M'. $l_new_row_deliveryid);
		$sheet->mergeCells('N'. $l_old_row_deliveryid .':N'. $l_new_row_deliveryid);
	}
	
	//다운
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename=order_all_'. date('YmdHis') .'.xlsx');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
?>