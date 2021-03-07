<?php
namespace devt\TextMsg;

require_once dirname(__FILE__) . '/classEnv.php';

class TextMessage
{
    private $_sms_url = null;
    function __construct($smsurl=null)
    {
        if ($smsurl)
            $this->_sms_url = $smsurl;
        else
            $this->_sms_url = "https://loc.nz/api/sms/v1/send";
    }

    public function parsePhoneNumber($number,$orgcountrycode)
    {
        $ret = trim($number);
        //If frist char is a + return it
        if (substr($ret,0,1) == "+")
            return $ret;

        if ($orgcountrycode)
        {
            switch ($orgcountrycode)
            {
                case "61": //Australia
                    if (substr($ret,0,1) == "0")
                    {
                        $l = strlen($ret);
                        $ret = '+' . $orgcountrycode . substr($ret,1,$l-1);
                    }
                    return $ret;
                    break;
                case "64": //New Zealand
                    if (substr($ret,0,1) == "0")
                    {
                        $l = strlen($ret);
                        $ret = '+' . $orgcountrycode . substr($ret,1,$l-1);
                    }
                    return $ret;
                    break;
                default:
                    break;
            }
        }
        return $ret;
    }




    public function send($to,$msg,$shelf,$countycode=null,$callback="TextStatus.php")
    {
        $env = new \Environment($shelf,220759);

        if (! $sms_key = $env->getkey("SMS_KEY") )
            throw new \Exception('Missing SMS_KEY from environment variables');

        //Prepare API data
        $postparam = array();
        $postparam['smskey'] = $env->getkey("SMS_KEY");
        $postparam['phone'] = $this->parsePhoneNumber($to,$countycode);
        $postparam['msg'] = $msg;

        if ($countycode)
            $postparam['county_code'] = $countycode;
        else
            $postparam['county_code'] = "64";


        //Prepare Callback URL
        $postparam['callback_url'] = $callback;

        $str = json_encode($postparam);
        $ch = curl_init($this->_sms_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS,$str);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        $result = curl_exec($ch);
        $result = json_decode($result,true);

        return $result;

    }
}
?>