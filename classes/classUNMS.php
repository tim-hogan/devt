<?php
class Unms
{
    private     $_host;
    private     $_port;
    private     $_token;

    function __construct($host,$port,$token)
    {
        $this->_host = $host;
        $this->_port = $port;
        $this->_token = $token;
    }

    private function curl_get($api)
    {
        $url = "https://{$this->_host}:{$this->_port}/{$api}";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("accept: application/json","x-auth-token: {$this->_token}"));
        $response  = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    private function curl_post($api,$data)
    {
        $url = "https://{$this->_host}:{$this->_port}/{$api}";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("accept: application/json","Content-Type: application/json","x-auth-token: {$this->_token}"));
        $response  = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    public function allSites()
    {
        return $this->curl_get("v2.1/sites");
    }

    public function allDevices()
    {
        return $this->curl_get("v2.1/devices");
    }
}
?>