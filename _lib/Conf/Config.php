<?php
/**
 *  Description		: Config.php
 *  Date			: 2017-09-05
 *  Created By Jeong sang hoon
 *  NANOIT TECH. jsh17@nanoit.kr
 */
	$G_PROTOCOL = "http://";
	if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on')	$G_PROTOCOL = "https://";
	
	// Server Setting
	date_default_timezone_set('Asia/Seoul');
	error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
	ini_set("display_errors", 1);


	// Header Info
	//header('Content-Type: application/json; charset=utf-8');		// json페이지
	header('Content-Type: text/html; charset=utf-8');				// 웹페이지
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT');
	header('Cache-Control: no-cache, must-revalidate');
	header('Pragma: no-cache');
	
	
	// Path Info
	define("HTTP_HOST", $_SERVER["HTTP_HOST"]);
	define("DOCUMENT_ROOT", $_SERVER['DOCUMENT_ROOT']);
	define("LIB_DIR", DOCUMENT_ROOT ."/_lib");
	define("CLASS_DIR", LIB_DIR ."/Class");
	define("PRIVATE_DIR", LIB_DIR ."/Private");
	
	define("IMG_URL", $G_PROTOCOL . HTTP_HOST ."/_upload");					// 이미지 URL
	define("UPLOAD_DIR", DOCUMENT_ROOT ."/_upload");					// 업로드 폴더
	
	// Db Info
	$ARR_DB_INFO = array("host" => "mysql:dbname=NS9;host=127.0.0.1", "user" => "ns9", "pass" => "ns9!@34");
	$ARR_DB_INFO_MO = array("host" => "mysql:dbname=mo;host=127.0.0.1", "user" => "root", "pass" => "123qwe");
	$ARR_DB_INFO_APMALL = array("host" => "mysql:dbname=APMALL;host=127.0.0.1", "user" => "apmall", "pass" => "apmall!@34");
	
	
	// Include Info
	include_once(CLASS_DIR . "/cPdo.php");
	include_once(CLASS_DIR . "/cEncrypt.php");
	include_once(CLASS_DIR . "/cFile.php");
	include_once(CLASS_DIR . "/cSendMail.php");
	include_once(CLASS_DIR . "/cLog.php");
	include_once(CLASS_DIR . "/cFnc.php");
	
	include_once(PRIVATE_DIR . "/pCode.php");
	include_once(PRIVATE_DIR . "/pFnc.php");
	
	// Common Set Class
	$cFnc = new cFnc();			// 사용자함수
	$cLog = new cLog();			// 로그함수
	
	// mobile / pc check
	$IS_MOBILE = CHECK_MOBILE($_SERVER['HTTP_USER_AGENT']);
?>