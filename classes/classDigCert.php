<?php
class digCert
{
    //ASN 1 defines
    private const ASN1_SEQUENCE = '30';
    private const ASN1_INTEGER = '02';

    protected $data = array();
    protected $key = array();
    protected $did = array();
    protected $vc = array();
    protected $public_key = null;
    protected $subject = array();

    function __construct($data)
    {
        $this->data = $data;
        if (isset($data["key"]))
            $this->key = $data["key"];
        if (isset($data["did"]))
            $this->did = $data["did"];
        if (isset($data["did"]  ["vc"]))
            $this->vc = $data["did"] ["vc"];
        if (isset($data["did"]  ["vc"] ["credentialSubject"] ))
            $this->subject = $data["did"]  ["vc"] ["credentialSubject"];
    }

    public function expired()
    {
        if ( ((new DateTime('now'))->getTimestamp()) > intval($this->did["exp"]))
            return true;
        return false;
    }

    public function getFormatedExpiryDate($format=null)
    {
        if (!$format)
            $format = "j/n/Y";
        return ((new DateTime())->setTimestamp(intval($this->did["exp"])))->format($format);
    }

    public function getKeyId()
    {
        if(isset($this->key ["kid"]) )
            return $this->key ["kid"];
        return null;
    }

    public function getUUID()
    {
        //"jti": "urn:uuid:cc599d04-0d51-4f7e-8ef5-d7b5f8461c5f",
        if (isset($this->did) && isset($this->did["jti"]) )
        {
            $t = trim($this->did["jti"]);
            if (substr($t,0,9) == "urn:uuid:")
            {
                $t = substr($t,-(strlen($t)-9));
                return str_replace("-","",$t);
            }
        }
        return null;
    }

    public function getUUIDString()
    {
        //"jti": "urn:uuid:cc599d04-0d51-4f7e-8ef5-d7b5f8461c5f",
        if (isset($this->did) && isset($this->did["jti"]) )
        {
            $t = trim($this->did["jti"]);
            if (substr($t,0,9) == "urn:uuid:")
            {
                $t = substr($t,-(strlen($t)-9));
                return $t;
            }
        }
        return null;
    }

    public function loadPublicKey()
    {
        $keyid = $this->getKeyId();
        if ($keyid)
        {
            if (file_exists("Public_Key_{$keyid}.pem") )
                $this->public_key = file_get_contents("Public_Key_{$keyid}.pem");
            else
            {
                try {
                    $this->public_key = $this->getPublicKey($keyid);
                }
                catch (Exception $e)
                {
                    error_log("Exception when trying to load public key {$e->getMessage()}");
                }

                if ($this->public_key)
                    file_put_contents("Public_Key_{$keyid}.pem",$this->public_key);
            }
        }
    }

    public function getPublicKey($keyid)
    {
        //Build the url
        $url = "https://" . str_replace("did:web:","",$this->did ["iss"]) . "/.well-known/did.json";
        
        //Create a curl request
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        $result = curl_exec($ch);
        if ($result)
        {
            $result = json_decode($result,true);
            //Check that the key matches our kid
            if (isset ($result["assertionMethod"]) && $result["assertionMethod"][0] == $this->did ["iss"] . "#" . $keyid)
            {
                //Check the verification method
                if (isset($result["verificationMethod"]))
                {
                    $vMethod = $result["verificationMethod"] [0];
                    if (isset($vMethod["type"]) && $vMethod["type"] == "JsonWebKey2020" && isset($vMethod["publicKeyJwk"]))
                    {
                        //Appears as if we have a key
                        $x = self::base64url_decode($vMethod["publicKeyJwk"] ["x"]);
                        $y = self::base64url_decode($vMethod["publicKeyJwk"] ["y"]);

                        $newraw = hex2bin("3059301306072a8648ce3d020106082a8648ce3d03010703420004") . $x . $y;
                        $newdata = base64_encode($newraw);

                        $public_key_pem = "-----BEGIN PUBLIC KEY-----\n";
                        for($idx =0;$idx < strlen($newdata);$idx+=64)
                        {
                           $public_key_pem .= substr($newdata,$idx,64) . "\n";
                        }
                        $public_key_pem .= "-----END PUBLIC KEY-----\n";
                        return $public_key_pem;
                    }
                    else
                    {
                        echo "vMethod Error\n";
                        var_dump($vMethod);
                        throw new Exception("verificationMethod error");
                    }
                }
                else
                    throw new Exception("verificationMethod missing");
            }
            else
                throw new Exception("Assert method mismatch to key");

        }
        else
            throw new Exception("Invalid result back from {$url}");
    }

    public function didToJson()
    {
        if (isset($this->did))
        {
            return json_encode($this->did,JSON_UNESCAPED_SLASHES);
        }
        return null;
    }

    public function getSignedData()
    {
        if (isset($this->data ["vdata"]) )
            return $this->data ["vdata"];
        else
            return null;
    }

    public function getSignature()
    {
        if (isset($this->data["opensslsig"]) )
            return base64_decode($this->data["opensslsig"]);
        else
            return null;
    }

    public static function createDEMSignature($r,$s,$options=null)
    {
        //Options
        $padrvalue = '';
        $padsvalue = '';
        $padrl = 0;
        $padsl = 0;

        if ($options === null)
        {
            $options = ["swap" => false,"ber" => false,"endianswap" => false];
        }
        if (isset($options["swap"]) && $options["swap"])
        {
            $t = $s;
            $s = $r;
            $r = $t;
        }

        if (isset($options["ber"]) && $options["ber"])
        {
            $padvalue = "00";
            $padlength = 1;
        }

        if (isset($options["endianswap"]) && $options["endianswap"])
        {
            $r1 = $r;
            $s1 = $s;
            $l = strlen($r);
            for($idx = 0; $idx < $l;$idx++)
            {
                $r[$idx] = $r1[($l-1)-$idx];
                $s[$idx] = $s1[($l-1)-$idx];
            }
        }



        $lr = strlen($r);
        $ls = strlen($s);

        //If the first byte of r is > 127 then we need to pad a zero
        if (hexdec(bin2hex(substr($r,0,1))) > 127)
        {
            $padrvalue = '00';
            $padrl = 1;
        }

        if (hexdec(bin2hex(substr($s,0,1))) > 127)
        {
            $padsvalue = '00';
            $padsl = 1;
        }

        $total_length = $lr + $ls + 2 + 2 + $padrl + $padsl;

        $a = self::ASN1_SEQUENCE . dechex($total_length) . self::ASN1_INTEGER . dechex($lr+$padrl) .$padrvalue . bin2hex($r) . self::ASN1_INTEGER . dechex($ls+$padsl) . $padsvalue . bin2hex($s);
        return hex2bin($a);
    }

    public static function base64url_decode($s)
    {
        return base64_decode(strtr($s,'-_', '+/'));
    }

}
?>