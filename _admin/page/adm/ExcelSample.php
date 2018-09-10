<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_lib/PHPExcel.php";


	// chkSession($url = '/_admin/');

	
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
	

	//$sheet->setCellValue('');

	/////////////////////////////////////////////
	// CI
	/////////////////////////////////////////////
	
	// 제목	
	//$sheet->mergeCells('A1:E1');
	$sheet->setCellValue('A1', '             COMMERCIAL INVOICE');
	$sheet->getStyle("A1")->getFont()->setName('Arial')->setSize(16)->setBold(true)->getColor()->setRGB('0000FF');
	$sheet->getRowDimension(1)->setRowHeight(-1);

	// Blank
	$sheet->setCellValue('A2', '');
	$sheet->getStyle("A2")->getFont()->setSize(10);	
	$sheet->getRowDimension(2)->setRowHeight(10);

	// 3Line Title
	//$sheet->mergeCells('A3:B3');
	$sheet->setCellValue('A3', '1)Shipper/Expoter');
	$sheet->getStyle("A3")->getFont()->setName('Arial')->setSize(8)->setBold(true)->getColor()->setRGB('0000FF');;		
    $sheet->getStyle("A3")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet->getStyle('A3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

	
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


	// Cell Merges
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


	// Cell Merges	
	$sheet->mergeCells('C5:D5');
	$sheet->setCellValue('C5', 'OD20181212121212');	
	$sheet->getStyle('C5:D5')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
	$sheet->getStyle('C5')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('C5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
	$sheet->getStyle("C5")->getFont()->setName('Tahoma')->setSize(10);	


	// DATE OF INVOICE
	$sheet->setCellValue('E5', '2018.04.23');	
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


	$sheet->setCellValue('C6', '8)No & date of L/C');		
	$sheet->getStyle('C6')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('C6')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
	$sheet->getStyle("C6")->getFont()->setName('Arial')->setSize(8)->setBold(true);
	$sheet->getStyle("C7:E8")->getFont()->setName('Arial')->setSize(8);
	$sheet->getStyle("C7:E8")->getFont()->setName('Tahoma')->setSize(10);


	// ========================================
	// step 2 START
	// ========================================
	$sheet->setCellValue('A9', '2)Consignee');
	$sheet->getStyle("A9")->getFont()->setName('Arial')->setSize(8)->setBold(true);
    $sheet->getStyle("A9")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet->getStyle('A9')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	
	$sheet->setCellValue('C9', '10)L/C issuing bank');
	$sheet->getStyle('C9')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('C9')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet->getStyle("C9")->getFont()->setName('Arial')->setSize(8)->setBold(true);		
	$sheet->getRowDimension(9)->setRowHeight(12);



	// Cell Merges
	$sheet->mergeCells('A10:B15');
	$sheet->setCellValue('A10', 'Nanoit'.chr(10).'2F');	
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



	$sheet->setCellValue('C12', '11)Remarks');
	$sheet->getStyle('C12')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('C12')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet->getStyle("C12")->getFont()->setName('Arial')->setSize(8)->setBold(true);	



	$sheet->getStyle('C14:E15')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
	$sheet->getStyle('C14:E15')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
	$sheet->getStyle('C14:E15')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
	$sheet->getStyle("A10")->getFont()->setName('Tahoma')->setSize(10)->setBold(true);			

	// ========================================
	// step 2 End
	// ========================================


	// ========================================
	// step 3 Start
	// ========================================
	$sheet->setCellValue('A16', '3)Nority Party');
	$sheet->getStyle("A16")->getFont()->setName('Arial')->setSize(8)->setBold(true);
    $sheet->getStyle("A16")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet->getStyle('A16')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);


	// Cell Merges
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



	$sheet->setCellValue('A21', '4)Port of Loading');
	$sheet->getStyle("A21")->getFont()->setName('Arial')->setSize(8)->setBold(true);
    $sheet->getStyle("A21")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet->getStyle('A21')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

	$sheet->getStyle("A22:A23")->getFont()->setName('Tahoma')->setSize(10);
    $sheet->getStyle("A22:A23")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet->getStyle('A22:A23')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

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

	$sheet->setCellValue('A26', 'By Air');
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
	$sheet->setCellValue('E27', '16)Amount(USD)');
	$sheet->getStyle("E27")->getFont()->setName('Arial')->setSize(8)->setBold(true);
    $sheet->getStyle("E27")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet->getStyle('E27')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('E27')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	$sheet->getStyle("E28:E29")->getFont()->setName('Tahoma')->setSize(10);
    $sheet->getStyle("E28:E29")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_DOUBLE);
	$sheet->getStyle('E28:E29')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('E28:E29')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet->getStyle('A30:E44')->applyFromArray(
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

	for($i= 30; $i<=44; $i++){
		$sheet->getRowDimension($i)->setRowHeight(12);	
	}

	// =======================
	// 17. ItemList end
	// =======================
	

	// =======================
	// 18. Total Start
	// =======================
	$sheet->setCellValue('B48', 'TOTAL');
	$sheet->getStyle("B48")->getFont()->setName('Tahoma')->setSize(9)->setBold(true);    
	$sheet->getStyle('B48')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('B48')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet->getStyle('B48')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');


	$sheet->setCellValue('C48', 'EA');
	$sheet->getStyle("C48")->getFont()->setName('Tahoma')->setSize(9)->setBold(true);    
	$sheet->getStyle('C48')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('C48')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);	

	
	$sheet->getStyle("D48:E48")->getFont()->setName('Tahoma')->setSize(9)->setBold(true);    
	$sheet->getStyle('D48:E48')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('D48:E48')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);	


	$sheet->setCellValue('E49', '///////////////////////////////////////////////////////////////////////////////////');
	$sheet->getStyle("E49")->getFont()->setName('굴림')->setSize(10)->setBold(true);    
	$sheet->getStyle('E49')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('E49')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);


	$sheet->setCellValue('A54', '1');
	$sheet->getStyle("A54")->getFont()->setName('Tahoma')->setSize(10);
	$sheet->getStyle('A54')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('A54')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	$sheet->getStyle('A54')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');

	$sheet->setCellValue('A55', 'MADE IN KOREA');
	$sheet->getStyle("A55")->getFont()->setName('Tahoma')->setSize(10);
	$sheet->getStyle('A55')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('A55')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);	



	$sheet->setCellValue('C57', '17)Signed by');
	$sheet->getStyle("C57")->getFont()->setName('Tahoma')->setSize(8);
	$sheet->getStyle('C57')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet->getStyle('C57')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet->getStyle('C58:D59')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');

	$sheet->mergeCells('C61:D62');
	$sheet->setCellValue('C61', 'Nano IT Co., Ltd.');
	$sheet->getStyle("C61:D62")->getFont()->setName('宋体')->setSize(14)->setBold(true)->setUnderline(true)->setItalic(true);
	$sheet->getStyle('C61')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);	

	



	// =======================
	// 18. Total end
	// =======================	


	$sheet->getStyle('A1:E64')->applyFromArray(
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
	$objDrawing->setWorksheet($sheet);	
	// ========================================
	// step 3 End
	// ========================================


	
	// Picking List End 
	// Picking List Start
	// Picking List Start
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
	

	/////////////////////////////////////////////
	// PL
	/////////////////////////////////////////////
	
	
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
	$sheet2->setCellValue('C5', 'OD20181212121212');	
	// $sheet2->getStyle('C5:D5')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
	$sheet2->getStyle('C5')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle('C5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
	$sheet2->getStyle("C5")->getFont()->setName('Tahoma')->setSize(10);	


	// DATE OF INVOICE
	$sheet2->mergeCells('E5:F5');
	$sheet2->setCellValue('F5', '2018.04.23');
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
	$sheet2->setCellValue('A10', 'Nanoit'.chr(10).'2F');	
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



	// $sheet2->getStyle('C14:E15')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
	$sheet2->getStyle('C14:E15')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
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

	$sheet2->setCellValue('A26', 'By Air');
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
	
	// 리스트 채우는 부분
	for($i= 30; $i<=50; $i++){
		$sheet2->getRowDimension($i)->setRowHeight(12);	
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


	$sheet2->setCellValue('C48', 'EA');
	$sheet2->getStyle("C48")->getFont()->setName('Tahoma')->setSize(9)->setBold(true);    
	$sheet2->getStyle('C48')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle('C48')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);	

	
	$sheet2->getStyle("D48:E48")->getFont()->setName('Tahoma')->setSize(9)->setBold(true);    
	$sheet2->getStyle('D48:E48')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	$sheet2->getStyle('D48:E48')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);	


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
















	// $sheet->setCellValue('B1', '이름');
	// $sheet->setCellValue('C1', '이메일');
	// $sheet->setCellValue('D1', '연락처');
	// $sheet->setCellValue('E1', '등록일');
	// $sheet->setCellValue('F1', '등급');
	// $sheet->setCellValue('G1', '사용여부');	
	
	//$sheet->setCellValue('A2', 'COMMERCIAL INVOICE');

	/*
	foreach($rsList as $i=>$ds){
		$row = $i + 2;
		
		$sheet->setCellValue('A'.$row, $ds['ADM_ID']);
		$sheet->setCellValue('B'.$row, $ds['ADM_NM']);
		$sheet->setCellValue('C'.$row, $ds['EMAIL']);
		$sheet->setCellValue('D'.$row, $ds['TEL']);		
		$sheet->setCellValue('E'.$row, $ds['REG_DT']);
		$sheet->setCellValue('F'.$row, CODE_ADM_GRADE($ds['GRADE']));
		$sheet->setCellValue('G'.$row, CODE_USE_YN($ds['USE_YN']));
	}
	*/

	//다운
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename=admin_list_'. date('YmdHis') .'.xlsx');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
//	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML');
//	$objWriter->save('test.html');
?>