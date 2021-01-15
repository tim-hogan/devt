<?php
require "./includes/classSMSDB.php";
class classSMS
{
    private $gatewayURL;
    private $application;
    private $customer;
    private $password;
    private $_remote;
    private $_country_code;

    function __construct($remote=false,$country_code=64)
    {
        /*
         * Note if remote is set then SMS messages are sent via an API to a remote server
         * Country code is only used for remote
         *
         */
        $this->_remote = $remote;
        $this->_country_code = $country_code;
        if (!$this->_remote)
        {
            $con_params = require('./config/database.php'); $con_params = $con_params['64f7bd7e'];
            $DB = new smsDB($con_params);
            $params = $DB->getGatewayParams();
            $this->gatewayURL = $params['tg_url'];
            $this->application = $params['tg_app_name'];
            $this->customer = $params['tg_customer'];
            $this->password = $params['tg_password'];
        }
    }

    //*********************************************************************
    // Diagnostic
    //*********************************************************************
    private function var_error_log( $object=null ,$text='')
    {
        ob_start();                    // start buffer capture
        var_dump( $object );           // dump the values
        $contents = ob_get_contents(); // put the buffer into a variable
        ob_end_clean();                // end capture
        error_log( "{$text} {$contents}" );        // log contents of the result of var_dump( $object )
    }

    public function parsePhoneNumber($number,$orgcountrycode)
    {

        $ret = trim($number);
        //If frist char is a + return it
        if (substr($ret,0,1) == "+")
            return $ret;

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

        return $ret;
    }


    private function SendText($to,$msg)
    {
        $strTo = trim($to);
        $strTo = trim($strTo,"+");
        $ch = curl_init();
        $url = $this->gatewayURL .
                "?application=" . $this->application.
                "&password=".$this->password.
                "&customer=".$this->customer.
                "&class=mt_message".
                "&content=" .
                urlencode($msg) .
                "&destination=%2b" .
                urlencode($strTo);


        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }


    public function createAndSend($srcKey,$to,$msg,$srcID=null,$callback=null)
    {
        if ($this->_remote)
        {
            $postparam = array();
            $postparam['smskey'] = $srcKey;
            $postparam['phone'] = $to;
            $postparam['msg'] = $msg;
            $postparam['srcid'] = $srcID;
            $postparam['callback_url'] = $callback;
            $postparam['county_code'] = strval($this->_country_code);

            $str = json_encode($postparam);
            $url = "https://loc.nz/api/sms/v1/send";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS,$str);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
            $result = curl_exec($ch);
            $result = json_decode($result,true);
            if (isset($result['meta']))
            {
                if ($result['meta'] ['status'] == "OK")
                {
                    $data = $result['data'];
                    $ret_result = array();
                    $ret_result['textid'] = $data['textid'];
                    $ret_result['status'] = $data['status'];
                    return $ret_result;

                }
            }
        }
        else
        {
            $con_params = require('./config/database.php'); $con_params = $con_params['64f7bd7e'];
            $DB = new smsDB($con_params);

            if ($textsource = $DB->getTextSourceByKey($srcKey))
            {
                if ($DB->createTextMsg($textsource['idtextsource'],$srcID,'Send',$to,$msg,'Queued',$callback))
                {
                    $textid = $DB->insert_id;
                    $rslt= '';
                    try
                    {
                        $rslt = $this->SendText($to,$msg);
                    }
                    catch(Exception $e)
                    {
                        error_log("Catch exception in classSMS when sending text.");
                        $rslt = '';
                    }
                    if (strlen($rslt) > 0)
                    {
                        if (substr(strtoupper($rslt),0,5) == "ERROR")
                        {
                            error_log("SMS Send error to server:  Response from server = " . $rslt . " Sent to: " . $to . " Message " . $msg);
                            $rslt = '';
                        }
                    }
                    $rslt_status = true;

                    if (strlen($rslt) == 0)
                    {
                        $DB->setFailedSend($textid);
                        $rslt_status = false;
                    }
                    else
                        $DB->setSendingSend($textid,$rslt);

                    $ret_result = array();
                    $ret_result['textid'] = $textid;
                    $ret_result['status'] = $rslt_status;
                    return $ret_result;
                }
            }
            else
                error_log("Not text source you need to define in database a text source");
        }
        return 0;
    }
}
?>