<?php
require_once (dirname(realpath(__FILE__)) ."/classEnvironment.php");
require_once (dirname(realpath(__FILE__)) ."/securityParams.php");

class Secure
{
	private $pepper = null;

	function __construct()
	{
		global $devt_environment;
		$this->pepper = $devt_environment->getkey('PEPPER');
	}

	public static function var_error_log( $object=null,$text='')
	{
		ob_start();
		var_dump( $object );
		$contents = ob_get_contents();
		ob_end_clean();
		error_log( "{$text} {$contents}" );
	}

	public static function isHTTPS()
	{
		if (empty($_SERVER['HTTPS']))
		{
			header('Location: https://' . $_SERVER["SERVER_NAME"] . htmlspecialchars($_SERVER["PHP_SELF"]));
			return false;
		}
		return true;
	}

	public static function usingHTTPS()
	{
		if (empty($_SERVER['HTTPS']))
			return false;
		return true;
	}

	public static function createRandomPW($length = 6)
	{
		$p = '';
		$characters = '23456789abcdefghjkmnprstuwxyzABCDEFGHJKLMNPQRSTUWXYZ';
		for($i = 0 ; $i < $length; $i++)
		{
			$p .= substr($characters, rand(0,51) , 1);
		}
		return strval($p);
	}

	public static function createRandomInt($length = 9)
	{
		$val = '';
		for ($c=0;$c<($length/4);$c++)
			$val .= sprintf("%04d",rand(0,9999));
		$val = intval(substr($val,0,$length));
		return $val;
	}

	public static function createRandomDigits($length = 6)
	{
		$val = '';
		for ($c=0;$c<($length/4);$c++)
			$val .= sprintf("%04d",rand(0,9999));
		return strval(substr($val,0,$length));
	}

	public static function createKey($length)
	{
		$val = '';
		for ($c=0;$c<($length/4);$c++)
			$val .= sprintf("%04d",rand(0,9999));
		return substr($val,0,$length);
	}

	public static function strongPassword($pwd,$length=8,$numCapital=1,$numLower=1,$numNumbers=1,$numNonAlphaNum=0)
	{
		$errtxt = '';
		if ($length > 0 && strlen($pwd) < intval($length))
		{
			$errtxt .= "Password too short must be {$length} characters ";
		}

		if ($numNumbers > 0 && strlen(preg_replace('![^0-9]+!', '', $pwd)) < $numNumbers)
		{
			if ($numNumbers > 1)
				$errtxt .= ", Password must include at least {$numNumbers} numbers ";
			else
				$errtxt .= ", Password must include at least 1 number ";
		}

		if ($numLower > 0 && strlen(preg_replace('![^a-z]+!', '', $pwd)) < $numLower)
		{
			if ($numLower > 1)
				$errtxt .= ", Password must include at least {$numLower} lower case letters ";
			else
				$errtxt .= ", Password must include at least 1 lower case letter ";
		}

		if ($numCapital > 0 && strlen(preg_replace('![^A-Z]+!', '', $pwd)) < $numCapital)
		{
			if ($numCapital > 1)
				$errtxt .= ", Password must include at least {$numCapital} upper case letters ";
			else
				$errtxt .= ", Password must include at least 1 upper case letter ";
		}

		if ($numNonAlphaNum > 0 && strlen(preg_replace('![0-9a-zA-Z]+!', '', $pwd)) < $numNonAlphaNum)
		{
			if ($numNonAlphaNum > 1)
				$errtxt .= ", Password must include at least {$numNonAlphaNum} non alphanumeric character ";
			else
				$errtxt .= ", Password must include at least 1 non alphanumeric character ";
		}

		$errtxt = trim($errtxt,",");
		$errtxt = trim($errtxt);
		return $errtxt;
	}

	public function passwordHash($pw,$salt)
	{
		return hash('sha256',$pw . hash('sha256', $salt . $this->pepper));
	}

	public static function createSalt()
	{
		return bin2hex(openssl_random_pseudo_bytes(32));
	}

	public function createHashAndSalt($pw)
	{
		$ret = array();
		$ret["salt"] = self::createSalt();
		$ret['hash'] = $this->passwordHash($pw,$ret["salt"]);
		return $ret;
	}

