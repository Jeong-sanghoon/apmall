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
	$name = $cFnc->getReq('name', '');
	$order_cont = $cFnc->getReq('order_cont', 'A.ORDER_SEQ');
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
	$qryOrder = "ORDER BY D.ORDER_SEQ, C.DEPOSIT_DT, C.GOODSSC_DT";
	$qryWhere = "WHERE A.REG_DT BETWEEN ? AND ?";

	array_push($arParam, $cal_1 .' 00:00:00');
	array_push($arParam, $cal_2 .' 23:59:59');

	if($orderid != ''){
		$qryWhere .= " AND D.ORDERID = ?";
		array_push($arParam, $orderid);
	}
	if($name != ''){
		$qryWhere .= " AND D.NAME = ?";
		array_push($arParam, $name);
	}

	$qry = "
		SELECT C.REG_DT, C.DEPOSIT_DT, C.GOODSSC_DT, D.ORDERID
		, C.WEARSC_DT, C.PRODUCTNAME, C.OPTVALUE, C.OPTFIELD
		, C.QTY, C.PRICE, OP.P_DELIVERYFEE, OP.P_DISCOUNT, (OP.P_PRICESUM-OP.P_DELIVERYFEE) AS PRODUCTSUM, OP.P_PRICESUM
		, '' AS INVOICE_PRICE
		, IFNULL(C.SUMPRICE_VND,0) AS SUMPRICE_VND
		, A.W_PRICE_P_VND, C.DEPOSITFEE_VND, (C.SUMPRICE_VND-C.DEPOSITFEE_VND) AS B_FEE
		, D.NAME, A.WEIGHT
		, A.REAL_WEIGHT AS CJWEIGHT
		, A.REAL_W_PRICE AS CJPRICE
		, A.HBL_CD AS HBLNO
		, A.MEMO AS MEMO
		, D.MEMO AS MEMOD
		FROM TB_DELIVERY A
		INNER JOIN TB_DELIVERY_ITEM B ON A.DELIVERY_SEQ = B.DELIVERY_SEQ
		INNER JOIN TB_ORDER_ITEM C ON B.ITEM_SEQ = C.ITEM_SEQ
		INNER JOIN TB_ORDER D ON C.ORDER_SEQ = D.ORDER_SEQ
		INNER JOIN (
			SELECT ITEM_SEQ, SUM(P_PRICE) AS P_PRICE, SUM(P_DELIVERYFEE) AS P_DELIVERYFEE, SUM(P_PRICESUM) AS P_PRICESUM, SUM(P_DISCOUNT) AS P_DISCOUNT
			FROM TB_ORDER_PURCHASE OP
			WHERE P_STATUS = 'E'
			GROUP BY OP.ITEM_SEQ
		) OP ON OP.ITEM_SEQ = C.ITEM_SEQ
		".$qryWhere."
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->getCntExec($qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$dsCount = $result['data'];			// 전체카운트

	if(is_array($dsCount)){
		$nTotalCnt = $dsCount["total"];
		$result = $cPdo->execQuery('list', $qry, $arParam);
		$rsList = $result['data'];
	}

	$objPHPExcel = new PHPExcel();
	$sheet = $objPHPExcel->getActiveSheet();

	$arr_c = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB');

	// 셀넓이
	for($i = 0; $i < count($arr_c); $i++){
		if($i == 0){
			$objPHPExcel->getActiveSheet()->getColumnDimension($arr_c[$i])->setWidth(8);
		}
		else{
			$objPHPExcel->getActiveSheet()->getColumnDimension($arr_c[$i])->setWidth(20);
		}
	}
	/*
	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setWidth(20);
	$objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setWidth(20);
	*/


	// 글꼴
	//$sheet->getStyle("A1")->getFont()->setName('Arial')->setSize(16)->setBold(true)->getColor()->setRGB('0000FF');
	$sheet->getDefaultStyle()->getFont()->setName('맑은고딕');
	$objPHPExcel->setActiveSheetIndex(0);

	// 제목
	$sheet->getRowDimension(1)->setRowHeight(18);
	$sheet->getStyle("A1:AB1")->getFont()->setSize(11)->setBold(true)->getColor()->setRGB('FFFFFF');
	$sheet->getStyle("A1:AB1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet->getStyle('A1:AB1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('708090');

	$arr_column = array();
	array_push($arr_column, 'NO', '주문일자', '결제일자', '수출일자', '주문번호');
	array_push($arr_column, '입고날짜', '품목', '옵션값', '옵션필드', '수량');
	array_push($arr_column, '단가', '택배비', '할인금액', '상품구매가', '결제금액');
	array_push($arr_column, '인보이스단가', '구매가격(VND)', '운송비', '선금', '잔금');
	array_push($arr_column, '합계', '받는사람', '무게', 'CJ측정무게', 'CJ운송비');
	array_push($arr_column, 'H,B/LNO', '수출신고번호', '비고');

	for($i = 0; $i < count($arr_c); $i++){
		$sheet->setCellValue($arr_c[$i] .'1', $arr_column[$i]);
	}
	
	
	$arr_border = array(
		'borders' => array(
			'allborders' => array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			)	            
		)
	);
	$l_orderid = "";
	$l_old_row = 0;
	$l_new_row = 0;
	$rowspan = 0;
	$sumprice_vnd_sum		= 0;
	$w_price_p_vnd_sum		= 0;
	$depositfee_vnd_sum		= 0;
	$b_fee_vnd_sum			= 0;
	$bRow = false;
	if($nTotalCnt > 0){
		foreach($rsList as $i=>$ds){
			$row = $i + 2;
			
			$sheet->getStyle("A". $row .":AB". $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$sheet->getStyle("A". $row .":AB". $row)->applyFromArray($arr_border);
			
			$PageS = $nTotalCnt - $i;
			
			if ($l_orderid != $ds["ORDERID"]){
				if($l_orderid == ''){
					$l_new_row = $row;
				}
				else{
					$l_new_row = $l_old_row + $rowspan;
				}
				
				if($rowspan >= 1) {
					$sheet->mergeCells('B'. $l_old_row .':B'. $l_new_row);
					$sheet->mergeCells('C'. $l_old_row .':C'. $l_new_row);
					$sheet->mergeCells('D'. $l_old_row .':D'. $l_new_row);
					$sheet->mergeCells('E'. $l_old_row .':E'. $l_new_row);
					$sheet->mergeCells('F'. $l_old_row .':F'. $l_new_row);
					$sheet->mergeCells('Q'. $l_old_row .':Q'. $l_new_row);
					$sheet->mergeCells('R'. $l_old_row .':R'. $l_new_row);
					$sheet->mergeCells('S'. $l_old_row .':S'. $l_new_row);
					$sheet->mergeCells('T'. $l_old_row .':T'. $l_new_row);
					$sheet->mergeCells('U'. $l_old_row .':U'. $l_new_row);
					$sheet->mergeCells('V'. $l_old_row .':V'. $l_new_row);
					$sheet->mergeCells('W'. $l_old_row .':W'. $l_new_row);
					$sheet->mergeCells('X'. $l_old_row .':X'. $l_new_row);
					$sheet->mergeCells('Y'. $l_old_row .':Y'. $l_new_row);
					$sheet->mergeCells('Z'. $l_old_row .':Z'. $l_new_row);
					$sheet->mergeCells('AA'. $l_old_row .':AA'. $l_new_row);
					$sheet->mergeCells('AB'. $l_old_row .':AB'. $l_new_row);
				}
				
				$b_fee_vnd_sum = floor(($b_fee_vnd_sum + 999) / 1000) * 1000;
				$totalprice_sum = ($depositfee_vnd_sum + $b_fee_vnd_sum) - $w_price_p_vnd_sum;
				$sheet->setCellValue('Q'.$l_old_row, number_format($sumprice_vnd_sum));
				$sheet->setCellValue('R'.$l_old_row, number_format($w_price_p_vnd_sum));
				$sheet->setCellValue('S'.$l_old_row, number_format($depositfee_vnd_sum));
				$sheet->setCellValue('T'.$l_old_row, number_format($b_fee_vnd_sum));
				$sheet->setCellValue('U'.$l_old_row, number_format($totalprice_sum));
				
				$l_orderid = $ds["ORDERID"];
				$l_old_row = $row;
				$bRow = true;
				
				$rowspan = 0;
			} else {
				$bRow = false;
				$rowspan++;
			}
			
			if($bRow){
				$ds['REG_DT_STR']		= $ds['REG_DT'] == '' ? '' : substr($ds['REG_DT'], 0, 10);
				$ds['DEPOSIT_DT_STR']	= $ds['DEPOSIT_DT'] == '' ? '' : substr($ds['DEPOSIT_DT'], 0, 10);
				$ds['GOODSSC_DT_STR']	= $ds['GOODSSC_DT'] == '' ? '' : substr($ds['GOODSSC_DT'], 0, 10);
				$ds['WEARSC_DT_STR']	= $ds['WEARSC_DT'] == '' ? '' : substr($ds['WEARSC_DT'], 0, 10);

				$sumprice_vnd_sum		= $ds['SUMPRICE_VND'];
				$w_price_p_vnd_sum		= $ds['W_PRICE_P_VND'];
				$depositfee_vnd_sum		= $ds['DEPOSITFEE_VND'];
				$b_fee_vnd_sum			= $ds['W_PRICE_P_VND'] + $ds['B_FEE'];
			} else {
				$ds['REG_DT_STR']		= "";
				$ds['DEPOSIT_DT_STR']	= "";
				$ds['GOODSSC_DT_STR']	= "";
				$ds['WEARSC_DT_STR']	= "";
				$ds['ORDERID']			= "";
				$ds["NAME"]				= "";
				$ds["W_PRICE_P_VND"]	= "";
				$ds["WEIGHT"]	= "";
				
				$sumprice_vnd_sum		+= $ds['SUMPRICE_VND'];
				$w_price_p_vnd_sum		+= $ds['W_PRICE_P_VND'];
				$depositfee_vnd_sum		+= $ds['DEPOSITFEE_VND'];
				$b_fee_vnd_sum			+= $ds['B_FEE'];
			}
			
			$sheet->setCellValue('A'.$row, $PageS);
			$sheet->setCellValue('B'.$row, $ds['REG_DT_STR']);
			$sheet->setCellValue('C'.$row, $ds['DEPOSIT_DT_STR']);
			$sheet->setCellValue('D'.$row, $ds['GOODSSC_DT_STR']);
			$sheet->setCellValue('E'.$row, $ds['ORDERID']);
			$sheet->setCellValue('F'.$row, $ds['WEARSC_DT_STR']);
			$sheet->setCellValue('G'.$row, $ds['PRODUCTNAME']);
			$sheet->setCellValue('H'.$row, $ds['OPTVALUE']);
			$sheet->setCellValue('I'.$row, $ds['OPTFIELD']);
			$sheet->setCellValue('J'.$row, number_format($ds['QTY']));
			$sheet->setCellValue('K'.$row, number_format($ds['PRICE']));
			$sheet->setCellValue('L'.$row, number_format($ds['P_DELIVERYFEE']));
			$sheet->setCellValue('M'.$row, number_format($ds['P_DISCOUNT']));
			$sheet->setCellValue('N'.$row, number_format($ds['PRODUCTSUM']));
			$sheet->setCellValue('O'.$row, number_format($ds['P_PRICESUM']));
			$sheet->setCellValue('P'.$row, number_format($ds['INVOICE_PRICE']));
			
			$sheet->setCellValue('V'.$row, $ds['NAME']);
			$sheet->setCellValue('W'.$row, $ds['WEIGHT']);
			$sheet->setCellValue('X'.$row, $ds['CJWEIGHT']);
			$sheet->setCellValue('Y'.$row, number_format($ds['CJPRICE']));
			$sheet->setCellValue('Z'.$row, $ds['HBLNO']);
			$sheet->setCellValue('AA'.$row, '');
			$sheet->setCellValue('AB'.$row, $ds['MEMO']);
		}
		
		if($rowspan >= 1) {
			$l_new_row = $l_old_row + $rowspan;
			$sheet->mergeCells('B'. $l_old_row .':B'. $l_new_row);
			$sheet->mergeCells('C'. $l_old_row .':C'. $l_new_row);
			$sheet->mergeCells('D'. $l_old_row .':D'. $l_new_row);
			$sheet->mergeCells('E'. $l_old_row .':E'. $l_new_row);
			$sheet->mergeCells('F'. $l_old_row .':F'. $l_new_row);
			$sheet->mergeCells('Q'. $l_old_row .':Q'. $l_new_row);
			$sheet->mergeCells('R'. $l_old_row .':R'. $l_new_row);
			$sheet->mergeCells('S'. $l_old_row .':S'. $l_new_row);
			$sheet->mergeCells('T'. $l_old_row .':T'. $l_new_row);
			$sheet->mergeCells('U'. $l_old_row .':U'. $l_new_row);
			$sheet->mergeCells('V'. $l_old_row .':V'. $l_new_row);
			$sheet->mergeCells('W'. $l_old_row .':W'. $l_new_row);
			$sheet->mergeCells('X'. $l_old_row .':X'. $l_new_row);
			$sheet->mergeCells('Y'. $l_old_row .':Y'. $l_new_row);
			$sheet->mergeCells('Z'. $l_old_row .':Z'. $l_new_row);
			$sheet->mergeCells('AA'. $l_old_row .':AA'. $l_new_row);
			$sheet->mergeCells('AB'. $l_old_row .':AB'. $l_new_row);
		}
		
		$b_fee_vnd_sum = floor(($b_fee_vnd_sum + 999) / 1000) * 1000;
		$totalprice_sum = ($depositfee_vnd_sum + $b_fee_vnd_sum) - $w_price_p_vnd_sum;
		$sheet->setCellValue('Q'.$l_old_row, number_format($sumprice_vnd_sum));
		$sheet->setCellValue('R'.$l_old_row, number_format($w_price_p_vnd_sum));
		$sheet->setCellValue('S'.$l_old_row, number_format($depositfee_vnd_sum));
		$sheet->setCellValue('T'.$l_old_row, number_format($b_fee_vnd_sum));
		$sheet->setCellValue('U'.$l_old_row, number_format($totalprice_sum));
	}

	//다운
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename=adjustment_'. $cal_1 .'_'. $cal_2 .'.xlsx');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
?>