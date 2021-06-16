<?php
namespace devt\siem;

class siem
{
    private $_siemServer = null;

    function __construct($siemServer)
    {
        $this->_siemServer = trim(trim($siemServer),"/");
    }

    private function curl($command,$type,$params=null)
    {
        $url = "https://" . $this->_siemServer . "/" . $command;

        $method = strtoupper($type);
        $str = "";
        if ($method != "GET" && $method != "POST" )
            throw new Exception('siem::curl Parameter type valid, shoudl be GET or POST');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($method == "POST")
        {
            if ( ! $params )
                throw new Exception('siem::curl Type is post and post params are null');

            if (gettype($params) == 'array')
                $str = json_encode($params);
            else
                $str = $params;
            curl_setopt($ch, CURLOPT_POSTFIELDS,$str);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

        $result = curl_exec($ch);
        if ($result)
            $result = json_decode($result,true);
        return $result;
    }

    function createEntry($type,$subtype,$eventnum,$severity,$source,$description="")
    {
        $params = array();
        $params['type'] = $type;
        $params['subtype'] = $subtype;
        $params['eventnum'] = intval($eventnum);
        $params['severity'] = $severity;
        $params['source'] = $source;
        $params['description'] = $description;

        return $this->curl("evant","POST",$params);
    }

    function createSecurityEntry($subtype,$eventnum,$severity,$source,$description="")
    {
        return $this->createEntry("security",$subtype,$eventnum,$severity,$source,$description);
    }

}


//Categories
//Severe
define('SECURITY_RATE_SIGNIN',1001);
//Major
define('SECURITY_ATTEMPTS_USERNAME',2001);
//Minor
define('SECURITY_INVALID_USERNAME',3001);
define('SECURITY_INVALID_PASSWORD',3002);
//Information
define('SECURITY_SIGNIN',4001);

?>