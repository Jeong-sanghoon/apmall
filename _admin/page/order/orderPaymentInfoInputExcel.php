<?
	// ===================================================
	// include
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_lib/PHPExcel.php";
	require_once $_SERVER['DOCUMENT_ROOT'] ."/_lib/PHPExcel/IOFactory.php";
	
	chkSessionAjax($url = '/_admin/');
	
	// ===================================================
	// get parameter
	// ===================================================
	$payment_tp = $cFnc->getReq('payment_tp', '');
	$UPFILE	= $_FILES['upload_excel'];
	
	// =====================================================
	// Set Variables
	// =====================================================
	$result = array();
	$result['status'] = 1;
	$result['msg'] = "";
	$result['url'] = "";
	$result['data'] = "";
	
	$arr_data = array();
	
	$strUpPath = UPLOAD_DIR;
	$strUpPathLoc = date('Ym');
	$strUpPathFull = $strUpPath ."/". $strUpPathLoc;
	$arrThumb = array();		// 썸네일 이미지 생성 array(가로크기, 세로크기)
	$bExtCheck = true;			// 파일 확장자 체크 여부
	
	$cPdo = new cPdo($ARR_DB_INFO);
	$oFile = new cFile($strUpPathFull, $arrThumb, $bExtCheck, 0, 0);
	
	$oFile->setFileExtenstion("xlsx|xls");
	
	// =====================================================
	// Start Tran
	// =====================================================
	try{
		$result['msg'] = "엑셀업로드 처리가 완료되었습니다";
		$result['url'] = "";
		
		// 파일업로드
		if($UPFILE["name"] != ''){
			// 폴더생성
			if(!is_dir($strUpPathFull)){
				mkdir($strUpPathFull, 0777);
				chmod($strUpPathFull, 0777);
			}
			
			// 확장자 유효성 검사 추가
			$strExt = $oFile->getFileExtension($UPFILE["name"]);
			if(!$oFile->isFileExtension($strExt)) throw new Exception('허용되지 않은 파일 형식입니다 : '. $strExt);
			
			// 업로드
			$upResult = $oFile->Upload($UPFILE, 0);
			$imgPath = $strUpPathLoc .'/'. $upResult;
		}
		
		$excelfile = $strUpPath .'/'. $imgPath;
		$arrData = array();
		$idx = 0;
		
		$objPHPExcel = new PHPExcel();
		
		$inputFileType = 'Excel2007';
		if($strExt == "xls") $inputFileType = 'Excel5';
		
		$objReader = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load($excelfile);
		$objPHPExcel->setActiveSheetIndex(0);
		$objSheet = $objPHPExcel->getActiveSheet();
		
		$rows = $objSheet->getRowIterator();									// row데이터 추출
		
		if($payment_tp == 'credit'){											// 신용카드
			$idx = 0;
			
			foreach($rows as $row_i=>$row){										// row데이터 루프
				$isEmpty = true;
				$cells = $row->getCellIterator();								// cell데이터 추출
				
				if($row_i > 18){												// row데이터 19번째행부터 읽어오기
					foreach($cells as $cell_i=>$cell){							// cell데이터 루프
						if($cell_i == 'D'){										// cell데이터 승인일자
							$val = $cell->getValue();
							if($val != '') $isEmpty = false;
						}
						
						if($isEmpty == false){									// 승인일자 있을때만
							if($cell_i == 'D'){									// cell데이터 승인일자
								$val = $cell->getValue();
								$arr_data[$idx]['approval_dt'] = str_replace('.', '-', $val);
							}
							
							if($cell_i == 'H'){									// cell데이터 승인번호
								$val = $cell->getValue();
								$arr_data[$idx]['approval_no'] = $val;
							}
						}
					}
					
					if($isEmpty == false) $idx++;
				}
			}
		}
		else if($payment_tp == 'check'){										// 체크카드
			$idx = 0;
			
			foreach($rows as $row_i=>$row){										// row데이터 루프
				$isEmpty = true;
				$cells = $row->getCellIterator();								// cell데이터 추출
				
				if($row_i > 1){													// row데이터 2번째행부터 읽어오기
					foreach($cells as $cell_i=>$cell){							// cell데이터 루프
						if($cell_i == 'B'){										// cell데이터 승인일자
							$val = $cell->getValue();
							if($val != '') $isEmpty = false;
						}
						
						if($isEmpty == false){									// 승인일자 있을때만
							if($cell_i == 'B'){									// cell데이터 승인일자
								$val = $cell->getValue();
								$arr_data[$idx]['approval_dt'] = substr($val, 0, 10);
							}
							
							if($cell_i == 'D'){									// cell데이터 승인번호
								$val = $cell->getValue();
								$arr_data[$idx]['approval_no'] = $val;
							}
						}
					}
					
					if($isEmpty == false) $idx++;
				}
			}
		}
		
		$result['data'] = $arr_data;
	}
	catch(Exception $e){
		$result['status'] = 0;
		$result['msg'] = $e->getMessage();
		if($e->getCode() != '')	$result['msg'] .= ' // '. $e->getCode();
		$result['url'] = "";
	}
	
	echo json_encode($result);
	$cPdo->close();
?>
