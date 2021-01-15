<?php
class SesssionSecurity
{
    static public function encryptarray($params,$generateiv=true)
    {
        // Remove the base64 encoding from our key
        if (isset($_SESSION['session_key']) && is_array($params))
        {          
            $encryption_key = base64_decode($_SESSION['session_key']);
            $data = "FFFF" . json_encode($params);
            $iv = null;
            if ($generateiv)
                $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
            else
                $iv = base64_decode($_SESSION['iv']);
            $encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);
            $result = base64_encode($encrypted . '::' . $iv);
            return urlencode($result);
        }
        return null;
    }
    
    static public function decryptstring($str)
    {
        if (isset($_SESSION['session_key']) && $str && strlen($str) > 0)
        {
            $encryption_key = base64_decode($_SESSION['session_key']);
            list($encrypted_data, $iv) = explode('::', base64_decode($str), 2);
            $decdata = openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
            if (substr($decdata,0,4) == 'FFFF')
            {
                return json_decode(substr($decdata,4),true);
            }
        }
        return null;
    }
}
?>