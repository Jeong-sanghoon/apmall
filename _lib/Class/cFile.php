<?
/**
 *  Description : 파일 관련 클래스 (업로드, 다운로드)
 */
class cFile{

	private $arOrisFile;
	private $arUpFile;
	private $strMapPath;
	private $strRootPath;
	private $nUpLoopCnt;
	private $nThumImgW;			//썸네일 이미지 제한 Width
	private $nThumImgH;			//섬네일 이미지 제한 Height
	private $IsStandard;		//썸네일 비율맞추기 사용 유무(0:사용,1:미사용)
	private $isThumbNail;		//썸네일 생성 유무(0:미생성,1:생성)
	private $strImgExt = "jpg|jpeg|gif|bmp|png|psd|pdd|tif|pdf|raw|ai|eps|svg|svgz|iff|fpx|frm|pcx|pct|pic|pxr|sct|tga|vda|icb|vst|zip";
	private $bExtCheck = true;

	
	public function __construct($strMapPath,$nImgInfo,$bCheck,$nStandard=0,$isThumb=0){
		$this->strMapPath	=	$strMapPath;
		$this->strRootPath  =	$strMapPath;
		$this->nThumImgW 	=	$nImgInfo[0];
		$this->nThumImgH 	=	$nImgInfo[1];
		$this->IsStandard	=	$nStandard;
		$this->isThumbNail	= 	$isThumb;
		$this->bExtCheck	= 	$bCheck;
	}
	
	
	public function __destruct(){
		$this->arOrisFile	= 	null;
		$this->arUpFile 	= 	null;
		$this->strMapPath 	= 	null;
		$this->nUpLoopCnt 	= 	null;
		$this->nThumImgW 	= 	null;
		$this->nThumImgH 	= 	null;
		$this->IsStandard	=	null;
		$this->isThumbNail	= 	null;
	}

	
	public function getMapPathInfo(){
		return $this->strMapPath;
	}

	
	//업로드 후 압축하기.
	public function UploadAndZip($arUpFile,$nIsArray,$subName=""){
		$arUploadFile = $this->setUpload($arUpFile,$nIsArray,$subName);

		//압축파일명.
		$tmpFileName = sha1( uniqid() ).".zip";
		$strTargetFile = "";
		$arDelFile = array();
		
		for($i=0;$i<count($arUploadFile);$i++){
			$strTargetFile .= " ".$this->strMapPath."/".$arUploadFile[$i];
			$arDelFile[$i] = $this->strMapPath."/".$arUploadFile[$i];
		}

		$strShell = "zip ".$this->strMapPath.$tmpFileName.$strTargetFile;
		$strResult = shell_exec($strShell);
		$this->DeleteFiles($arDelFile);
		
		return $tmpFileName;
	}

	
	//파일 업로드하기
	public function Upload($arUpFile, $subName = "")
	{
		$this->arOrisFile = $arUpFile;
		$strRealName = '';
		
		$arFileName = $this->getFileName($this->arOrisFile["name"]);
		
		$strUpFileName = sha1( uniqid() );
		if($arFileName[1]!="" && strlen($arFileName[1])>2)	$strUpFileName .= ".".$arFileName[1];
		$strRealName = ($subName != "") ? $subName : $strUpFileName;
		$this->setRealUpload($this->arOrisFile["tmp_name"],$strRealName);
		
		return $strRealName;
	}//end fnc


	//파일 업로드하기
	public function UploadMulty($arUpFile,$i){
		$this->arOrisFile = $arUpFile;
		//$this->nUpLoopCnt = count($this->arOrisFile);//전체 배열 갯수
		$this->nUpLoopCnt = max(array_map('count', $this->arOrisFile));
		$strUpLoadName = "";
		
		if(trim($this->arOrisFile["name"][$i]) != ""){
			$arFileName = $this->getFileName($this->arOrisFile["name"][$i]);
			//확장자 없을 경우 처리
			$strUpFileName = sha1( uniqid());
			if($arFileName[1]!="" && strlen($arFileName[1])>2)$strUpFileName .= ".".$arFileName[1];
			$strUpLoadName= $strUpFileName;
			$this->setRealUpload($this->arOrisFile["tmp_name"][$i],$strUpLoadName);
			$strUpLoadName = str_replace($this->strRootPath,"",$this->strMapPath)."/".$strUpLoadName;
		}
		else{
			$strUpLoadName="";
		}
		
		return $strUpLoadName;
	}//end fnc


