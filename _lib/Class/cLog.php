<?
/**
 *  Description : Log4php Function
 */
include_once LIB_DIR ."/_log4php/Logger.php";

class cLog extends Logger{
	
	var $logger;
	
	public function __construct() {
		Logger::configure(LIB_DIR ."/Conf/log4php.properties");
		$this->logger = Logger::getLogger($_SERVER['PHP_SELF']);
	}
	
	function logtrace($msg){
		$this->logger->trace($msg);
	}
	
	function logdebug($msg){
		$this->logger->debug($msg);
	}
	
	function loginfo($msg){
		$this->logger->info($msg);
	}
	
	function logwarn($msg){
		$this->logger->warn($msg);
	}
	
	function logerror($msg){
		$this->logger->error($msg);
	}
	
	function logfatal($msg){
		$this->logger->fatal($msg);
	}
}
?>
