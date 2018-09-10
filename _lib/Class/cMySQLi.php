<?php
/**
*  Description : mysqli DB 클래스
*/
class cMySQLi{
	
	private $oMySQLI = null;
	private $bErrPrint = false; //Error Log Print
	private $nLimitCnt = 10;
	private $nBlockSize = 10;
	private $nTotalPage = 0;
	private $nTotalCnt = 0;
	private $strErrMsg = '';
	
	
	public function __construct($arDbInfo, $ErrPrint = false){
		$oMySQL = new mysqli($arDbInfo["host"], $arDbInfo["user"],$arDbInfo["pass"], $arDbInfo["dbname"]);
		
		if (mysqli_connect_errno()){
			printf("Connect failed: %s\n", mysqli_connect_error());
			exit();
		}

		$this->oMySQLI = $oMySQL;
		$this->execQuery('insert', 'SET NAMES UTF8');
		$this->bErrPrint = $ErrPrint;
	}
	
	
	public  function __destruct(){
		if(is_object($this->oMySQLI))$this->oMySQLI->close();
		$this->oMySQLI = null;
		$this->nLimitCnt = 10;
		$this->nBlockSize = 10;
		$this->nTotalPage = 0;
		$this->nTotalCnt = 0;
	}
	
	
	//param data 받기
	public function refValues($arr){
		$refs = array();

		foreach ($arr as $key => $value){
			$refs[$key] = &$arr[$key];
		}

		return $refs;
	}
	
	
	//데이터 받아오기
	function setRowsData($stmt){
		$meta = $stmt->result_metadata();
		$field = $results = array();
		
		while ($field = $meta->fetch_field()){
			$params[] = &$row[$field->name];
		}//end while
		
		call_user_func_array(array($stmt, 'bind_result'), $params);
		
		while($stmt->fetch()){
			foreach ($row as $key=>$val)$c[$key] = $val;
			$result[] = $c;
		}//end while
		
		return $result;
	}//end fnc
	
	
	//SET Dynamic Bind_param
	public function setDinamicParam($arParam){
		$nParamCnt = count($arParam);
		$arInParam = array();
		$strType = "";
		
		for($i = 0; $i < $nParamCnt; $i++){
			$strType .= $this->_determineType($arParam[$i]);
		}//end for
		
		array_unshift($arParam, $strType);
		
		return $arParam;
	}//end fnc
	
	
	protected function _determineType($item){
		switch (gettype($item)) {
			case 'NULL':
			
			case 'string':
				return 's';
				break;
				
			case 'integer':
				return 'i';
				break;
				
			case 'blob':
				return 'b';
				break;
				
			case 'double':
				return 'd';
				break;
		}
		
		return '';
	}//end fnc
	
	
	//페이징 정보 설정하기
	public function setPagingInfo($nLimit,$nBlock){
		$this->nLimitCnt = $nLimit;
		$this->nBlockSize = $nBlock;
	}
	
	
	/**
	 *  쿼리문 실행
	 *  $strQuery	: 실행할 쿼리문
	 *  $arParam	: ?에 대응하는 파라미터(배열형태)
	 *  $strMode	: insert > 데이터 입력
					  update > 데이터 수정
					  list > 리스트 데이터 조회 결과값 반환
					  data > 1개 데이터 조회 결과값 반환
	 *  $isAddInfo	: insert, update시 반환 데이터 
					  true > insert - insert_id값 / update - 수정된 갯수
					  false > 성공/실패
	 *                
	**/
	public function execQuery($strMode='insert', $strQuery, $arParam='', $isAddInfo = false){
		
		$rtnRslt = -1;//fail
		$isParam = false;
		
		if($arParam != "" && COUNT($arParam) > 0){
			$arInParam = $this->setDinamicParam($arParam);
			$isParam =true;
		}
		
		$stmt = $this->oMySQLI->stmt_init();
		
		if($stmt->prepare($strQuery)){
			if($isParam) call_user_func_array(array($stmt,'bind_param'),$this->refValues($arInParam));
		}
		
		$rslt = $stmt->execute();
		if($stmt->errno != "" && $stmt->errno > 0) $this->strErrMsg = $stmt->error;
		
		switch($strMode){
			case('insert') :
				if($isAddInfo)		$rtnRslt = $stmt->insert_id;
				else				$rtnRslt = $rslt;
				break;
				
			case('update') :
				if($isAddInfo)		$rtnRslt = $stmt->affected_rows;
				else				$rtnRslt = $rslt;
				break;
				
			case('list') :
				$rtnRslt = $this->setRowsData($stmt);
				break;
				
			case('data') :
				$arList =  $this->setRowsData($stmt);
				$rtnRslt = $arList[0];
				break;
		}
		
		$stmt->close();
		
		return $rtnRslt;
	}//end fnc
	
	//카운트 조회
	public function getCntExec($strQuery, $arParam=''){
		if($arParam != "" && COUNT($arParam)>0){
			$arInParam = $this->setDinamicParam($arParam);
			$stmt = $this->oMySQLI->stmt_init();
			
			if($stmt->prepare($strQuery)){
				call_user_func_array(array($stmt,'bind_param'),$this->refValues($arInParam));
				$stmt->execute();
				if($stmt->errno != "" && $stmt->errno > 0) $this->strErrMsg = $stmt->error;
				$stmt->store_result();
				
				if($stmt->errno != "" && $stmt->errno > 0){
					$stmt->close();
					return $stmt->error;
					exit;
				}
				
				$result = $stmt->num_rows;
				$stmt->close();
			}
			else{
				$result = -1;
			}
		}
		else{
			//paramter가 없을 경우.
			$stmt = $this->oMySQLI->stmt_init();
			
			if($stmt->prepare($strQuery)){
				$stmt->execute();
				if($stmt->errno != "" && $stmt->errno > 0) $this->strErrMsg = $stmt->error;
				$stmt->store_result();
				
				if($stmt->errno != "" && $stmt->errno > 0){
					$stmt->close();
					return $stmt->error;
					exit;
				}
				
				$result = $stmt->num_rows;
				$stmt->close();
			}//end if
		}//end if
	
		$this->nTotalCnt = $result;
		$this->nTotalPage = ceil(($this->nTotalCnt )/$this->nLimitCnt);
		$arResult = array("total"=>$this->nTotalCnt,"page"=>$this->nTotalPage);
		
		return $arResult;
	}//end fnc
	
	public function outErrPrint(){
		$rtn = '';
		
		if($this->bErrPrint == true){
			$rtn = $this->strErrMsg;
		}
		else{
			$rtn = 'Error';
		}
		
		return $rtn;
	}//end fnc
	
	public function close(){
		if(is_object($oMySQL))$oMySQL->close();
	}
	
	
	// 트랜젝션
	public function tran(){
		$this->oMySQLI->autocommit(false);
	}
	
	
	// 롤백
	public function rollback(){
		$this->oMySQLI->rollback();
	}
	
	
	// 커밋
	public function commit(){
		$this->oMySQLI->commit();
	}
	 
}//end class
?>