	//실제 업로드 하는 함수
	public function setRealUpload($orisFile,$newFile){
		$strUpPath = $this->strMapPath;
		if(!move_uploaded_file($orisFile,$strUpPath."/".$newFile))$this->outErrMsg("업로드 실패 [파일명:".$orisFile.' , '.($strUpPath."/".$newFile)."]");
		
		if($this->isThumbNail==1){
			$arFileName = explode(".", $newFile);
			$nResult = $this->makeThumImg($strUpPath."/".$newFile, $strUpPath ."/". $arFileName[0] ."_s.". $arFileName[1]);
			if($nResult !=1)$this->outErrMsg("썸네일 생성 중 에러 발생:".$nResult);
		}
	}

	
	//예외처리
	public function outErrMsg($strMsg){
		return $strMsg;
	}

	
	//파일 삭제하기 (배열 파라미터)
	public function DeleteFiles($arUpFile){
		$nLoopCnt = count($arUpFile);
		
		for($i=0;$i<$nLoopCnt;$i++){
			if(trim($arUpFile[$i])!="" && file_exists($arUpFile[$i])) @unlink($arUpFile[$i]);
		}
	}

	
	//실제 업로드 수량
	public function getUpCnt(){
		$nCnt= 0;
		
		for($i=0;$i<Count($this->arOrisFile);$i++){
			if(trim($this->arOrisFile["name"][$i])!="")$nCnt++;
		}
		
		return $nCnt;
	}


	//확장자 구하기
	public function getFileExtension($strFileName){
		return strtolower(substr(strrchr($strFileName, '.'), 1));
	}

	
	//확장자 비교하기
	public function isFileExtension($strExt){
		if($this->bExtCheck == true){
			if(strpos($this->strImgExt, $strExt) === false) return false;
		}
		
		return true;
	}

	
	//허용 확장자 변경 ( 확장자/확장자/확장자 형태로 추가 )
	public function setFileExtenstion($strExt){
		
		$this->strImgExt = $strExt;
		if($this->strImgExt != $strExt) return false;
		return true;
	}


	// Thumbnail Img 만들기
	public function makeThumImg($strOrisImg,$strThumFile){
		
		if($strOrisImg == "" || $strThumFile == "")		return 2;		// 해당 내용 없음
		if(!file_exists($strOrisImg))					return 3;		// 파일이 존재 하지 않음

		$arImg = getimagesize($strOrisImg);

		if((int)$arImg[0] <= $this->nThumImgW && (int)$arImg[1] <=$this->nThumImgH){
			
			@copy($strOrisImg,$strThumFile);
			return 1;//썸네일 만들 필요 없음
		}

		if($arImg[2]<1 && $arImg[2]>3) return 4;//미지원 포맷(gif/png/jpg)

		if($this->IsStandard==1){
			$arImgSize = array("W"=>$this->nThumImgW,"H"=>$this->nThumImgH);
		}
		else{
			$arTmpSize = $this->getThumbRatio($arImg);
			$arImgSize = array("W"=>$arTmpSize["W"],"H"=>$arTmpSize["H"]);
		}

		if($arImg[2]==6){
			
			$objBmpToJpg = new ConvertBMP2GDClass();
			$img = $objBmpToJpg -> imagecreatefrombmp($strOrisImg);
			imagejpeg($img,$strThumFile);
		}
		else{
			switch($arImg[2]){
				
				case(1):$objImg = imagecreatefromgif($strOrisImg);
						$tmpImg = imagecreate($arImgSize["W"],$arImgSize["H"]);
						ImageCopyResampled($tmpImg,$objImg,0,0,0,0,$arImgSize["W"],$arImgSize["H"],ImageSX($objImg),ImageSY($objImg));
						imageinterlace($tmpImg);
						imagegif($tmpImg,$strThumFile);
						break;
				case(2):$objImg = imagecreatefromjpeg($strOrisImg);
						$tmpImg	= imagecreatetruecolor($arImgSize["W"],$arImgSize["H"]);
						imagecopyresampled($tmpImg,$objImg,0,0,0,0,$arImgSize["W"],$arImgSize["H"],imagesx($objImg),imagesy($objImg));
						imagejpeg($tmpImg,$strThumFile);
						break;
				case(3):$objImg = imagecreatefrompng($strOrisImg);
						$tmpImg = imagecreatetruecolor($arImgSize["W"],$arImgSize["H"]);
						imagecopyresampled($tmpImg,$objImg,0,0,0,0,$arImgSize["W"],$arImgSize["H"],imagesx($objImg),imagesy($objImg));
						imagepng($tmpImg,$strThumFile);
						break;
			}

			Imagedestroy($objImg);
			imagedestroy($tmpImg);
		}//end bmp if
		
		return 1;
	}

	
	//Thumbnail 이미지 비율 얻기(arImgInfo[0]="Img with",arImgInfo[1]="Img height",arImgInfo[2]="Img Maxwith",arImgInfo[3]="Img MaxHeight"
	public function getThumbRatio($arImgInfo){
		
		$arSize = array("W"=>(int)$arImgInfo[0],"H"=>(int)$arImgInfo[1]);
		
		if($arImgInfo[0] > $this->nThumImgW && $arImgInfo[1] > $this->nThumImgH){
			if($arImgInfo[0] <= $arImgInfo[1]) {
				$arSize["W"] = ceil(($arImgInfo[0]/$arImgInfo[1])*$this->nThumImgH);
				$arSize["H"] = $this->nThumImgH;
				
				if($arSize["W"]>$this->nThumImgW){
					$arSize["W"] = $this->nThumImgW;
					$arSize["H"] = ceil(($arSize["W"]/$arSize["H"])*$this->nThumImgH);
				}
			}
			else{
				$arSize["W"] = $this->nThumImgW;
				$arSize["H"] = ceil(($arImgInfo[1]/$arImgInfo[0])*$this->nThumImgW);
				
				if($arSize["H"]>$this->nThumImgH){
					$arSize["W"] = ceil(($arSize["H"]/$arSize["W"])*$this->nThumImgW);
					$arSize["H"] = $this->nThumImgH;
				}
			}
		}
		elseif ($arImgInfo[0]>$this->nThumImgW && $arImgInfo[1]<=$this->nThumImgH){
			$arSize["W"] = $this->nThumImgW;
			$arSize["H"] = ceil(($arImgInfo[1]/$arImgInfo[0])*$this->nThumImgH);
		}
		elseif ($arImgInfo[0]<=$this->nThumImgW && $arImgInfo[1]>$this->nThumImgH){
			$arSize["W"] = ceil(($arImgInfo[0]/$arImgInfo[1])*$this->nThumImgH);
			$arSize["H"] = $this->nThumImgH;
		}
		
		return $arSize;
	}


