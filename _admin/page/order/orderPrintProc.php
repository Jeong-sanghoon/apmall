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
	$cal_1 = $cFnc->getReq('cal_1', date('Y-m-d', strtotime('-1 month')));
	$cal_2 = $cFnc->getReq('cal_2', date('Y-m-d'));
	$type = $cFnc->getReq('type', '');
	
	//=====================================================
	//== 도움말 - Set Variables
	//=====================================================
	$cPdo = new cPdo($ARR_DB_INFO, true);
	$month_str = date('M', strtotime($cal_1));
	$month_day = date('md', strtotime($cal_1));
	
	//=====================================================
	//== 도움말 - Start Tran
	//=====================================================
	if($type == 'cod'){
		$arParam = Array();
		$qryOrder = "ORDER BY DATE_FORMAT(C.GOODSSC_DT,'%Y-%m-%d') ASC";
		$qryWhere = "
			WHERE C.GOODSSC_DT BETWEEN ? AND ?
			AND C.STATUS = 'G'
		";
		array_push($arParam, $cal_1 .' 00:00:00');
		array_push($arParam, $cal_2 .' 23:59:59');
		
		$qry = "
			SELECT A.DELIVERY_SEQ, DATE_FORMAT(C.GOODSSC_DT,'%Y-%m-%d') AS GOODSSC_DT_STR, D.ORDERID,  D.NAME, D.DST_ADDR, D.TEL, D.MEMO
			-- , (SUM(C.SUMPRICE) - SUM(C.DEPOSITFEE)) + A.W_PRICE_P AS COD_AMOUNT
			-- , (SUM(C.SUMPRICE_VND) - SUM(C.DEPOSITFEE_VND)) + A.W_PRICE_P_VND AS COD_AMOUNT_VND
			, A.COD AS COD_AMOUNT
			, A.COD_VND AS COD_AMOUNT_VND
			, A.W_PRICE
			, '' AS BL -- B/L 없음
			FROM TB_DELIVERY A
			INNER JOIN TB_DELIVERY_ITEM B ON A.DELIVERY_SEQ = B.DELIVERY_SEQ
			INNER JOIN TB_ORDER_ITEM C ON B.ITEM_SEQ = C.ITEM_SEQ
			INNER JOIN TB_ORDER D ON C.ORDER_SEQ = D.ORDER_SEQ
			". $qryWhere ."
			GROUP BY A.DELIVERY_SEQ, DATE_FORMAT(C.GOODSSC_DT,'%Y-%m-%d'), D.ORDERID,  D.NAME, D.DST_ADDR, D.TEL, D.MEMO, A.W_PRICE
			". $qryOrder ."
		";
		$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
		$result = $cPdo->execQuery('list', $qry, $arParam);
		if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
		$rsList = $result['data'];
		//echo json_encode($rsList);exit;
		
		
		$objPHPExcel = new PHPExcel();
		$sheet = $objPHPExcel->getActiveSheet();
		
		// 셀넓이
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(14.63);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(18.38);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(29.13);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(76.38);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(27.25);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25.63);
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30.13);
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(41.88);
		
		// 글꼴
		//$sheet->getStyle("A1")->getFont()->setName('Arial')->setSize(16)->setBold(true)->getColor()->setRGB('0000FF');
		$sheet->getDefaultStyle()->getFont()->setName('Arial');
		$objPHPExcel->setActiveSheetIndex(0);
		
		// 제목
		$sheet->getRowDimension(1)->setRowHeight(24);
		$sheet->getStyle("A1:H1")->getFont()->setSize(14)->setBold(true)->getColor()->setRGB('FFFFFF');
		$sheet->getStyle("A1:H1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle('A1:H1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('0000FF');
		
		$sheet->setCellValue('A1', 'DATE');
		$sheet->setCellValue('B1', 'OD NO.');
		$sheet->setCellValue('C1', 'NAME');
		$sheet->setCellValue('D1', 'ADDRESS');
		$sheet->setCellValue('E1', 'MOBILE');
		$sheet->setCellValue('F1', 'COD_AMOUNT');
		$sheet->setCellValue('G1', 'NOTE');
		$sheet->setCellValue('H1', 'B/L NO.');
		
		
		$date = '';
		$date_str = '';
		foreach($rsList as $i=>$ds){
			$row = $i + 2;
			
			if($date != $ds['GOODSSC_DT_STR']){
				$date = $ds['GOODSSC_DT_STR'];
				$date_str = $ds['GOODSSC_DT_STR'];
			}
			else{
				$date_str = '';
			}
			
			$sheet->getStyle('A'. $row)->getFont()->setSize(10);
			$sheet->getStyle('B'. $row)->getFont()->setSize(10);
			$sheet->getStyle('C'. $row)->getFont()->setSize(10);
			$sheet->getStyle('D'. $row)->getFont()->setSize(10);
			$sheet->getStyle('E'. $row)->getFont()->setSize(10);
			$sheet->getStyle('F'. $row)->getFont()->setSize(10);
			$sheet->getStyle('G'. $row)->getFont()->setSize(10);
			$sheet->getStyle('H'. $row)->getFont()->setSize(10);
			
			$sheet->getStyle('A'. $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$sheet->getStyle('B'. $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$sheet->getStyle('C'. $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$sheet->getStyle('D'. $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$sheet->getStyle('E'. $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$sheet->getStyle('F'. $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$sheet->getStyle('G'. $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$sheet->getStyle('H'. $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			
			$sheet->setCellValue('A'.$row, $date_str);
			$sheet->setCellValue('B'.$row, $ds['ORDERID']);
			$sheet->setCellValue('C'.$row, $ds['NAME']);
			$sheet->setCellValue('D'.$row, $ds['DST_ADDR']);
			$sheet->setCellValue('E'.$row, $ds['TEL']);
			$sheet->setCellValue('F'.$row, number_format($ds['COD_AMOUNT_VND']) .' ₫');
			$sheet->setCellValue('G'.$row, $ds['MEMO']);
			$sheet->setCellValue('H'.$row, $ds['BL']);
			
		}
		
		//다운
		header('Content-Type: application/vnd.ms-excel');
		//header('Content-Disposition: attachment;filename=Nanoit COD('. $month_str .').xlsx');
		header('Content-Disposition: attachment;filename=Nanoit COD.xlsx');
		header('Cache-Control: max-age=0');
	}
	else{
		$arParam = Array();
		$qryOrder = "ORDER BY A.DELIVERYID, C.ITEMID ASC";
		$qryWhere = "
			WHERE C.GOODSSC_DT BETWEEN ? AND ?
			AND C.STATUS = 'G'
		";
		array_push($arParam, $cal_1 .' 00:00:00');
		array_push($arParam, $cal_2 .' 23:59:59');
		
		$qry = "
			SELECT A.DELIVERYID			-- 주문번호
			, C.ITEMID					-- 상품ID
			, C.INVOICENAME				-- 상품명(영문)
			, C.QTY						-- 주문수량
			, C.SUMPRICE				-- 결제금액( PRICE * 0.93 * 1.22 * 0.001 )
			, ROUND((C.SUMPRICE * 0.093 * 1.22 * 0.001 ), 2 ) AS PRICEUSD	-- usd변환금액
			, D.NAME					-- 주문자명
			, 'VN' AS COUNTRY_CD		-- VN Vietnam
			, E.HSCODE_VALUE			-- HS코드
			, A.WEIGHT					-- 중량
			, A.W_PRICE					-- 운송비
			, ROUND( (A.W_PRICE * 0.093 * 1.22 * 0.001 ), 2 ) AS W_PRICEUSD -- USD운송비
			, 'NS9BUY.VN' AS DOMAIN		-- 도메인 고정
			, '' AS VENDOR				-- 제조자
			, '' AS VENDORCOMPANY		-- 제조자사업자번호
			, '' AS VENDORNUMBER		-- 제조자사업장일련번호
			, '' AS VENDORSERIAL		-- 제조자통관고유부호
			, '' AS POST				-- 제조장소(우편번호)
			, '' AS VENDORSIGN			-- 산업단지부호
			, '' AS VENDORP				-- 인도조건
			, '' AS VENDORWON			-- 운영원화
			, '' AS VENDORINSU			-- 보험료원화
			, '' AS VENDORSPEC			-- 상품성분명
			, '' AS VENDORQTY			-- 주문수량단위
			FROM TB_DELIVERY A
			INNER JOIN TB_DELIVERY_ITEM B ON A.DELIVERY_SEQ = B.DELIVERY_SEQ
			INNER JOIN TB_ORDER_ITEM C ON B.ITEM_SEQ = C.ITEM_SEQ
			INNER JOIN TB_ORDER D ON C.ORDER_SEQ = D.ORDER_SEQ
			LEFT OUTER JOIN TB_HSCODE E ON C.HSCODE_SEQ = E.HSCODE_SEQ
			". $qryWhere ."
			". $qryOrder ."
		";
		$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
		$result = $cPdo->execQuery('list', $qry, $arParam);
		if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
		$rsList = $result['data'];
		//echo json_encode($rsList);exit;
		
		$qry = "
			SELECT A.DELIVERYID			-- 주문번호
			, SUM(ROUND((C.SUMPRICE * 0.093 * 1.22 * 0.001 ), 2 )) AS PRICEUSD	-- usd변환금액
			, A.WEIGHT					-- 중량
			, SUM(ROUND( (A.W_PRICE * 0.093 * 1.22 * 0.001 ), 2 )) AS W_PRICEUSD -- USD운송비
			, COUNT(A.DELIVERYID) AS CNT
			FROM TB_DELIVERY A
			INNER JOIN TB_DELIVERY_ITEM B ON A.DELIVERY_SEQ = B.DELIVERY_SEQ
			INNER JOIN TB_ORDER_ITEM C ON B.ITEM_SEQ = C.ITEM_SEQ
			INNER JOIN TB_ORDER D ON C.ORDER_SEQ = D.ORDER_SEQ
			LEFT OUTER JOIN TB_HSCODE E ON C.HSCODE_SEQ = E.HSCODE_SEQ
			". $qryWhere ."
			GROUP BY A.DELIVERYID, A.WEIGHT
			". $qryOrder ."
		";
		$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
		$result = $cPdo->execQuery('list', $qry, $arParam);
		if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
		$rsGrp = $result['data'];
		//echo json_encode($rsGrp);exit;
		
		foreach($rsGrp as $j=>$grp){
			$cnt = 0;
			
			foreach($rsList as $i=>$ds){
				if($ds['DELIVERYID'] == $grp['DELIVERYID']){
					$cnt++;
					$rsList[$i]['PRICEUSD_TOT'] = $grp['PRICEUSD'];
					$rsList[$i]['WEIGHT'] = 0;
					
					if($cnt == $grp['CNT']){
						$rsList[$i]['WEIGHT'] = number_format($grp['WEIGHT'], 2, '.', ',');
					}
				}
			}
		}
		//echo json_encode($rsList);exit;
		
		$objPHPExcel = new PHPExcel();
		$sheet = $objPHPExcel->getActiveSheet();
		
		// 셀넓이
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(21.88);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(14.63);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(62.38);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(8.75);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(8.75);
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12.63);
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(33.88);
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15.50);
		$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(11.38);
		$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(5.00);
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(5.75);
		$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(14.25);
		$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(10.38);
		$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(16.88);
		$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(21.00);
		$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(18.88);
		$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(18.25);
		$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(12.63);
		$objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(8.63);
		$objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(8.63);
		$objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(10.75);
		$objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(10.75);
		$objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(12.63);
		
		// 글꼴
		//$sheet->getStyle("A1")->getFont()->setName('Arial')->setSize(16)->setBold(true)->getColor()->setRGB('0000FF');
		$sheet->getDefaultStyle()->getFont()->setName('맑은고딕');
		$objPHPExcel->setActiveSheetIndex(0);
		
		// 제목
		$sheet->getRowDimension(1)->setRowHeight(18);
		$sheet->getStyle("A1:W1")->getFont()->setSize(11)->setBold(true)->getColor()->setRGB('FFFFFF');
		$sheet->getStyle("A1:W1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle('A1:W1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('708090');
		
		$arr_column = array();
		array_push($arr_column, '주문번호', '상품ID', '상품명 (영문)', '주문수량', '결제금액');
		array_push($arr_column, '결제통화코드', '구매자상호명', '목적국 국가코드', 'HS코드', '중량');
		array_push($arr_column, '가격', '도메인명', '제조자', '제조자사업자번호', '제조자사업장일련번호');
		array_push($arr_column, '제조자통관고유부호', '제조장소(우편번호)', '산업단지부호', '인도조건', '운임원화');
		array_push($arr_column, '보험료원화', '상품성분명', '주문수량단위');
		
		$i = 0;
		for($c = 'A'; $c <= 'W'; $c++){
			$sheet->setCellValue($c .'1', $arr_column[$i]);
			$i++;
		}
		
		
		foreach($rsList as $i=>$ds){
			$row = $i + 2;
			
			for($c = 'A'; $c <= 'W'; $c++){
				$sheet->getStyle($c . $row)->getFont()->setSize(10);
				
				if($c == 'B' || $c == 'D' || $c == 'E' || $c == 'J' || $c == 'K'){
					$sheet->getStyle($c . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				}
				else if($c == 'C'){
					$sheet->getStyle($c . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				}
				else{
					$sheet->getStyle($c . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				}
			}
			
			$sheet->setCellValue('A'.$row, $ds['DELIVERYID']);
			$sheet->setCellValue('B'.$row, $ds['ITEMID']);
			$sheet->setCellValue('C'.$row, $ds['INVOICENAME']);
			$sheet->setCellValue('D'.$row, number_format($ds['QTY']));
			$sheet->setCellValue('E'.$row, number_format($ds['PRICEUSD_TOT'], 2, '.', ','));
			$sheet->setCellValue('F'.$row, 'USD');
			$sheet->setCellValue('G'.$row, $ds['NAME']);
			$sheet->setCellValue('H'.$row, $ds['COUNTRY_CD']);
			$sheet->setCellValue('I'.$row, $ds['HSCODE_VALUE']);
			$sheet->setCellValue('J'.$row, number_format($ds['WEIGHT'], 2, '.', ','));
			$sheet->setCellValue('K'.$row, number_format($ds['PRICEUSD'], 2, '.', ','));
			$sheet->setCellValue('L'.$row, $ds['DOMAIN']);
			$sheet->setCellValue('M'.$row, $ds['VENDOR']);
			$sheet->setCellValue('N'.$row, $ds['VENDORCOMPANY']);
			$sheet->setCellValue('O'.$row, $ds['VENDORNUMBER']);
			$sheet->setCellValue('P'.$row, $ds['VENDORSERIAL']);
			$sheet->setCellValue('Q'.$row, $ds['POST']);
			$sheet->setCellValue('R'.$row, $ds['VENDORSIGN']);
			$sheet->setCellValue('S'.$row, $ds['VENDORP']);
			$sheet->setCellValue('T'.$row, $ds['VENDORWON']);
			$sheet->setCellValue('U'.$row, $ds['VENDORINSU']);
			$sheet->setCellValue('V'.$row, $ds['VENDORSPEC']);
			$sheet->setCellValue('W'.$row, $ds['VENDORQTY']);
		}
		
		//다운
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename=Orderlist_'. $month_day .'.xlsx');
		//header('Content-Disposition: attachment;filename=Orderlist_.xlsx');
		header('Cache-Control: max-age=0');
	}
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
?>