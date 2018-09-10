<?php 
/**
 *  Description : 암호화/복호화 클래스
 */
Class cEncrypt{
	
	static $padded_key = array();

	static function myhex2bin($hexstr){
		$n = strlen($hexstr);
		$sbin = "";
		$i = 0;
		
		while($i < $n){
			$a = substr($hexstr, $i, 2);
			$c = pack("H*",$a);
			if ($i == 0) {
				$sbin = $c;
			} else {
				$sbin .= $c;
			}
			$i+=2;
		}
		
		return $sbin;
	}

	static function toBlockKey($orig_key){
		$key = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";
		
		for( $i=0; $i<strlen($orig_key); $i++ ){
			$key[$i%16] = chr(ord($key[$i%16]) ^ ord($orig_key[$i]));
		}
		
		return $key;
	}

	static function aes_encrypt($value, $key = ""){
		if (!isset(self::$padded_key[$key])){
			self::$padded_key[$key] = self::toBlockKey($key);
		}
		
		return strtoupper(bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, self::$padded_key[$key], $value, MCRYPT_MODE_ECB)));
	}

	static function aes_decrypt($value, $key = ""){
		if (!isset(self::$padded_key[$key])){
			self::$padded_key[$key] = self::toBlockKey($key);
		}
		
		return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, self::$padded_key[$key], self::myhex2bin($value), MCRYPT_MODE_ECB), "\0\4");
	}
}
?>