	public function createsecondfactor($length,$salt)
	{
		$val = '';
		for ($c=0;$c<($length/4);$c++)
			$val .= sprintf("%04d",rand(0,9999));
		$val = substr($val,0,$length);
		$hash = $this->passwordHash($val,$salt);
		$ret = array();
		$ret['p'] = $val;
		$ret['h'] = $hash;
		return $ret;
	}

	public function checkPassword($pw,$hash,$salt)
	{
		if ($this->passwordHash($pw,$salt) === $hash)
			return true;
		return false;
	}

	//Session based routines
	static public function isSignedIn()
	{
		if (isset($_SESSION['userid']))
			return true;
		return false;
	}

	static public function SigninAndRedirect()
	{
		$_SESSION['SinginRedirect'] = basename($_SERVER["REQUEST_URI"]);
		header('Location: Signin.php');
	}

	static public function CheckSecurity($seclevel)
	{
		$uid = 0;
		if(isset($_SESSION['userid']))
			$uid=intval($_SESSION['userid']);

		if ($uid > 0)
		{
			if (isset($_SESSION['security']))
			{
				if (intval($_SESSION['security']) & intval($seclevel))
					return true;
			}
		}
		return false;
	}

	static public function CheckUserSecurity($user,$seclevel)
	{
		if ($user)
		{
			if (gettype($user) == 'array' && intval($user['user_security']) & intval($seclevel))
				return true;
			if (gettype($user) == 'object' && intval($user->user_security) & intval($seclevel))
				return true;
		}
		return false;
	}

	static public function CheckPage($seclevel,$error_page="SecurityError.php")
	{
		if (!Secure::isHTTPS())
			exit();
		if (!Secure::isSignedIn())
		{
			Secure::SigninAndRedirect();
			exit();
		}
		if (!Secure::CheckSecurity($seclevel))
		{
			header("Location: {$error_page}");
			exit();
		}
	}

	static public function isUserVerified($user)
	{
		if ($user && isset($user['user_verified']))
		{
			if ($user['user_verified'])
				return true;
		}
		return false;
	}

	static public function CheckPage2($user,$seclevel,$error_page="SecurityError.php")
	{
		if (!Secure::isHTTPS())
			exit();
		if (!$user)
		{
			Secure::SigninAndRedirect();
			exit();
		}
		if (!Secure::isSignedIn())
		{
			Secure::SigninAndRedirect();
			exit();
		}
		if (!Secure::CheckUserSecurity($user,$seclevel))
		{
			header("Location: {$error_page}");
			exit();
		}
	}

	static public function checkCSRF($session_name="csrf_key",$token_name="formtoken")
	{
		if (isset($_SESSION[$session_name]))
		{
			if (isset($_POST[$token_name]))
			{
				if ($_POST[$token_name] == $_SESSION[$session_name])
					return true;
			}
			if (isset($_GET[$token_name]))
			{
				if ($_GET[$token_name] == $_SESSION[$session_name])
					return true;
			}
		}
		return false;
	}

	/**
	 * Summary of cleanAndCheckMime
	 * @param mixed $m The mime to test
	 * @param mixed $allowAsterix Allow an Asterix as a suffix
	 * @return mixed NULL if invcalid else a cleaned mime
	 */
	public static function cleanAndCheckMime($m,$allowAsterix=false)
	{
		$mime = trim($m);
		if ($allowAsterix)
			$mime = preg_replace('![^0-9a-zA-Z\/*\-\.\+]+!', '', $mime);
		else
			$mime = preg_replace('![^0-9a-zA-Z\/\-\.\+]+!', '', $mime);
		//Count slashes the should be at least one.
		if (strlen(preg_replace('![^\/]+!', '', $mime)) != 1)
			return null;
		//Check there is something before and after the slash
		$l = strlen($mime);
		$k = strpos($mime,"/");
		if ($k ==0 || $k == $l -1)
			return null;
		//check that the asterix is the last character
		$j = strpos($mime,"*");
		if ($j !== false && $j != $l -1)
			return null;
		return $mime;
	}

