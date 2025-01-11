<?php
namespace devt\dns;
/**
 * Class dns is used to mange and condfigure the deVT dns servcie based on bind
 * @author Tim Hogan
*/
class dns
{
    private $_dnsServer = null;

    function __construct($dnsServer)
    {
        $this->_dnsServer = $dnsServer;
    }

    private function var_error_log( $object=null ,$text='')
    {
        ob_start();
        var_dump( $object );
        $contents = ob_get_contents();
        ob_end_clean();
        error_log( "{$text} {$contents}" );
    }

    private function curl($url,$type,$params=null)
    {
        error_log("dns::curl In curl");
        $method = strtoupper($type);
        $str = "";
        if ($method != "GET" && $method != "POST" )
            throw new Exception('ns::curl Parameter type valid, shoudl be GET or POST');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($method == "POST")
        {
            if ( ! $params )
                throw new Exception('dns::curl Type is post and post params are null');

            if (gettype($params) == 'array')
                $str = json_encode($params);
            else
                $str = $params;
            curl_setopt($ch, CURLOPT_POSTFIELDS,$str);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

        error_log("dns::curl About to send");

        $result = curl_exec($ch);

        error_log("dns::curl result follows");
        $this->var_error_log($result, "result");

        if ($result)
            $result = json_decode($result,true);
        return $result;
    }

    public function addDomain($domain,$ipaddress)
    {
        $rslt = array();

        $params = array();
        $params['domain'] = $domain;
        $params['ipaddress'] = $ipaddress;

        $url = "https://{$this->_dnsServer}/api/v1/json/newdomain";

        try
        {
            $r = $this->curl($url,"POST",$params);
        }

        catch (Exception $e)
        {
            error_log("dns::addDomain [". __LINE__ . "] Exception thrown: {$e}");
            $rslt['status'] = false;
            $rslt['error'] = "dns::addDomain [__LINE__] Exception thrown: {$e}";
            return $rslt;
        }

        if (!$r || !isset($r['meta']) || !isset($r['meta'] ['status']) )
        {
            error_log("dns::addArecord [". __LINE__ . "] Null rslt returned");
            $rslt['status'] = false;
            $rslt['error'] = "dns::addArecord [". __LINE__ . "] Null rslt returned";
            return $rslt;

        }

        if ($r['meta'] ['status'])
        {
            $rslt['status'] = true;
        }
        else
        {
            $rslt['status'] = false;
            $rslt['error'] = $r['meta'] ['errormsg'];
        }

        return $rslt;

    }

    public function addARecord($domain,$subdomain,$ttl,$ipaddress)
    {
        $rslt = array();
        $params = array();
        $params['type'] = "A";
        $params['domain'] = $domain;
        $params['name'] = $subdomain;
        $params['ttl'] = $ttl;
        $params['ipaddress'] = $ipaddress;

        $url = "https://{$this->_dnsServer}/api/v1/json/add";


        try
        {
            $r = $this->curl($url,"POST",$params);
        }

        catch (Exception $e)
        {
            error_log("dns::addArecord [". __LINE__ . "] Exception thrown: {$e}");
            $rslt['status'] = false;
            $rslt['error'] = "dns::addArecord [__LINE__] Exception thrown: {$e}";
            return $rslt;
        }

        $this->var_error_log($r,"r");

        if (!$r || !isset($r['meta']) || !isset($r['meta'] ['status']) )
        {
            error_log("dns::addArecord [". __LINE__ . "] Null rslt returned");
            $rslt['status'] = false;
            $rslt['error'] = "dns::addArecord [". __LINE__ . "] Null rslt returned";
            return $rslt;

        }

        if ($r['meta'] ['status'])
        {
            $rslt['status'] = true;
        }
        else
        {
            $rslt['status'] = false;
            $rslt['error'] = $r['meta'] ['errormsg'];
        }

        return $rslt;
    }

    public function editARecord($domain,$subdomain,$ttl,$ipaddress)
    {
        $rslt = array();
        $params = array();
        $params['type'] = "A";
        $params['domain'] = $domain;
        $params['name'] = $subdomain;
        $params['ttl'] = $ttl;
        $params['ipaddress'] = $ipaddress;

        $url = "https://{$this->_dnsServer}/api/v1/json/edit";


        try
        {
            $r = $this->curl($url,"POST",$params);
        }

        catch (Exception $e)
        {
            error_log("dns::editArecord [". __LINE__ . "] Exception thrown: {$e}");
            $rslt['status'] = false;
            $rslt['error'] = "dns::editArecord [__LINE__] Exception thrown: {$e}";
            return $rslt;
        }

        if (!$r || !isset($r['meta']) || !isset($r['meta'] ['status']) )
        {
            error_log("dns::editArecord [". __LINE__ . "] Null rslt returned");
            $rslt['status'] = false;
            $rslt['error'] = "dns::editArecord [". __LINE__ . "] Null rslt returned";
            return $rslt;

        }

        if ($r['meta'] ['status'])
        {
            $rslt['status'] = true;
        }
        else
        {
            $rslt['status'] = false;
            $rslt['error'] = $r['meta'] ['errormsg'];
        }

        return $rslt;
    }

    public function deleteARecord($domain,$subdomain)
    {
        $rslt = array();
        $params = array();
        $params['type'] = "A";
        $params['domain'] = $domain;
        $params['name'] = $subdomain;

        $url = "https://{$this->_dnsServer}/api/v1/json/delete";


        try
        {
            $r = $this->curl($url,"POST",$params);
        }

        catch (Exception $e)
        {
            error_log("dns::addArecord [". __LINE__ . "] Exception thrown: {$e}");
            $rslt['status'] = false;
            $rslt['error'] = "dns::addArecord [__LINE__] Exception thrown: {$e}";
            return $rslt;
        }

        if (!$r || !isset($r['meta']) || !isset($r['meta'] ['status']) )
        {
            error_log("dns::addArecord [". __LINE__ . "] Null rslt returned");
            $rslt['status'] = false;
            $rslt['error'] = "dns::addArecord [". __LINE__ . "] Null rslt returned";
            return $rslt;

        }

        if ($r['meta'] ['status'])
        {
            $rslt['status'] = true;
        }
        else
        {
            $rslt['status'] = false;
            $rslt['error'] = $r['meta'] ['errormsg'];
        }

        return $rslt;
    }

    public function haveARecord($domain,$subdomain)
    {
        $url = "https://{$this->_dnsServer}/api/v1/json/havearecord/{$domain}/{$subdomain}";

        try
        {
            $r = $this->curl($url,"GET");
        }

        catch (Exception $e)
        {
            error_log("dns::haveArecord [". __LINE__ . "] Exception thrown: {$e}");
            return ["status" => false, "error" => "Exception: {$e->getMessage}"];
        }

        if (!$r)
            return ["status" => false, "error" => "Invalid response from DNS server"];


        if ($r['meta'] ['status'])
            return ["status" => true, "error" => null, "have" => $r['data'] ['have']];

        return ["status" => false, "error" => "Exception"];

    }

    public function getSubdomains($domain)
    {
        $url = "https://{$this->_dnsServer}/api/v1/json/allsubdomains/{$domain}";

        try
        {
            $r = $this->curl($url,"GET");
        }

        catch (Exception $e)
        {
            error_log("dns::getSubdomains [". __LINE__ . "] Exception thrown: {$e}");
            return ["status" => false, "error" => "Exception: {$e->getMessage}"];
        }

        if (!$r)
            return ["status" => false, "error" => "Invalid response from DNS server"];


        if ($r['meta'] ['status'])
            return ["status" => true, "error" => null, "subdomains" => $r['data'] ];

        return ["status" => false, "error" => "Exception"];

    }
}
?>