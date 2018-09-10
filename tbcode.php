<?
	include_once $_SERVER['DOCUMENT_ROOT'] ."/_admin/include/common.php";
	
	$tb = $cFnc->getReq('tb');	
	
	$cPdo = new cPdo($ARR_DB_INFO);
	
	$qry = "
		desc ".$tb."
	";
	$cLog->logdebug($qry); $cLog->logdebug(json_encode($arParam));
	$result = $cPdo->getCntExec($qry, $arParam);	
	$dsCount = $result['data'];		// 전체카운트
	

	$result = $cPdo->execQuery('list', $qry, $arParam);
	$rsList = $result['data'];


	echo 'tablename == ' .$tb."<br><br/><br/>";


	echo "start get Parameter =========================<br/>";
	foreach($rsList as $i=>$ds){		
		echo '$'.$ds['Field'] . '  = $cFnc->getReq("'.$ds['Field'].'");<br/>';
	}
	

	
	echo "<br/><br/><br/>";
	echo "Array Create Parameter =========================<br/>";
	echo '$arParam = array();';
	echo "<br/>";
	foreach($rsList as $i=>$ds){		
		echo 'array_push( $arParam, $'.$ds['Field'].');<br/>';
	}



	// echo "size==".sizeof($rsList)."<br>";
	$rscnt = sizeof($rsList);

	echo "<br/>";
	$j = 1;
	echo "1.select Query String Make<br/>";
	echo 'SELECT ';

	foreach($rsList as $i=>$ds){			
		echo $ds['Field'];		
		if($j == $rscnt ){
			echo " ";			
		}
		else {
			echo ", ";			
			// echo $j;
			
		}
		$j++;	

	}
	echo '<BR/>FROM '.$tb;


	echo "<br/>";
	echo "<br/>";
	echo "<br/>";
	$j = 1;
	echo "2.INSERT Query String Make<br/>";
	echo 'INSERT INTO '.$tb.' ( ';
	foreach($rsList as $i=>$ds){			
		echo $ds['Field'];		
		if($j == $rscnt ){
			echo " ";			
		}
		else {
			echo ", ";			
		}
		$j++;	
	}
	echo ')';
	echo "<br/>";
	$j = 1;
	echo "VALUES (<br/>";
	foreach($rsList as $i=>$ds){			
		if($j == 1){
			echo "?<br/>";
		}
		else if($j == $rscnt ){
			echo ",?<br/> ";			
		}
		else {
			echo ",?<br/>";			
		}
		$j++;					
	}
	echo ")";




	$array_key = array();
	echo "<br/>";
	echo "<br/>";
	echo "<br/>";
	$j = 1;
	echo "3.update Query String Make<br/>";
	echo 'UPDATE '.$tb.' SET <br/>';
	foreach($rsList as $i=>$ds){					
		echo $ds['Field'] .' = ?<br/>';		
		if($j == $rscnt ){
			echo " ";			
		}
		else {
			echo ", ";			
		}
		$j++;		
		if($ds['Key'] == 'PRI'){
			array_push($array_key,$ds['Field']);
		}
	}

	echo 'WHERE ';	
	$j = 1;
	foreach($array_key as $f=>$item){						
		if($j>1){
			echo ' AND ' .$item. ' = ?';	
		} else {
			echo $item. ' = ?';	
		}
		$j++;
	}
	echo "<br/>";


	echo "<br/>";
	echo "<br/>";
	echo "<br/>";
	$j = 1;
	$array_key = array();
	echo "4.DELETE Query String Make<br/>";
	echo 'DELETE FROM '.$tb;
	foreach($rsList as $i=>$ds){
		if($ds['Key'] == 'PRI'){
			array_push($array_key,$ds['Field']);
		}
		$j++;	
	}
	echo ' WHERE ';	
	$j = 1;
	foreach($array_key as $f=>$item){						
		if($j>1){
			echo ' AND ' .$item. ' = ?';	
		} else {
			echo $item. ' = ?';	
		}
		$j++;
	}
	echo "<br/>";




	echo "<br/>";
	echo "<br/>";
	echo "<br/>";

	echo "<br/>";
	echo "<br/>";
	echo "<br/>";	



	// echo json_encode($result);
	$cPdo->close();
?>