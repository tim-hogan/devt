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
        $url = "https://" . $this->_siemServer . "/api/v1/json/" . $command;

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

    public function createEntry($type,$subtype,$eventnum,$severity,$source,$description="")
    {
        $params = array();
        $params['type'] = $type;
        $params['subtype'] = $subtype;
        $params['eventnum'] = intval($eventnum);
        $params['severity'] = $severity;
        $params['source'] = $source;
        $params['description'] = $description;

        return $this->curl("event","POST",$params);
    }

    public function createSecurityEntry($subtype,$eventnum,$severity,$source,$description="")
    {
        return $this->createEntry("security",$subtype,$eventnum,$severity,$source,$description);
    }

    public function createServerStatusEntry($server,$up,$utilisation)
    {
        $params = array();
        $params['server'] = $server;
        $params['up'] = $up;
        $params['utilisation'] = $utilisation;

        return $this->curl("serverstatus","POST",$params);
    }

}


//Categories
//Severe
define('SECURITY_RATE_SIGNIN',1001);

//Major
define('SECURITY_ATTEMPTS_USERNAME',2001);
define('SECURITY_NO_PRIVILEGES',2002);
define('SECURITY_INVALID_API_KEY',2003);
define('SECURITY_API_USER_MISMATCH',2004);
define('SECURITY_WRONG_INSTANCE',2005);
define('SECURITY_API_KEY_RANDOM_MISMATCH',2006);
define('SECURITY_USER_RANDOM_MISMATCH',2007);

//Minor
define('SECURITY_INVALID_USERNAME',3001);
define('SECURITY_INVALID_PASSWORD',3002);
define('SECURITY_NO_SESSIONID',3003);
define('SECUITY_INVALID_CSRF',3004);
define('SECURITY_ROLLING_SCORES',3005);
define('SECURITY_NO_USER_FOR_RANDOMID',3006);
define('SECURITY_INVALID_PHASE',3007);
define('SECURITY_PHASE_NOT_ENABLED',3008);
define('SECURITY_INVALID_QUESTION',3009);
define('SECURITY_TEAM_NOT_ENABLED',3010);
define('SECURITY_CHANGE_PW_TO_SOON',3011);
define('SECURITY_CHANGE_PW_TO_PREVIOUS',3012);

//Information
define('SECURITY_SIGNIN',4001);
define('SECURITY_PASSWORD_CHANGED',4002);
define('SECURITY_NEW_USER',4003);
define('SECURITY_ADMIN_PASSWORD_RESET',4004);
define('SECURITY_ADMIN_UNLOCK_USER',4005);
define('SECURITY_ADMIN_DELETE_USER',4006);
define('SECURITY_ADMIN_UNDELETED_USER',4007);
define('SECURITY_ADMIN_UPDATED_USER',4008);
?>