	static public function encryptarray($params,$key,$url_encode=true)
	{
		// Remove the base64 encoding from our key
		if (is_array($params))
		{
			$encryption_key = base64_decode($key);
			$data = "FFFF" . json_encode($params);
			$iv = null;
			$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
			$encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);
			$result = base64_encode($encrypted . '::' . $iv);
			if ($url_encode)
				$result = urlencode($result);
			return $result;
		}
		return null;
	}

	static public function decrypttoarray($str,$key)
	{
		if ($str && strlen($str) > 0)
		{
			$encryption_key = base64_decode($key);
			list($encrypted_data, $iv) = explode('::', base64_decode($str), 2);
			$decdata = openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
			if (substr($decdata,0,4) == 'FFFF')
			{
				return json_decode(substr($decdata,4),true);
			}
		}
		return null;
	}

	static public function sec_encryptParam($v,$key)
	{
		// Remove the base64 encoding from our key
		if ($key)
		{
			$flag = "FFFF";
			$data = $flag . (string) $v;
			$encryption_key = base64_decode($key);

			// Generate an initialization vector
			$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
			// Encrypt the data using AES 256 encryption in CBC mode using our encryption key and initialization vector.
			$encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);
			// The $iv is just as important as the key for decrypting, so save it with our encrypted data using a unique separator (::)
			$result = urlencode(base64_encode($encrypted . '::' . $iv));
			return $result;
		}
		else
			return null;
	}

	static public function sec_decryptParamPart($data,$key)
	{
		if ($key)
		{

			// Remove the base64 encoding from our key
			$encryption_key = base64_decode($key);
			// To decrypt, split the encrypted data from our IV - our unique separator used was "::"
			list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);

			if (strlen($iv) != 16)
			{
				error_log("Error in sec_decryptParamPart iv wrong length, backtrace follows");
				Secure::var_error_log(debug_backtrace(),"backtrace");
				return null;
			}

			$de =  openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
			if (substr($de,0,4) == 'FFFF')
			{
				return substr($de,4,strlen($de)-4);
			}
		}
		return null;
	}

	static public function encryptAttachment($strfileIn,$strfileOut,$key)
	{
		$encryption_key = base64_decode($key);
		$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
		if ($hread = fopen($strfileIn, "r") )
		{
			if ($hwrite = fopen($strfileOut,"w"))
			{
				fwrite($hwrite,$iv,16);
				$encrypt_data = openssl_encrypt(fread($hread, filesize($strfileIn)), 'aes-256-cbc', $encryption_key, OPENSSL_RAW_DATA, $iv);
				fwrite($hwrite,$encrypt_data,strlen($encrypt_data));
				fclose($hwrite);
				fclose($hread);
				return true;
			}
		}
		return false;
	}

	static public function decryptAttachment($strfileIn,$key)
	{
		$encryption_key = base64_decode($key);
		if ($hread = fopen($strfileIn, "r") )
		{
			$l = filesize($strfileIn);
			$iv = fread($hread,16);
			$l -= 16;
			//$edata = fread($hread,$l);
			$rdata = openssl_decrypt(fread($hread,$l), 'aes-256-cbc', $encryption_key, OPENSSL_RAW_DATA, $iv);
			if ($rdata)
			{
				return $rdata;
			}
		}
		return false;
	}
}

class CookieJar
{
	private $_name;
	private $_key;

	function __construct($name=null)
	{
		global $devt_environment;
		$this->_name=$name;
		$this->_key = $devt_environment->getkey('COOKIE_KEY');
	}

	private function var_error_log( $object=null,$text='')
	{
		ob_start();
		var_dump( $object );
		$contents = ob_get_contents();
		ob_end_clean();
		error_log( "{$text} {$contents}" );
	}

	public function encode($data,$seconds=3600)
	{
		$encoded = Secure::sec_encryptParam(urlencode($data),base64_encode($this->_key));
		if ($seconds == 0)
			$options['expires'] = 0;
		else
			$options['expires'] = time() + $seconds;
		$options['path'] = "/";
		$options['secure'] = true;
		$options['httponly'] = true;
		$options['samesite'] = "Strict";

		setcookie($this->_name, $encoded, $options);

	}

	//This function is depricated
	public function deocde()
	{
		if (isset($_COOKIE[$this->_name]))
		{
			$s = Secure::sec_decryptParamPart(urldecode($_COOKIE[$this->_name]),base64_encode($this->_key));
			$a = array();
			parse_str(urldecode($s),$a);
			return $a;
		}
	}

	public function decode()
	{
		if (isset($_COOKIE[$this->_name]))
		{
			$s = Secure::sec_decryptParamPart(urldecode($_COOKIE[$this->_name]),base64_encode($this->_key));
			$a = array();
			parse_str(urldecode($s),$a);
			return $a;
		}
		return null;
	}

