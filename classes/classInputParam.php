<?php
require_once dirname(__FILE__) . "/classSecure.php";

class InputParam
{
    public $parameters;

    function __construct($param,$key)
    {
        $s = Secure::sec_decryptParamPart($param,base64_encode($key));
        if ($s && strlen($s) > 0)
        {
            parse_str($s,$a);
            $this->parameters = $a;
        }
        else
            throw new Exception(__FILE__ . " [".__LINE__."] InputParam - Invalid input, did not decode");
    }

    public static function load($param,$key)
    {
        try {
            return new InputParam($param,$key);
        }
        catch (Exception $e) {
            return null;
        }
    }

    public function __get($name)
    {
        if (isset($this->parameters[$name]))
            return $this->parameters[$name];
        return null;
    }

    public static function encryptFromString($str,$key)
    {
        return Secure::sec_encryptParam($str,base64_encode($key));
    }

    public static function encryptFromArray($a,$key)
    {
        $str = "";
        foreach ($a as $k => $v)
            $str .= "&{$k}={$v}";
        $str = trim($str,"&");
        return Secure::sec_encryptParam($str,base64_encode($key));
    }

}
?>