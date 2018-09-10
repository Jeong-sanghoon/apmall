<?
	// ===================================================
	// include And Init
	// ===================================================
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	$cPdo = new cPdo($ARR_DB_INFO, true);
	
	$oMySQL = new mysqli("127.0.0.1", "ns9", "ns9!@34", "NS9");
	
	echo '<br>start time == '. date('Y-m-d H:i:s');
	$qry = "INSERT INTO ttt (dt) VALUES ";
	for($i = 1; $i <= 10000; $i++){
		$qry .= "(NOW()),";
		
		if($i % 300 == 0){
			$qry = substr($qry, 0, strlen($qry) - 1);
			//echo '<br><br>qry == '. $qry;
			$stmt = $oMySQL->stmt_init();
			$stmt->prepare($qry);
			$rslt = $stmt->execute();
			
			$qry = "INSERT INTO ttt (dt) VALUES ";
		}
	}
	
	$qry = substr($qry, 0, strlen($qry) - 1);
	//echo '<br><br>qry == '. $qry;
	$stmt = $oMySQL->stmt_init();
	$stmt->prepare($qry);
	$rslt = $stmt->execute();
	echo '<br>end time == '. date('Y-m-d H:i:s');
	
?>