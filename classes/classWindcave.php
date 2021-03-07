<?php
namespace nvaluate\windcave;

class WindcavePayment
{
    private $_host;
    private $_callback_approved;
    private $_callback_declined;
    private $_callback_cancelled;
    private $_notificationUrl;


    function __construct($params)
    {
        if ($params)
        {
            if (isset($params['host']))
                $this->_host = $params['host'];
        }
    }

    private function postCURL($api,$params)
    {
        $url = "https://{$this->_host}/{$api}";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json","Authorization: Basic ABC123"]);
        $str = json_encode($params);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$str);
        $result = curl_exec($ch);
        if ($result)
            $result = json_decode($result,true);
        return $result;
    }

    public function createSeesion($type,$amount,$currency,$reference)
    {
        $params = array();
        $params["type"] = $type;
        $params["amount"] = strval(trim($amount));
        $params["currency"] = $currency;
        $params["merchantReference"] = $reference;
        $params['callbackUrls'] = array();
        $params['callbackUrls'] ['approved'] = $this->_callback_approved;
        $params['callbackUrls'] ['declined'] = $this->_callback_declined;
        $params['callbackUrls'] ['cancelled'] = $this->_callback_cancelled;
        $params['notificationUrl'] = $this->_notificationUrl;

        return $this->postCURL("api/v1/sessions",$params);
    }
}
?>