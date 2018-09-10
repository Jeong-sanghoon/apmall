<?
/**
*  Description : pdo DB 클래스
*/
class cPdo{
	private $oPdo = null;
	private $bErrPrint = true; //Error Log Print
	private $nLimitCnt = 10;
	private $nBlockSize = 10;
	private $nTotalPage = 0;
	private $nTotalCnt = 0;
	private $strErrMsg = '';
	
	
	public function __construct($arDbInfo, $ErrPrint = true){
		$this->bErrPrint = $ErrPrint;
		
		try{
			$dbh = new PDO($arDbInfo["host"], $arDbInfo["user"], $arDbInfo["pass"]);
			$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$dbh->exec("set names utf8");
			
			$this->oPdo = $dbh;
		}
		catch(PDOException $pe){
			$this->strErrMsg = "Connection failed : ". $pe->getMessage();
			echo $this->getErrMsg();
			exit;
		}
	}
	
	
	public  function __destruct(){
		$this->close();
		$this->nLimitCnt = 10;
		$this->nBlockSize = 10;
		$this->nTotalPage = 0;
		$this->nTotalCnt = 0;
	}
	
	
	public function getErrMsg(){
		$rtn = '';
		
		if($this->bErrPrint == true){
			$rtn = $this->strErrMsg;
		}
		else{
			$rtn = 'DB Error';
		}
		
		return $rtn;
	}//end fnc
	
	
	public function close(){
		if(is_object($this->oPdo)) $this->oPdo = null;
	}
	
	
	// 트랜젝션
	public function tran(){
		$this->oPdo->beginTransaction();
	}
	
	
	// 롤백
	public function rollback(){
		$this->oPdo->rollBack();
	}
	
	
	// 커밋
	public function commit(){
		$this->oPdo->commit();
	}
	
	
	//페이징 정보 설정하기
	public function setPagingInfo($nLimit,$nBlock){
		$this->nLimitCnt = $nLimit;
		$this->nBlockSize = $nBlock;
	}
	
	
	//파라미터 형식에 따라 pdo에서의 데이터형 명시
	public function setDinamicParam($arParam){
		$nParamCnt = count($arParam);
		$arInParam = array();
		$strType = "";
		
		for($i = 0; $i < $nParamCnt; $i++){
			$strType = $this->_determineType($arParam[$i]);
			array_push($arInParam, $strType);
		}//end for
		
		return $arInParam;
	}//end fnc
	
	
	// 파라미터의 형식 체크
	protected function _determineType($item){
		$rtn = '';
		//echo '<br>type == '. gettype($item);
		switch (gettype($item)) {
			case 'NULL':
				$rtn = PDO::PARAM_NULL;
				break;
			
			case 'string':
				$rtn = PDO::PARAM_STR;
				break;
				
			case 'integer':
				$rtn = PDO::PARAM_INT;
				break;
				
			case 'blob':
				$rtn = PDO::PARAM_LOB;
				break;
			
			case 'double':
				$rtn = PDO::PARAM_STR;
				break;
				
			default :
				$rtn = '';
				break;
		}
		
		return $rtn;
	}//end fnc
	
	
	//데이터 받아오기
	function setRowsData($stmt){
		$result = array();
		$i = 0;
		
		while($row = $stmt->fetch(PDO::FETCH_OBJ)){
			foreach($row as $key=>$val){
				$result[$i][$key] = $val;
			}
			
			$i++;
		}//end while
		
		return $result;
	}//end fnc
	
	
	//카운트 조회
	public function getCntExec($strQuery, $arParam = ''){
		$arResult = array();
		$arResult['status'] = 1;
		$arResult['msg'] = "";
		
		try{
			if($arParam != "" && COUNT($arParam)>0){
				$arInParam = $this->setDinamicParam($arParam);
				
				$stmt = $this->oPdo->prepare($strQuery, array(PDO::ATTR_CURSOR=>PDO::CURSOR_FWDONLY));
				
				foreach($arParam as $i=>$val){
					$idx = $i + 1;
					$stmt->bindValue($idx, $val, $arInParam[$i]);
				}
				
				$stmt->execute();
				$this->nTotalCnt = $stmt->rowCount();
			}
			else{
				//paramter가 없을 경우.
				$stmt = $this->oPdo->prepare($strQuery, array(PDO::ATTR_CURSOR=>PDO::CURSOR_FWDONLY));
				$stmt->execute();
				$this->nTotalCnt = $stmt->rowCount();
			}//end if
			
			$this->nTotalPage = ceil(($this->nTotalCnt )/$this->nLimitCnt);
			$arResult['data'] = array("total" => $this->nTotalCnt, "page" => $this->nTotalPage);
		}
		catch(PDOException $pe){
			$this->strErrMsg = "SYSTEM ERROR : ". $pe->getMessage();
			$arResult['status'] = 0;
			$arResult['msg'] = $this->getErrMsg();
		}
		
		return $arResult;
	}//end fnc
	
	
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
	public function execQuery($strMode = 'insert', $strQuery, $arParam = ''){
		
		$arResult = array();
		$arResult['status'] = 1;
		$arResult['msg'] = "";
		
		try{
			if($arParam != "" && COUNT($arParam) > 0){
				$arInParam = $this->setDinamicParam($arParam);
			}
			
			$stmt = $this->oPdo->prepare($strQuery, array(PDO::ATTR_CURSOR=>PDO::CURSOR_FWDONLY));
			
			foreach($arParam as $i=>$val){
				$idx = $i + 1;
				$stmt->bindValue($idx, $val, $arInParam[$i]);
			}
			
			$stmt->execute();
			
			switch($strMode){
				case('insert') :
					$arResult['data']['count'] = $stmt->rowCount();
					$arResult['data']['insert_id'] = $this->oPdo->lastInsertId();
					break;
					
				case('update') :
					$arResult['data']['count'] = $stmt->rowCount();
					break;
					
				case('list') :
					$arResult['data'] = $this->setRowsData($stmt);
					break;
					
				case('data') :
					$arList =  $this->setRowsData($stmt);
					$arResult['data'] = $arList[0];
					break;
			}
		}
		catch(PDOException $pe){
			$this->strErrMsg = "SYSTEM ERROR : ". $pe->getMessage();
			$arResult['status'] = 0;
			$arResult['msg'] = $this->getErrMsg();
		}
		
		return $arResult;
	}//end fnc
}
?>