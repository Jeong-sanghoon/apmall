<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_lib/PHPExcel.php";
	
	chkSession($url = '/_admin/');
	
	$delivery_seq = $cFnc->getReq('delivery_seq', '');
	
	
	$cPdo = new cPdo($ARR_DB_INFO, true);
	
	
	// 출고정보조회
	$arParam = array();
	array_push($arParam, $delivery_seq);
	$qry = "SELECT * FROM TB_DELIVERY WHERE DELIVERY_SEQ = ?";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('data', $qry, $arParam);
	$ds = $result['data'];
	
	
	// 아이템정보조회
	$arParam = Array();
	array_push($arParam, $delivery_seq);
	$qry = "
		SELECT C.DST_ADDR, C.TEL, C.NAME
		, B.ITEM_SEQ, B.ITEMID, B.PRODUCTNAME, B.INVOICENAME, B.QTY, B.PRICE, B.SUMPRICE, B.GOODSSC_DT
		FROM TB_DELIVERY_ITEM A
		INNER JOIN TB_ORDER_ITEM B ON B.ITEM_SEQ = A.ITEM_SEQ
		INNER JOIN TB_ORDER C ON C.ORDER_SEQ = B.ORDER_SEQ
		WHERE A.DELIVERY_SEQ = ?
		ORDER BY B.ITEM_SEQ
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->execQuery('list', $qry, $arParam);
	if($result['status'] == 0) throw new Exception($result['msg'], 1001);			// 시스템에러
	$rs = $result['data'];
	//echo json_encode($rs);exit;
	
	//$cFnc->echoQry($qry, $arParam);		
	$objPHPExcel = new PHPExcel();
	$sheet = $objPHPExcel->getActiveSheet();


	// 글꼴
	$sheet->getDefaultStyle()->getFont()->setName('굴림');
	$objPHPExcel->setActiveSheetIndex(0);
	$sheet->setTitle('CI');	

	$sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
	$sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
	$sheet->getPageSetup()->setFitToPage(true);
	$sheet->getPageSetup()->setFitToWidth(1);
	$sheet->getPageSetup()->setFitToHeight(0);


	$sheet2 = $objPHPExcel->createSheet(1); 
	$sheet2->getDefaultStyle()->getFont()->setName('굴림');			
	$sheet2->setTitle('PL');	


	$sheet2->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
	$sheet2->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
	$sheet2->getPageSetup()->setFitToPage(true);
	$sheet2->getPageSetup()->setFitToWidth(1);
	$sheet2->getPageSetup()->setFitToHeight(0);


	$aColSize = Array();
	array_push($aColSize, "15.89");
	array_push($aColSize, "41.89");
	array_push($aColSize, "12");
	array_push($aColSize, "14");
	array_push($aColSize, "15");
	
	
	/////////////////////////////////////////////
	// CI
	// COMMERCIAL INVOICE
	/////////////////////////////////////////////
	$i = 0;
	foreach(range('A', 'E') as $cID){
		// echo $aColSize[$i];
		$sheet->getColumnDimension($cID)->setWidth($aColSize[$i]);
		$i++;
	}

	$sheet->getStyle('A1:E64')->applyFromArray(
	    array(
	        'borders' => array(
	            'allborders' => array(
	                'style' => PHPExcel_Style_Border::BORDER_NONE	                
	            )
	        )
	    )
	);
	
	
	// 제목	
	//$sheet->mergeCells('A1:E1');
	$sheet->setCellValue('A1', '             COMMERCIAL INVOICE');
	$sheet->getStyle("A1")->getFont()->setName('Arial')->setSize(16)->setBold(true)->getColor()->setRGB('0000FF');
	$sheet->getRowDimension(1)->setRowHeight(-1);

	// Blank
	$sheet->setCellValue('A2', '');
	$sheet->getStyle("A2")->getFont()->setSize(10);	
	$sheet->getRowDimension(2)->setRowHeight(10);
	
	
	// 1. Shipper / Exporter
	$sheet->setCellValue('A3', '1)Shipper/Expoter');
	$sheet->getStyle("A3")->getFont()->setName('Arial')->setSize(8)->setBold(true)->getColor()->setRGB('0000FF');;		
    $sheet->getStyle("A3")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet->getStyle('A3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	
	
	// 8. No. & date of Invoice
	$sheet->setCellValue('C3', '8)No & date of invoice');
	$sheet->getStyle('C3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('C3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet->getStyle("C3")->getFont()->setName('Arial')->setSize(8)->setBold(true)->getColor()->setRGB('0000FF');;		
	$sheet->getRowDimension(3)->setRowHeight(12);
	$sheet->getStyle('A3:E3')->applyFromArray(
	    array(
	        'borders' => array(
	            'top' => array(
	                'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);
	
	
	// 1. Shipper / Exporter
	$strA1 = "Nano IT Co., Ltd.".chr(10). "2F, 38-3, Chengdugot-Gil, Seocho-Gu".chr(10). "Seoul, Korea".chr(10). "T : +82-2-6342-0114";
	$sheet->mergeCells('A4:B8');
	$sheet->setCellValue('A4', $strA1);	
	$sheet->getRowDimension(4)->setRowHeight(12);
	$sheet->getRowDimension(5)->setRowHeight(12);
	$sheet->getRowDimension(6)->setRowHeight(12);
	$sheet->getRowDimension(7)->setRowHeight(12);
	$sheet->getStyle('A4:B8')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
	$sheet->getStyle('A4')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
	$sheet->getStyle('A4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
	$sheet->getStyle("A4")->getFont()->setName('Tahoma')->setSize(10)->setBold(true);		
	$sheet->getStyle('B4:B8')->applyFromArray(
	    array(
	        'borders' => array(
	            'right' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);


	// 8. No. & date of Invoice
	$sheet->mergeCells('C5:D5');
	$sheet->setCellValue('C5', $ds['DELIVERYID']);	
	$sheet->getStyle('C5:D5')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
	$sheet->getStyle('C5')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('C5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
	$sheet->getStyle("C5")->getFont()->setName('Tahoma')->setSize(10);	
	
	$goodssc_dt = '';
	if($rs[0]['GOODSSC_DT'] != '') $goodssc_dt = substr($rs[0]['GOODSSC_DT'], 0, 10);
	
	$sheet->setCellValue('E5', $goodssc_dt);
	$sheet->getStyle('E5')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
	$sheet->getStyle('E5')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('E5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	$sheet->getStyle("E5")->getFont()->setName('Tahoma')->setSize(10);		
	$sheet->getStyle('A8:E8')->applyFromArray(
	    array(
	        'borders' => array(
	            'bottom' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);

	$sheet->getStyle('C5:E5')->applyFromArray(
	    array(
	        'borders' => array(
	            'bottom' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);
	
	
	// 9. No. & date of L/C
	$sheet->setCellValue('C6', '9)No & date of L/C');		
	$sheet->getStyle('C6')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('C6')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
	$sheet->getStyle("C6")->getFont()->setName('Arial')->setSize(8)->setBold(true);
	$sheet->getStyle("C7:E8")->getFont()->setName('Arial')->setSize(8);
	$sheet->getStyle("C7:E8")->getFont()->setName('Tahoma')->setSize(10);


	// 2. Consignee
	$sheet->setCellValue('A9', '2)Consignee');
	$sheet->getStyle("A9")->getFont()->setName('Arial')->setSize(8)->setBold(true);
    $sheet->getStyle("A9")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet->getStyle('A9')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	
	
	// 10)L/C issuing bank
	$sheet->setCellValue('C9', '10)L/C issuing bank');
	$sheet->getStyle('C9')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('C9')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet->getStyle("C9")->getFont()->setName('Arial')->setSize(8)->setBold(true);		
	$sheet->getRowDimension(9)->setRowHeight(12);
	
	
	// 2. Consignee
	$sheet->mergeCells('A10:B15');
	$sheet->setCellValue('A10', $rs[0]['NAME'] .chr(10). $rs[0]['DST_ADDR'] .chr(10). 'M : '. $rs[0]['TEL']);
	$sheet->getRowDimension(10)->setRowHeight(12);
	$sheet->getRowDimension(11)->setRowHeight(12);
	$sheet->getRowDimension(12)->setRowHeight(12);
	$sheet->getRowDimension(13)->setRowHeight(12);
	$sheet->getRowDimension(14)->setRowHeight(12);
	$sheet->getRowDimension(15)->setRowHeight(12);
	$sheet->getStyle('A10:B15')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
	$sheet->getStyle('A10')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
	$sheet->getStyle('A10')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
	$sheet->getStyle("A10")->getFont()->setName('Tahoma')->setSize(10)->setBold(true);
	$sheet->getStyle('B10:B15')->applyFromArray(
	    array(
	        'borders' => array(
	            'right' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);
	
	$sheet->getStyle('A15:B15')->applyFromArray(
	    array(
	        'borders' => array(
	            'bottom' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);
	
	$sheet->getStyle('C11:E11')->applyFromArray(
	    array(
	        'borders' => array(
	            'bottom' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);
	
	
	// 11)Remarks
	$sheet->setCellValue('C12', '11)Remarks');
	$sheet->getStyle('C12')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('C12')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet->getStyle("C12")->getFont()->setName('Arial')->setSize(8)->setBold(true);	
	
	$sheet->setCellValue('C14', '* FOB INCHEON');
	$sheet->setCellValue('C15', '* Country of Origin : SOUTH KOREA');
	$sheet->getStyle('C14:E15')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
	$sheet->getStyle('C14:E15')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('C14:E15')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
	$sheet->getStyle("A10")->getFont()->setName('Tahoma')->setSize(10)->setBold(true);			
	// ========================================
	// step 2 End
	// ========================================


	// ========================================
	// step 3 Start
	// ========================================
	// 3)Nority Party
	$sheet->setCellValue('A16', '3)Nority Party');
	$sheet->getStyle("A16")->getFont()->setName('Arial')->setSize(8)->setBold(true);
    $sheet->getStyle("A16")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet->getStyle('A16')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	
	$strA3 = "Dinh Phi".chr(10). "605A Diamond Plaza, 34 Le Duan, Quan 1, HCM".chr(10). "M : +84908460010";
	$sheet->mergeCells('A17:B20');
	$sheet->setCellValue('A17', $strA3);	
	$sheet->getRowDimension(17)->setRowHeight(12);
	$sheet->getRowDimension(18)->setRowHeight(12);
	$sheet->getRowDimension(19)->setRowHeight(12);
	$sheet->getRowDimension(20)->setRowHeight(12);
	$sheet->getStyle('A17:B20')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
	$sheet->getStyle('A17')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
	$sheet->getStyle('A17')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
	$sheet->getStyle("A17")->getFont()->setName('Tahoma')->setSize(10)->setBold(true);
	
	$sheet->getStyle('A16:B44')->applyFromArray(
	    array(
	        'borders' => array(
	            'right' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);

	$sheet->getStyle('A20:B20')->applyFromArray(
	    array(
	        'borders' => array(
	            'bottom' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);
	
	
	// 4)Port of Loading
	$sheet->setCellValue('A21', '4)Port of Loading');
	$sheet->getStyle("A21")->getFont()->setName('Arial')->setSize(8)->setBold(true);
    $sheet->getStyle("A21")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet->getStyle('A21')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

	$sheet->getStyle("A22:A23")->getFont()->setName('Tahoma')->setSize(10);
    $sheet->getStyle("A22:A23")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet->getStyle('A22:A23')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

	$sheet->setCellValue('A23', 'Incheon,KOREA');
	$sheet->getStyle('A21:A23')->applyFromArray(
	    array(
	        'borders' => array(
	            'bottom' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            ),
	            'right' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);


	// 5)Final Destination
	$sheet->setCellValue('B21', '5)Final Destination');
	$sheet->getStyle("B21")->getFont()->setName('Arial')->setSize(8)->setBold(true);
    $sheet->getStyle("B21")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet->getStyle('B21')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle("B22:B23")->getFont()->setName('Tahoma')->setSize(10);
    $sheet->getStyle("B22:B23")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet->getStyle('B22:B23')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('B23')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	
	$sheet->setCellValue('B23', 'HOCHIEMINH, VEITNAM');
	$sheet->getStyle('B23')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
	$sheet->getStyle('B22:B23')->applyFromArray(
	    array(
	        'borders' => array(
	            'bottom' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            ),
	            'right' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);

	$sheet->setCellValue('A24', '6)Carrier');
	$sheet->getStyle("A24")->getFont()->setName('Arial')->setSize(8)->setBold(true);
    $sheet->getStyle("A24")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet->getStyle('A24')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle("A25:A26")->getFont()->setName('Tahoma')->setSize(10);
    $sheet->getStyle("A25:A26")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet->getStyle('A25:A26')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('A26')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	$sheet->setCellValue('A26', 'By air');
	$sheet->getStyle('A26')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
	$sheet->getStyle('A24:A26')->applyFromArray(
	    array(
	        'borders' => array(
	            'bottom' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            ),
	            'right' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);


	// ==============
	// 7. START
	// ==============
	$sheet->setCellValue('B24', '7)Sailing on or about');
	$sheet->getStyle("B24")->getFont()->setName('Arial')->setSize(8)->setBold(true);
    $sheet->getStyle("B24")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet->getStyle('B24')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle("B25:B26")->getFont()->setName('Tahoma')->setSize(10);
    $sheet->getStyle("B25:B26")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet->getStyle('B25:B26')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('B26')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet->getStyle('B24:B26')->applyFromArray(
	    array(
	        'borders' => array(
	            'bottom' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            ),
	            'right' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);
	// ==============
	// 7. END
	// ==============


	// =======================
	// 12. Pallet No Start 
	// =======================
	$sheet->setCellValue('A27', '12)Pallet no.');
	$sheet->getStyle("A27")->getFont()->setName('Arial')->setSize(8)->setBold(true);
    $sheet->getStyle("A27")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet->getStyle('A27')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	
	$sheet->setCellValue('A28', '1');
	$sheet->getStyle("A28:A29")->getFont()->setName('Arial')->setSize(8);
    $sheet->getStyle("A28:A29")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet->getStyle('A28:A29')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('A28:A29')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet->getStyle('A27:A29')->applyFromArray(
	    array(
	        'borders' => array(
	            'bottom' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            ),
	            'right' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);
	// =======================
	// 12. Pallet No End 
	// =======================


	// =======================
	// 13. Description of Goods
	// =======================
	$sheet->setCellValue('B27', '13)Description of Goods');
	$sheet->getStyle("B27")->getFont()->setName('Arial')->setSize(8)->setBold(true);
    $sheet->getStyle("B27")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet->getStyle('B27')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle("B28:B29")->getFont()->setName('Arial')->setSize(8);
    $sheet->getStyle("B28:B29")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet->getStyle('B28:B29')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('B28:B29')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet->getStyle('B27:B29')->applyFromArray(
	    array(
	        'borders' => array(
	            'bottom' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            ),
	            'right' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);
	// =======================
	// 13. Description of Goods end 
	// =======================



	// =======================
	// 14. Quantity start
	// =======================
	$sheet->setCellValue('C27', '14)Quantity');
	$sheet->getStyle("C27")->getFont()->setName('Arial')->setSize(8)->setBold(true);
    $sheet->getStyle("C27")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet->getStyle('C27')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('D27')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet->getStyle("C28:C29")->getFont()->setName('Tahoma')->setSize(10);
    $sheet->getStyle("C28:C29")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet->getStyle('C28:C29')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('C28:C29')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet->getStyle('C27:C29')->applyFromArray(
	    array(
	        'borders' => array(
	        	'top' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            ),
	            'bottom' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            ),
	            'right' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);	
	// =======================
	// 14. Quantity end 
	// =======================


	// =======================
	// 15. Unit Price(USD) start
	// =======================
	$sheet->setCellValue('D27', '15)Unit price(USD)');
	$sheet->getStyle("D27")->getFont()->setName('Arial')->setSize(8)->setBold(true);
    $sheet->getStyle("D27")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet->getStyle('D27')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('D27')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	$sheet->getStyle("D28:D29")->getFont()->setName('Tahoma')->setSize(10);
    $sheet->getStyle("D28:D29")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet->getStyle('D28:D29')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('D28:D29')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet->getStyle('D27:D29')->applyFromArray(
	    array(
	        'borders' => array(
	        	'top' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            ),
	            'bottom' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            ),
	            'right' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);	
	// =======================
	// 15. UnitPrice end
	// =======================



	// =======================
	// 16. Amount start
	// =======================
	$sheet->setCellValue('E27', '16)Amount(USD)');
	$sheet->getStyle("E27")->getFont()->setName('Arial')->setSize(8)->setBold(true);
    $sheet->getStyle("E27")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet->getStyle('E27')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('E27')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	$sheet->getStyle("E28:E29")->getFont()->setName('Tahoma')->setSize(10);
    $sheet->getStyle("E28:E29")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet->getStyle('E28:E29')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('E28:E29')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet->getStyle('E27:E29')->applyFromArray(
	    array(
	        'borders' => array(
	        	'top' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            ),
	            'bottom' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            ),
	            'right' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);	
	// =======================
	// 16. Amount End
	// =======================


	// =======================
	// 17. ItemList Start
	// =======================
	$rowcnt = count($rs) + 29;
	if($rowcnt < 44) $rowcnt = 44;
	
	$sheet->setCellValue('E27', '16)Amount(USD)');
	$sheet->getStyle("E27")->getFont()->setName('Arial')->setSize(8)->setBold(true);
    $sheet->getStyle("E27")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet->getStyle('E27')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('E27')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	$sheet->getStyle("E28:E29")->getFont()->setName('Tahoma')->setSize(10);
    $sheet->getStyle("E28:E29")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet->getStyle('E28:E29')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('E28:E29')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet->getStyle('A30:E'. $rowcnt)->applyFromArray(
	    array(
	        'borders' => array(
	        	'allborders' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )	            
	        )
	    )
	);
	$sheet->getStyle('A30:E30')->applyFromArray(
	    array(
	        'borders' => array(
	        	'top' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THICK,
	                'color' => array('rgb' => '000000')
	            )	            
	        )
	    )
	);	

	$sumQty = 0;
	$sumAmount = 0;
	$idx = 0;
	for($i = 30; $i <= $rowcnt; $i++){
		$dsItem = $rs[$idx];
		
		if(is_array($dsItem)){
			$usdPrice = round($dsItem['PRICE'] * 0.93 * 1.22 * 0.001, 2);
			$usdSumPrice = $dsItem['QTY'] * $usdPrice;
			
			$sheet->getRowDimension($i)->setRowHeight(12);
			$sheet->getStyle('A'. $i)->getFont()->setName('Arial')->setSize(10);
			$sheet->getStyle('B'. $i)->getFont()->setName('Arial')->setSize(10);
			$sheet->getStyle('C'. $i)->getFont()->setName('Arial')->setSize(10);
			$sheet->getStyle('C'. $i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$sheet->getStyle('C'. $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$sheet->getStyle('D'. $i)->getFont()->setName('Arial')->setSize(10);
			$sheet->getStyle('D'. $i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$sheet->getStyle('D'. $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			$sheet->getStyle('E'. $i)->getFont()->setName('Arial')->setSize(10);
			$sheet->getStyle('E'. $i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$sheet->getStyle('E'. $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			
			$sheet->setCellValue('A'. $i, $dsItem['ITEMID']);
			$sheet->setCellValue('B'. $i, $dsItem['INVOICENAME']);
			$sheet->setCellValue('C'. $i, number_format($dsItem['QTY']));
			$sheet->setCellValue('D'. $i, number_format($usdPrice, 2, '.', ','));
			$sheet->setCellValue('E'. $i, number_format($usdSumPrice, 2, '.', ','));
			
			$sumQty = $sumQty + $dsItem['QTY'];
			$sumAmount = $sumAmount + $usdSumPrice;
			$idx++;
		}
	}
	// =======================
	// 17. ItemList end
	// =======================
	

	// =======================
	// 18. Total Start
	// =======================
	$idx = $rowcnt + 3;		// 48
	$sheet->setCellValue('B'. $idx, 'TOTAL');
	$sheet->getStyle('B'. $idx)->getFont()->setName('Tahoma')->setSize(9)->setBold(true);    
	$sheet->getStyle('B'. $idx)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('B'. $idx)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet->getStyle('B'. $idx)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');


	$sheet->setCellValue('C'. $idx, number_format($sumQty) .' EA');
	$sheet->getStyle('C'. $idx)->getFont()->setName('Tahoma')->setSize(9)->setBold(true);    
	$sheet->getStyle('C'. $idx)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('C'. $idx)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);	

	$sheet->setCellValue('E'. $idx, number_format($sumAmount, 2, '.', ','));
	$sheet->getStyle('D'. $idx .':E'. $idx)->getFont()->setName('Tahoma')->setSize(9)->setBold(true);    
	$sheet->getStyle('D'. $idx .':E'. $idx)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('D'. $idx .':E'. $idx)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);	

	
	$idx = $idx + 1;		// 49
	$sheet->setCellValue('E'. $idx, '///////////////////////////////////////////////////////////////////////////////////');
	$sheet->getStyle('E'. $idx)->getFont()->setName('굴림')->setSize(10)->setBold(true);    
	$sheet->getStyle('E'. $idx)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('E'. $idx)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

	
	$idx = $idx + 5;		// 54
	$sheet->setCellValue('A'. $idx, '1');
	$sheet->getStyle('A'. $idx)->getFont()->setName('Tahoma')->setSize(10);
	$sheet->getStyle('A'. $idx)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('A'. $idx)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	$sheet->getStyle('A'. $idx)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
	
	
	$idx = $idx + 1;		// 55
	$sheet->setCellValue('A'. $idx, 'MADE IN KOREA');
	$sheet->getStyle('A'. $idx)->getFont()->setName('Tahoma')->setSize(10);
	$sheet->getStyle('A'. $idx)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('A'. $idx)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);	
	
	
	$idx = $idx + 2;		// 57
	$sheet->setCellValue('C'. $idx, '17)Signed by');
	$sheet->getStyle('C'. $idx)->getFont()->setName('Tahoma')->setSize(8);
	$sheet->getStyle('C'. $idx)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('C'. $idx)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	
	$idx = $idx + 1;		// 58
	$sheet->getStyle('C'. $idx .':D'. ($idx + 1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
	
	
	$idx = $idx + 3;		// 61
	$sheet->mergeCells('C'. $idx .':D'. ($idx + 1));
	$sheet->setCellValue('C'. $idx, 'Nano IT Co., Ltd.');
	$sheet->getStyle('C'. $idx . ':D'. ($idx + 1))->getFont()->setName('宋体')->setSize(14)->setBold(true)->setUnderline(true)->setItalic(true);
	$sheet->getStyle('C'. $idx)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);	

	



	// =======================
	// 18. Total end
	// =======================	
	$idx = $idx + 3;		// 64
	$sheet->getStyle('A1:E'. $idx)->applyFromArray(
	    array(
	        'borders' => array(
	        	'bottom' => array(
	                'style' => PHPExcel_Style_Border::BORDER_DASHED,
	                'color' => array('rgb' => '000000')
	            ),
	            'right' => array(
	                'style' => PHPExcel_Style_Border::BORDER_DASHED,
	                'color' => array('rgb' => '000000')
	            )	            
	        )
	    )
	);
	
	$idx = $idx - 6;		// 58
	$objDrawing = new PHPExcel_Worksheet_Drawing();
	$objDrawing->setName('test_img');
	$objDrawing->setDescription('test_img');
	$objDrawing->setPath('/home/NanoIT.png');
	$objDrawing->setCoordinates('E'. $idx);

	//setOffsetX works properly
	//$objDrawing->setOffsetX(5); 
	//$objDrawing->setOffsetY(5);                

	//set width, height
	$objDrawing->setWidth(80); 
	$objDrawing->setHeight(80); 
	$objDrawing->setWorksheet($sheet);	
	// ========================================
	// step 3 End
	// ========================================


	
	/////////////////////////////////////////////
	// PL
	// PACKING LIST
	/////////////////////////////////////////////
	$aColSize = Array();
	array_push($aColSize, "15.89");
	array_push($aColSize, "41.89");
	array_push($aColSize, "12");
	array_push($aColSize, "14");
	array_push($aColSize, "15");
	array_push($aColSize, "15");

	$i = 0;
	foreach(range('A', 'F') as $cID){		
		$sheet2->getColumnDimension($cID)->setWidth($aColSize[$i]);
		$i++;
	}

	$sheet2->getStyle('A1:F64')->applyFromArray(
	    array(
	        'borders' => array(
	            'allborders' => array(
	                'style' => PHPExcel_Style_Border::BORDER_NONE	                
	            )
	        )
	    )
	);
	
	
	// 제목	
	$sheet2->mergeCells('A1:F1');
	$sheet2->setCellValue('A1', 'PACKING LIST');
	$sheet2->getStyle("A1")->getFont()->setName('Arial')->setSize(16)->setBold(true)->getColor()->setRGB('0000FF');
	$sheet2->getStyle('A1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet2->getRowDimension(1)->setRowHeight(-1);

	// Blank
	$sheet2->setCellValue('A2', '');
	$sheet2->getStyle("A2")->getFont()->setSize(10);	
	$sheet2->getRowDimension(2)->setRowHeight(10);

	// 3Line Title
	//$sheet2->mergeCells('A3:B3');
	$sheet2->setCellValue('A3', '1)Shipper/Expoter');
	$sheet2->getStyle("A3")->getFont()->setName('Arial')->setSize(8)->setBold(true)->getColor()->setRGB('0000FF');;		
    $sheet2->getStyle("A3")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet2->getStyle('A3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

	
	$sheet2->setCellValue('C3', '8)No & date of invoice');
	$sheet2->getStyle('C3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	//$sheet2->getStyle('C3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet2->getStyle("C3")->getFont()->setName('Arial')->setSize(8)->setBold(true)->getColor()->setRGB('0000FF');;		
	$sheet2->getRowDimension(3)->setRowHeight(12);
	$sheet2->getStyle('A3:F3')->applyFromArray(
	    array(
	        'borders' => array(
	            'top' => array(
	                'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);


	// Cell Merges
	$sheet2->mergeCells('A4:B8');
	$sheet2->setCellValue('A4', $strA1);	
	$sheet2->getRowDimension(4)->setRowHeight(12);
	$sheet2->getRowDimension(5)->setRowHeight(12);
	$sheet2->getRowDimension(6)->setRowHeight(12);
	$sheet2->getRowDimension(7)->setRowHeight(12);
	// $sheet2->getStyle('A4:B8')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
	$sheet2->getStyle('A4')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
	$sheet2->getStyle('A4')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
	$sheet2->getStyle("A4")->getFont()->setName('Tahoma')->setSize(10)->setBold(true);		
	$sheet2->getStyle('B4:B8')->applyFromArray(
	    array(
	        'borders' => array(
	            'right' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);


	// Cell Merges	
	$sheet2->mergeCells('C5:D5');
	$sheet2->setCellValue('C5', $ds['DELIVERYID']);	
	// $sheet2->getStyle('C5:D5')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
	$sheet2->getStyle('C5')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle('C5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
	$sheet2->getStyle("C5")->getFont()->setName('Tahoma')->setSize(10);	


	// DATE OF INVOICE
	$goodssc_dt = '';
	if($rs[0]['GOODSSC_DT'] != '') $goodssc_dt = substr($rs[0]['GOODSSC_DT'], 0, 10);
	
	$sheet2->setCellValue('F5', $goodssc_dt);
	// $sheet2->getStyle('E5')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
	$sheet2->getStyle('F5')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle('F5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	$sheet2->getStyle("F5")->getFont()->setName('Tahoma')->setSize(10);		
	$sheet2->getStyle('A8:F8')->applyFromArray(
	    array(
	        'borders' => array(
	            'bottom' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);

	$sheet2->getStyle('C5:F5')->applyFromArray(
	    array(
	        'borders' => array(
	            'bottom' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);


	$sheet2->setCellValue('C6', '8)No & date of L/C');		
	$sheet2->getStyle('C6')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle('C6')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
	$sheet2->getStyle("C6")->getFont()->setName('Arial')->setSize(8)->setBold(true);
	$sheet2->getStyle("C7:E8")->getFont()->setName('Arial')->setSize(8);
	$sheet2->getStyle("C7:E8")->getFont()->setName('Tahoma')->setSize(10);


	// ========================================
	// step 2 START
	// ========================================
	$sheet2->setCellValue('A9', '2)Consignee');
	$sheet2->getStyle("A9")->getFont()->setName('Arial')->setSize(8)->setBold(true);
    $sheet2->getStyle("A9")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet2->getStyle('A9')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	
	$sheet2->setCellValue('C9', '10)L/C issuing bank');
	$sheet2->getStyle('C9')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle('C9')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet2->getStyle("C9")->getFont()->setName('Arial')->setSize(8)->setBold(true);		
	$sheet2->getRowDimension(9)->setRowHeight(12);



	// Cell Merges
	$sheet2->mergeCells('A10:B15');
	$sheet2->setCellValue('A10', $rs[0]['NAME'] .chr(10). $rs[0]['DST_ADDR'] . chr(10) .'M : '. $rs[0]['TEL']);	
	$sheet2->getRowDimension(10)->setRowHeight(12);
	$sheet2->getRowDimension(11)->setRowHeight(12);
	$sheet2->getRowDimension(12)->setRowHeight(12);
	$sheet2->getRowDimension(13)->setRowHeight(12);
	$sheet2->getRowDimension(14)->setRowHeight(12);
	$sheet2->getRowDimension(15)->setRowHeight(12);
	// $sheet2->getStyle('A10:B15')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
	$sheet2->getStyle('A10')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
	$sheet2->getStyle('A10')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
	$sheet2->getStyle("A10")->getFont()->setName('Tahoma')->setSize(10)->setBold(true);		
	$sheet2->getStyle('B10:B15')->applyFromArray(
	    array(
	        'borders' => array(
	            'right' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);

	$sheet2->getStyle('A15:B15')->applyFromArray(
	    array(
	        'borders' => array(
	            'bottom' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);


	$sheet2->getStyle('C11:F11')->applyFromArray(
	    array(
	        'borders' => array(
	            'bottom' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);



	$sheet2->setCellValue('C12', '11)Remarks');
	$sheet2->getStyle('C12')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle('C12')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet2->getStyle("C12")->getFont()->setName('Arial')->setSize(8)->setBold(true);	
	
	$sheet2->setCellValue('C14', '* FOB INCHEON');
	$sheet2->setCellValue('C15', '* Country of Origin : SOUTH KOREA');
	$sheet2->getStyle('C14:E15')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle('C14:E15')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
	$sheet2->getStyle("A10")->getFont()->setName('Tahoma')->setSize(10)->setBold(true);			

	// ========================================
	// step 2 End
	// ========================================


	// ========================================
	// step 3 Start
	// ========================================
	$sheet2->setCellValue('A16', '3)Nority Party');
	$sheet2->getStyle("A16")->getFont()->setName('Arial')->setSize(8)->setBold(true);
    $sheet2->getStyle("A16")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet2->getStyle('A16')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);


	// Cell Merges
	$sheet2->mergeCells('A17:B20');
	$sheet2->setCellValue('A17', $strA3);	
	$sheet2->getRowDimension(17)->setRowHeight(12);
	$sheet2->getRowDimension(18)->setRowHeight(12);
	$sheet2->getRowDimension(19)->setRowHeight(12);
	$sheet2->getRowDimension(20)->setRowHeight(12);
	// $sheet2->getStyle('A17:B20')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
	$sheet2->getStyle('A17')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
	$sheet2->getStyle('A17')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
	$sheet2->getStyle("A17")->getFont()->setName('Tahoma')->setSize(10)->setBold(true);		
	$sheet2->getStyle('A16:B44')->applyFromArray(
	    array(
	        'borders' => array(
	            'right' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);

	$sheet2->getStyle('A20:B20')->applyFromArray(
	    array(
	        'borders' => array(
	            'bottom' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);



	$sheet2->setCellValue('A21', '4)Port of Loading');
	$sheet2->getStyle("A21")->getFont()->setName('Arial')->setSize(8)->setBold(true);
    $sheet2->getStyle("A21")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet2->getStyle('A21')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

	$sheet2->setCellValue('A23', 'Incheon,KOREA');
	$sheet2->getStyle("A22:A23")->getFont()->setName('Tahoma')->setSize(10);
    $sheet2->getStyle("A22:A23")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet2->getStyle('A22:A23')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

	$sheet2->getStyle('A21:A23')->applyFromArray(
	    array(
	        'borders' => array(
	            'bottom' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            ),
	            'right' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);


	$sheet2->setCellValue('B21', '5)Final Destination');
	$sheet2->getStyle("B21")->getFont()->setName('Arial')->setSize(8)->setBold(true);
    $sheet2->getStyle("B21")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet2->getStyle('B21')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle("B22:B23")->getFont()->setName('Tahoma')->setSize(10);
    $sheet2->getStyle("B22:B23")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet2->getStyle('B22:B23')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle('B23')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	$sheet2->setCellValue('B23', 'HOCHIEMINH, VEITNAM');
	// $sheet2->getStyle('B23')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
	$sheet2->getStyle('B22:B23')->applyFromArray(
	    array(
	        'borders' => array(
	            'bottom' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            ),
	            'right' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);

	$sheet2->setCellValue('A24', '6)Carrier');
	$sheet2->getStyle("A24")->getFont()->setName('Arial')->setSize(8)->setBold(true);
    $sheet2->getStyle("A24")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet2->getStyle('A24')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle("A25:A26")->getFont()->setName('Tahoma')->setSize(10);
    $sheet2->getStyle("A25:A26")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet2->getStyle('A25:A26')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle('A26')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	$sheet2->setCellValue('A26', 'By air');
	// $sheet2->getStyle('A26')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
	$sheet2->getStyle('A24:A26')->applyFromArray(
	    array(
	        'borders' => array(
	            'bottom' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            ),
	            'right' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);


	// ==============
	// 7. START
	// ==============
	$sheet2->setCellValue('B24', '7)Sailing on or about');
	$sheet2->getStyle("B24")->getFont()->setName('Arial')->setSize(8)->setBold(true);
    $sheet2->getStyle("B24")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet2->getStyle('B24')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle("B25:B26")->getFont()->setName('Tahoma')->setSize(10);
    $sheet2->getStyle("B25:B26")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet2->getStyle('B25:B26')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle('B26')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet2->getStyle('B24:B26')->applyFromArray(
	    array(
	        'borders' => array(
	            'bottom' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            ),
	            'right' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);
	// ==============
	// 7. END
	// ==============


	// =======================
	// 12. Pallet No Start 
	// =======================
	$sheet2->setCellValue('A27', '12)Pallet no.');
	$sheet2->getStyle("A27")->getFont()->setName('Arial')->setSize(8)->setBold(true);
    $sheet2->getStyle("A27")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet2->getStyle('A27')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle("A28:A29")->getFont()->setName('Arial')->setSize(8);
    $sheet2->getStyle("A28:A29")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet2->getStyle('A28:A29')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle('A28:A29')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet2->getStyle('A27:A29')->applyFromArray(
	    array(
	        'borders' => array(
	            'bottom' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            ),
	            'right' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);
	// =======================
	// 12. Pallet No End 
	// =======================


	// =======================
	// 13. Description of Goods
	// =======================
	$sheet2->setCellValue('B27', '13)Description of Goods');
	$sheet2->getStyle("B27")->getFont()->setName('Arial')->setSize(8)->setBold(true);
    $sheet2->getStyle("B27")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet2->getStyle('B27')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle("B28:B29")->getFont()->setName('Arial')->setSize(8);
    $sheet2->getStyle("B28:B29")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet2->getStyle('B28:B29')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle('B28:B29')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet2->getStyle('B27:B29')->applyFromArray(
	    array(
	        'borders' => array(
	            'bottom' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            ),
	            'right' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);
	// =======================
	// 13. Description of Goods end 
	// =======================



	// =======================
	// 14. Quantity start
	// =======================
	$sheet2->setCellValue('C27', '14)Quantity');
	$sheet2->getStyle("C27")->getFont()->setName('Arial')->setSize(8)->setBold(true);
    $sheet2->getStyle("C27")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet2->getStyle('C27')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle('D27')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet2->getStyle("C28:C29")->getFont()->setName('Tahoma')->setSize(10);
    $sheet2->getStyle("C28:C29")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet2->getStyle('C28:C29')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle('C28:C29')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet2->getStyle('C27:C29')->applyFromArray(
	    array(
	        'borders' => array(
	        	'top' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            ),
	            'bottom' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            ),
	            'right' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);	
	// =======================
	// 14. Quantity end 
	// =======================


	// =======================
	// 15. Unit Price(USD) start
	// =======================
	$sheet2->setCellValue('D27', '15)Net Weight'.chr(10).'(KGS)');
	$sheet2->getStyle("D27")->getFont()->setName('Arial')->setSize(8)->setBold(true);
    $sheet2->getStyle("D27")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet2->getStyle('D27')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle('D27')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	$sheet2->setCellValue('D28', '(KGS)');
	$sheet2->getStyle("D28")->getFont()->setName('Arial')->setSize(8);
    $sheet2->getStyle("D28")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet2->getStyle('D28')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle('D28')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet2->getStyle("D28:D29")->getFont()->setName('Tahoma')->setSize(10);
    $sheet2->getStyle("D28:D29")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet2->getStyle('D28:D29')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle('D28:D29')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet2->getStyle('D27:D29')->applyFromArray(
	    array(
	        'borders' => array(
	        	'top' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            ),
	            'bottom' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            ),
	            'right' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);	
	// =======================
	// 15. UnitPrice end
	// =======================



	// =======================
	// 16. Amount start
	// =======================
	$sheet2->setCellValue('E27', '16)Gross weight');
	$sheet2->getStyle("E27")->getFont()->setName('Arial')->setSize(8)->setBold(true);
    $sheet2->getStyle("E27")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet2->getStyle('E27')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle('E27')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	$sheet2->setCellValue('E28', '(KGS)');
	$sheet2->getStyle("E28")->getFont()->setName('Arial')->setSize(8);
    $sheet2->getStyle("E28")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet2->getStyle('E28')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle('E28')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);


	$sheet2->getStyle("E28:E29")->getFont()->setName('Tahoma')->setSize(10);
    $sheet2->getStyle("E28:E29")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet2->getStyle('E28:E29')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle('E28:E29')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet2->getStyle('E27:E29')->applyFromArray(
	    array(
	        'borders' => array(
	        	'top' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            ),
	            'bottom' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            ),
	            'right' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);	
	// =======================
	// 16. Amount End
	// =======================


	// =======================
	// 17. ItemList Start
	// =======================
	$sheet2->setCellValue('F27', '17)Measurement');
	$sheet2->getStyle("F27")->getFont()->setName('Arial')->setSize(8)->setBold(true);
    $sheet2->getStyle("F27")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet2->getStyle('F27')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle('F27')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

	$sheet2->setCellValue('F28', '(CBM)');
	$sheet2->getStyle("F28")->getFont()->setName('Arial')->setSize(8);
    $sheet2->getStyle("F28")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet2->getStyle('F28')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle('F28')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	$sheet2->getStyle("F28:F29")->getFont()->setName('Tahoma')->setSize(10);
    $sheet2->getStyle("F28:F29")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet2->getStyle('F28:F29')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle('F28:F29')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet2->getStyle('F27:F29')->applyFromArray(
	    array(
	        'borders' => array(
	        	'top' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            ),
	            'bottom' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            ),
	            'right' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )
	        )
	    )
	);	


	$sheet2->getStyle('A30:F44')->applyFromArray(
	    array(
	        'borders' => array(
	        	'allborders' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => '969696')
	            )	            
	        )
	    )
	);
	$sheet2->getStyle('A30:F30')->applyFromArray(
	    array(
	        'borders' => array(
	        	'top' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THICK,
	                'color' => array('rgb' => '000000')
	            )	            
	        )
	    )
	);	
	
	for($i = 30; $i <= 50; $i++){
		$dsItem = $rs[0];
		
		$sheet2->getRowDimension($i)->setRowHeight(12);
		$sheet2->getStyle('A'. $i)->getFont()->setName('Arial')->setSize(10);
		$sheet2->getStyle('B'. $i)->getFont()->setName('Arial')->setSize(10);
		
		$sheet2->getStyle('C'. $i)->getFont()->setName('Arial')->setSize(10);
		$sheet2->getStyle('C'. $i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$sheet2->getStyle('C'. $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$sheet2->getStyle('D'. $i)->getFont()->setName('Arial')->setSize(10);
		$sheet2->getStyle('D'. $i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$sheet2->getStyle('D'. $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$sheet2->getStyle('E'. $i)->getFont()->setName('Arial')->setSize(10);
		$sheet2->getStyle('E'. $i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$sheet2->getStyle('E'. $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$sheet2->getStyle('F'. $i)->getFont()->setName('Arial')->setSize(10);
		$sheet2->getStyle('F'. $i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$sheet2->getStyle('F'. $i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		
		$sheet2->setCellValue('A30', $ds['DELIVERYID']);
		$sheet2->setCellValue('B30', $dsItem['INVOICENAME']);
		$sheet2->setCellValue('C30', '1 EA');
		$sheet2->setCellValue('D30', number_format($ds['WEIGHT'], 2, '.', ',') .' KGS');
		$sheet2->setCellValue('E30', number_format($ds['WEIGHT'], 2, '.', ',') .' KGS');
		$sheet2->setCellValue('F30', '0.00 CBM');
	}

	// =======================
	// 17. ItemList end
	// =======================
	

	// =======================
	// 18. Total Start
	// =======================
	$sheet2->setCellValue('B48', 'TOTAL');
	$sheet2->getStyle("B48")->getFont()->setName('Tahoma')->setSize(9)->setBold(true);    
	$sheet2->getStyle('B48')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle('B48')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet2->getStyle('B48')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');


	$sheet2->setCellValue('C48', '1 EA');
	$sheet2->setCellValue('D48', number_format($ds['WEIGHT'], 2, '.', ',') .' KGS');
	$sheet2->setCellValue('E48', number_format($ds['WEIGHT'], 2, '.', ',') .' KGS');
	$sheet2->setCellValue('F48', '0.00 CBM');
	$sheet2->getStyle("C48:F48")->getFont()->setName('Tahoma')->setSize(9)->setBold(true);    
	$sheet2->getStyle('C48:F48')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle('C48:F48')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);	
	
	
	$sheet2->setCellValue('E49', '///////////////////////////////////////////////////////////////////////////////////');
	$sheet2->getStyle("E49")->getFont()->setName('굴림')->setSize(10)->setBold(true);    
	$sheet2->getStyle('E49')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle('E49')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);


	$sheet2->setCellValue('A54', '1');
	$sheet2->getStyle("A54")->getFont()->setName('Tahoma')->setSize(10);
	$sheet2->getStyle('A54')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle('A54')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	$sheet2->getStyle('A54')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');

	$sheet2->setCellValue('A55', 'MADE IN KOREA');
	$sheet2->getStyle("A55")->getFont()->setName('Tahoma')->setSize(10);
	$sheet2->getStyle('A55')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle('A55')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);	

	$sheet2->setCellValue('C57', '17)Signed by');
	$sheet2->getStyle("C57")->getFont()->setName('Tahoma')->setSize(8);
	$sheet2->getStyle('C57')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle('C57')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	// $sheet2->getStyle('C58:D59')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');

	$sheet2->mergeCells('C61:D62');
	$sheet2->setCellValue('C61', 'Nano IT Co., Ltd.');
	$sheet2->getStyle("C61:D62")->getFont()->setName('宋体')->setSize(14)->setBold(true)->setUnderline(true)->setItalic(true);
	$sheet2->getStyle('C61')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);	

	



	// =======================
	// 18. Total end
	// =======================
	
	$sheet2->getStyle('A1:F64')->applyFromArray(
	    array(
	        'borders' => array(
	        	'bottom' => array(
	                'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
	                'color' => array('rgb' => '000000')
	            )	            
	        )
	    )
	);




	$objDrawing = new PHPExcel_Worksheet_Drawing();
	$objDrawing->setName('test_img');
	$objDrawing->setDescription('test_img');
	$objDrawing->setPath('/home/NanoIT.png');
	$objDrawing->setCoordinates('E58');                      

	//setOffsetX works properly
	//$objDrawing->setOffsetX(5); 
	//$objDrawing->setOffsetY(5);                

	//set width, height
	$objDrawing->setWidth(80); 
	$objDrawing->setHeight(80); 
	$objDrawing->setWorksheet($sheet2);	

	//다운
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename=(HCMC)IV&PL_'. $rs[0]['NAME'] .' '. $ds['DELIVERYID'] .'.xlsx');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
//	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML');
//	$objWriter->save('test.html');
?>