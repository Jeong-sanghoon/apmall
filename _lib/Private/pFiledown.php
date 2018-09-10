<?
	// ===================================================
	// include
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_lib/Conf/Config.php";
	
	$filename = $cFnc->getReq('file_ori', '');
	$server_filename = $cFnc->getReq('file', '');
	
	$oFile = new cFile('', '', '', 0, 0);
	
	$oFile->DownloadFile($filename, $server_filename, $expires = 0, $speed_limit = 0);
?>