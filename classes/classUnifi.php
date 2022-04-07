<?php
class unifi
{
    private $controller;
    private $site;
    private $port;
    private $cookies;
    private $_debug;
    private $_debugErrorLog;

    public function __construct($controller,$site,$port=8443)
    {
         $this->controller = $controller;
         $this->site = $site;
         $this->port = $port;
         $this->cookies = null;
         $this->signed_in = false;
         $this->_debug = false;
         $this->_debugErrorLog = true;
    }

    private function var_error_log( $object=null,$text='')
    {
        ob_start();
        var_dump( $object );
        $contents = ob_get_contents();
        ob_end_clean();
        error_log( "{$text} {$contents}" );
    }

    private function debugOut($t)
    {
        if ($this->_debug)
        {
            if ($this->_debugErrorLog)
                error_log($t);
            else
                echo $t . "\n";
        }
    }

    private function debugVar($v,$t)
    {
        if ($this->_debug)
        {
            if ($this->_debugErrorLog)
                $this->var_error_log($v,$t);
            else
            {
                echo $t . " ";
                var_dump($v);
                echo "\n";
            }
        }
    }

    private function curlGET($api)
    {
        $url = "https://{$this->controller}/{$api}";

        $this->debugOut("classUnifi::curlGET to $url");

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_PORT, $this->port);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);

        if ($this->cookies)
        {
            $cs = "";
            foreach($this->cookies as $k => $v)
            {
                $cs .= "{$k}={$v};";
            }

            curl_setopt($ch, CURLOPT_COOKIE, $cs);
        }


        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $response  = curl_exec($ch);

        $this->debugOut("   result {$response}");

        if ($response)
        {
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($response, 0, $header_size);
            $body = substr($response, $header_size);

            $hrecs = explode("\n",$header);
            foreach ($hrecs as $rec)
            {

                if (substr($rec,0,11) == "Set-Cookie:")
                {
                    $c = explode(":",$rec);
                    $cookie = trim(strtok($c[1],";"));
                    $a = explode("=",$cookie);

                    $this->cookies[trim($a[0])] = trim($a[1]);
                }
            }

            //$this->var_error_log($header,"header");
            //$this->var_error_log($body,"body");

            curl_close($ch);

            return ["header" => $header,"body" => $body];

        }
        else
        {
            error_log("Curl error: " . curl_error($ch));
            curl_close($ch);
            return false;
        }

    }

    private function curlPOST($api,$postdata)
    {
        $url = "https://{$this->controller}/{$api}";

        $this->debugOut("classUnifi::curlPOST to $url");

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_PORT, $this->port);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS,$postdata);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);

        if ($this->cookies)
        {
            $cs = "";
            foreach($this->cookies as $k => $v)
            {
                $cs .= "{$k}={$v};";
            }

            curl_setopt($ch, CURLOPT_COOKIE, $cs);
        }


        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

        $response  = curl_exec($ch);
        $this->debugOut("   result {$response}");

        if ($response)
        {
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($response, 0, $header_size);
            $body = substr($response, $header_size);

            $hrecs = explode("\n",$header);
            foreach ($hrecs as $rec)
            {

                if (substr($rec,0,11) == "Set-Cookie:")
                {
                    $c = explode(":",$rec);
                    $cookie = trim(strtok($c[1],";"));
                    $a = explode("=",$cookie);

                    $this->cookies[trim($a[0])] = trim($a[1]);
                }
            }

            //$this->var_error_log($header,"header");
            //$this->var_error_log($body,"body");

            curl_close($ch);
            return ["header" => $header,"body" => $body];
        }
        else
        {
            error_log("Curl error: " . curl_error($ch));
            curl_close($ch);
            return false;
        }
    }

    public function isSignedIn()
    {
        return $this->signed_in;
    }

    public function login($usercode,$password)
    {

        $postdata = json_encode(array('username' => $usercode, 'password' => $password));
        $result = $this->curlPOST('api/login',$postdata);
        if ($result === false)
            return false;

        $result = json_decode($result['body'],true);

        $this->debugVar($result,"result from login");

        if (isset($result['meta']) && isset($result['meta'] ['rc']) && $result['meta'] ['rc'] == "ok")
        {
            $this->signed_in = true;
            return true;
        }
        $this->signed_in = false;
        return false;
    }

    public function devices()
    {

        $this->debugOut("classUnifi::devices called");

        $result = $this->curlGET('api/s/'.$this->site.'/stat/device');
        if ($result === false)
        {
            error_log("Curl failed");
            return false;
        }

        if (!isset($result['body']))
            return false;

        $returned = json_decode($result['body'],true);

        $this->debugVar($returned,"result from devices");


        if (isset($returned['meta']) && isset($returned['meta'] ['rc']) && $returned['meta'] ['rc'] == "ok" && isset($returned['data']) )
        {
            return $returned['data'];
        }

        $this->signed_in = false;
        return false;
    }

    public function active()
    {
        $this->debugOut("classUnifi::active called");

        $result = $this->curlGET('v2/api/site/'.$this->site.'/clients/active');
        if ($result === false)
        {
            error_log("Curl failed");
            return false;
        }

        if (!isset($result['body']))
            return false;

        $returned = json_decode($result['body'],true);

        $this->debugVar($returned,"result from active");

        return $returned;
    }

    public function statistics()
    {
        $this->debugOut("classUnifi::statistics called");

        $result = $this->curlGET('api/s/'.$this->site.'/stat/sta');
        if ($result === false)
        {
            error_log("Curl failed");
            return false;
        }

        if (!isset($result['body']))
            return false;

        $returned = json_decode($result['body'],true);

        $this->debugVar($returned,"result from statistics");


        if (isset($returned['meta']) && isset($returned['meta'] ['rc']) && $returned['meta'] ['rc'] == "ok" && isset($returned['data']) )
        {
            return $returned['data'];
        }
        $this->signed_in = false;
        return false;

    }

    public function past()
    {

        $this->debugOut("classUnifi::past called");

        $api = "api/s/{$this->site}/stat/alluser?is_offline=true&within=744";
        $result = $this->curlGET($api);
        if ($result === false)
        {
            error_log("Curl failed");
            return false;
        }

        if (!isset($result['body']))
            return false;

        $returned = json_decode($result['body'],true);

        $this->debugVar($returned,"result from past");

        if (isset($returned['meta']) && isset($returned['meta'] ['rc']) && $returned['meta'] ['rc'] == "ok" && isset($returned['data']) )
        {
            return $returned['data'];
        }
        $this->signed_in = false;
        return false;

    }

    public function session($mac,$limit)
    {
        $postdata = json_encode(array('mac' => $mac, '_limit' => $limit, '_sort' => '-assoc_time'));

        $result = $this->curlPOST("api/s/{$this->site}/stat/session",$postdata);
        if ($result === false)
            return false;

        $returned = json_decode($result['body'],true);

        $this->debugVar($returned,"result from session");

        if (isset($returned['meta']) && isset($returned['meta'] ['rc']) && $returned['meta'] ['rc'] == "ok")
        {
            return $returned['data'];
        }

        $this->signed_in = false;
        return false;

    }

    public function setDebug($v)
    {
        $this->_debug = $v;
    }

    public function setDebugMode($mode)
    {
        if ($mode != "errorlog" && $mode != "cli")
            throw(new Exception("classUnifi::setDebugMode error: Mode must be errorlog or cli"));
        if ($mode == "errorlog")
            $this->_debugErrorLog = true;
        else
            $this->_debugErrorLog = false;
    }

    public function logCookies()
    {
        error_log("classUnifi Cookies follow");
        foreach ($this->cookies as $key => $value)
            error_log(" [{$key}] {$value}");
    }

}
?>