	//유니크한 파일명 얻기
	public function getUniqFileName($strFileName){
		
		$strPath = $this->strMapPath;
		$strExt = $this->getFileExtension($strFileName);
		$arFileName = $this->getFileName($strFileName);

		//(1)형식의 이름 변경하기
		$strFile	= eregi_replace("\(|\)|\(+[0-9]+\)","",$arFileName[0]);
		$strNewFile = $strFile.".".$strExt;
		$i=0;
		
		while(true){
			if(file_exists($strPath."/".$strNewFile)){
				$strNewFile = $strFile."(".$i.").".$strExt;
				$i++;
			}
			else{
				break;
			}
		}//end while
		
		return $strNewFile;
	}


	//파일명만 얻기
	public function getFileName($strFileName){
		
		$arTmpFile = explode("/",$strFileName);
		$tmpFile = $arTmpFile[count($arTmpFile)-1];//확장자 포함 파일명
		$arExt   = explode(".",$tmpFile);
		$arFileName = array($arExt[0],$arExt[count($arExt)-1]);
		return $arFileName;
	}


	//디렉토리 생성
	public function setNewDir($strFullDirName){
		
		if(!is_dir($strFullDirName))
		{
			mkdir($strFullDirName, 0707);
			chmod($strFullDirName, 0777);
		}
		return $strFullDirName;
	}
	
	
	// PHP 파일 다운로드 함수
	public function DownloadFile($filename, $server_filename, $expires = 0, $speed_limit = 0) {
		
		// 서버측 파일명을 확인한다.
		if (!file_exists($server_filename) || !is_readable($server_filename)) {
			return false;
		}
		if (($filesize = filesize($server_filename)) == 0) {
			return false;
		}
		if (($fp = @fopen($server_filename, 'rb')) === false) {
			return false;
		}
		
		// 파일명에 사용할 수 없는 문자를 모두 제거하거나 안전한 문자로 치환한다.
		$illegal = array('\\', '/', '<', '>', '{', '}', ':', ';', '|', '"', '~', '`', '@', '#', '$', '%', '^', '&', '*', '?');
		$replace = array('', '', '(', ')', '(', ')', '_', ',', '_', '', '_', '\'', '_', '_', '_', '_', '_', '_', '', '');
		$filename = str_replace($illegal, $replace, $filename);
		$filename = preg_replace('/([\\x00-\\x1f\\x7f\\xff]+)/', '', $filename);
		
		// 유니코드가 허용하는 다양한 공백 문자들을 모두 일반 공백 문자(0x20)로 치환한다.
		$filename = trim(preg_replace('/[\\pZ\\pC]+/u', ' ', $filename));
		
		// 위에서 치환하다가 앞뒤에 점이 남거나 대체 문자가 중복된 경우를 정리한다.
		$filename = trim($filename, ' .-_');
		$filename = preg_replace('/__+/', '_', $filename);
		if ($filename === '') {
			return false;
		}
		
		// 브라우저의 User-Agent 값을 받아온다.
		$ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$old_ie = (bool)preg_match('#MSIE [3-8]\.#', $ua);
		
		if (preg_match('/^[a-zA-Z0-9_.-]+$/', $filename)) {
			// 파일명에 숫자와 영문 등만 포함된 경우 브라우저와 무관하게 그냥 헤더에 넣는다.
			$header = 'filename="' . $filename . '"';
		}
		elseif ($old_ie || preg_match('#Firefox/(\d+)\.#', $ua, $matches) && $matches[1] < 5) {
			// IE 9 미만 또는 Firefox 5 미만의 경우.
			$header = 'filename="' . rawurlencode($filename) . '"';
		}
		elseif (preg_match('#Chrome/(\d+)\.#', $ua, $matches) && $matches[1] < 11) {
			// Chrome 11 미만의 경우.
			$header = 'filename=' . $filename;
		}
		elseif (preg_match('#Safari/(\d+)\.#', $ua, $matches) && $matches[1] < 6) {
			// Safari 6 미만의 경우.
			$header = 'filename=' . $filename;
		}
		elseif (preg_match('#Android #', $ua, $matches)) {
			// 안드로이드 브라우저의 경우. (버전에 따라 여전히 한글은 깨질 수 있다. IE보다 못한 녀석!)
			$header = 'filename="' . $filename . '"';
		}
		else {
			// 그 밖의 브라우저들은 RFC2231/5987 표준을 준수하는 것으로 가정한다.
			// 단, 만약에 대비하여 Firefox 구 버전 형태의 filename 정보를 한 번 더 넣어준다.
			$header = "filename*=UTF-8''" . rawurlencode($filename) . '; filename="' . rawurlencode($filename) . '"';
		}
		
		if (!$expires) {
			// 캐싱이 금지된 경우...
			if ($old_ie) {
				// 익스플로러 8 이하 버전은 SSL 사용시 no-cache 및 pragma 헤더를 알아듣지 못한다.
				// 그냥 알아듣지 못할 뿐 아니라 완전 황당하게 오작동하는 경우도 있으므로
				// 캐싱 금지를 원할 경우 아래와 같은 헤더를 사용해야 한다.
				header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0');
				header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');
			}
			else {
				// 그 밖의 브라우저들은 말을 잘 듣는 착한 어린이!
				header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
				header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');
			}
		}
		else {
			// 캐싱이 허용된 경우...
			header('Cache-Control: max-age=' . (int)$expires);
			header('Expires: ' . gmdate('D, d M Y H:i:s', time() + (int)$expires) . ' GMT');
		}
		
		// 이어받기를 요청한 경우 여기서 처리해 준다.
		if (isset($_SERVER['HTTP_RANGE']) && preg_match('/^bytes=(\d+)-/', $_SERVER['HTTP_RANGE'], $matches)) {
			$range_start = $matches[1];
			if ($range_start < 0 || $range_start > $filesize) {
				header('HTTP/1.1 416 Requested Range Not Satisfiable');
				return false;
			}
			header('HTTP/1.1 206 Partial Content');
			header('Content-Range: bytes ' . $range_start . '-' . ($filesize - 1) . '/' . $filesize);
			header('Content-Length: ' . ($filesize - $range_start));
		}
		else {
			$range_start = 0;
			header('Content-Length: ' . $filesize);
		}
		
		// 나머지 모든 헤더를 전송한다.
		header('Accept-Ranges: bytes');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; ' . $header);
		
		// 출력 버퍼를 비운다.
		// 파일 앞뒤에 불필요한 내용이 붙는 것을 막고, 메모리 사용량을 줄이는 효과가 있다.
		while (ob_get_level()) {
			ob_end_clean();
		}
		
		// 파일을 64KB마다 끊어서 전송하고 출력 버퍼를 비운다.
		// readfile() 함수 사용시 메모리 누수가 발생하는 경우가 가끔 있다.
		$block_size = 16 * 1024;
		$speed_sleep = $speed_limit > 0 ? round(($block_size / $speed_limit / 1024) * 1000000) : 0;
		
		$buffer = '';
		if ($range_start > 0) {
			fseek($fp, $range_start);
			$alignment = (ceil($range_start / $block_size) * $block_size) - $range_start;
			
			if ($alignment > 0) {
				$buffer = fread($fp, $alignment);
				echo $buffer; unset($buffer); flush();
			}
		}
		while (!feof($fp)) {
			$buffer = fread($fp, $block_size);
			echo $buffer; unset($buffer); flush();
			usleep($speed_sleep);
		}
		
		fclose($fp);
		
		// 전송에 성공했으면 true를 반환한다.
		return true;
	}

}//end class
?>