	public function delete()
	{
		$options['expires'] = time() - 300;
		$options['path'] = "/";
		$options['secure'] = true;
		$options['httponly'] = true;
		$options['samesite'] = "Strict";

		setcookie($this->_name, "", $options);
	}
}

class Session
{
	private $_id;
	private $_cookie;
	private $_data;
	private $_callbackclass;
	private $_getter;
	private $_setter;
	private $_persistant;

	/**
	 * Summary of __construct
	 * @param class $callbackclass
	 * @param string $getter
	 * @param string $setter
	 * @param boolean $persistant
	 * Create a session.  The call backs are used to get and session the data.
	 * If persistant is set then the cookie used to target the session wil pbe persistant for 1 year.
	 */
	function __construct($callbackclass,$getter,$setter,$persistant=false)
	{
		$this->_id = null;
		$this->_data = null;
		$this->_callbackclass = $callbackclass;
		$this->_getter = $getter;
		$this->_setter = $setter;
		$this->_persistant = $persistant;

		$this->_cookie = new CookieJar("DEVTSE");
		if ($cookie = $this->_cookie->decode())
		{
			if (isset($cookie['u']))
			{
				$this->_id = $cookie['u'];
			}
			if (isset($cookie['p']) && intval($cookie['p']) == 1)
				$this->_persistant = true;
		}
		if ($this->_id)
		{
			if ($this->_callbackclass)
			{
				$this->_data = call_user_func(array($this->_callbackclass, $this->_getter),$this->_id);
			}
			else
			{
				$this->_data = $this->_getter($this->_id);
			}

		}
	}

	function __destruct()
	{
		$this->save();
	}

	public function isAvailable()
	{
		return ($this->_id) ? true : false;
	}

	public function Id()
	{
		return $this->_id;
	}

	public function setPersistent($v=true)
	{
		$this->_persistant = boolval($v);
		$seconds = 0;
		if ($this->_persistant)
			$seconds = (3600*24*366);
		$strP = ($this->_persistant) ? "1" : "0";
		if ($this->_id)
			$this->_cookie->encode("u={$this->_id}&p={$strP}",$seconds);
	}

	public function create($id)
	{
		$seconds = 0;
		if ($this->_persistant)
			$seconds = (3600*24*366);
		if (! $this->_id || $this->_id != $id) {
			$this->_id = $id;
			$strP = ($this->_persistant) ? "1" : "0";
			$this->_cookie->encode("u={$id}&p={$strP}",$seconds);
			if ($this->_callbackclass)
			{
				$this->_data = call_user_func(array($this->_callbackclass, $this->_getter),$this->_id);
			}
			else
			{
				$this->_data = $this->_getter($this->_id);
			}

		}
	}

	public function unsetAll()
	{
		$this->_data = null;
	}

	public function save()
	{
		if ($this->_id)
		{
			if ($this->_callbackclass)
			{
				call_user_func(array($this->_callbackclass, $this->_setter),$this->_id,$this->_data);
			}
			else
			{
				$this->_setter($this->_id,$this->_data);
			}
		}
	}

	public function delete()
	{
		$this->_data = null;
		$this->save();
		$this->_cookie->delete();
	}

	public function createNewCSRF()
	{
		$this->csrf_key = base64_encode(openssl_random_pseudo_bytes(32));
		$_SESSION["csrf_key"] = $this->csrf_key;
	}

	public function checkCSRF()
	{
		//error_log("Check CSRF Key this->csrf_key = {$this->csrf_key} post formtoken = {$_POST["formtoken"]} get formtoken = {$_GET["formtoken"]}");

		if ($this->csrf_key)
		{
			if (isset($_POST["formtoken"]))
				return $_POST["formtoken"] == $this->csrf_key;
			if (isset($_GET["formtoken"]))
				return $_GET["formtoken"] == $this->csrf_key;
		}
		return false;
	}

	public function push($stackname,$value)
	{
		if (isset($this->_data[$stackname]))
			$this->_data[$stackname] = array();
		$this->_data[$stackname] [] = $value;
	}

	public function pop($stackname)
	{
		if (isset($this->_data[$stackname]))
		{
			if (count($this->_data[$stackname]) > 0)
				return array_pop($this->_data[$stackname]);
		}
		return null;
	}

	public function __set($name,$value)
	{
		$this->_data[$name] = $value;
	}

	public function __get($name)
	{
		if (isset($this->_data[$name]))
			return $this->_data[$name];
		return null;
	}
}
?>