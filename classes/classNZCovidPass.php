<?php
require_once dirname(__FILE__) . "/classBase2n.php";
require_once dirname(__FILE__) . "/classCBOR.php";
require_once dirname(__FILE__) . "/classDigCert.php";



class nzVaccinePass extends digCert
{

    const
    ERROR_NO_DID = -1,
    ERROR_NO_ISS = -2,
    ERROR_INVALID_ISS = -3,
    ERROR_INVALID_SIGNATURE = -4,
    ERROR_NO_NOT_BEFORE_DATE = -5,
    ERROR_NO_EXPIRY_DATE = -6,
    ERROR_VALID_IN_FUTURE = -7,
    ERROR_EXPIRED = -8,
    ERROR_INVALID_ISSUER = -9;

    private $qrVersion = null;
    private $last_error = '';

    function __construct($data,$QRVersion=null)
    {
        parent::__construct($data);
        $this->qrVersion = $QRVersion;

    }

    public function getFirstName()
    {
        if (isset($this->subject["givenName"]))
        {
            $n = explode(" ",trim($this->subject["givenName"]));
            if (count($n) > 0)
                return trim($n[0]);
        }
        return '';
    }

    public function getFirstNames()
    {
        if (isset($this->subject["givenName"]))
            return trim($this->subject["givenName"]);
        return '';
    }

    public function getLastName()
    {
        if (isset($this->subject["familyName"]))
            return trim($this->subject["familyName"]);
        return '';
    }

    public function getFormatedName()
    {
        if (isset($this->subject["givenName"]) && isset($this->subject["familyName"]))
            return trim(trim($this->subject["givenName"]) . " " . trim($this->subject["familyName"]));
        return '';
    }

    public function getFormatedDOB()
    {
        return (new DateTime($this->subject["dob"]))->format("j/n/Y");
    }

    public function getDOB()
    {
        if (isset($this->subject) && isset($this->subject["dob"]) )
        {
            return (new DateTime($this->subject["dob"]))->format("Y-m-d 00:00:00");
        }
        return null;
    }

    public function getAge()
    {
        $dob = new DateTime($this->subject["dob"],new DateTimeZone("Pacific/Auckland"));
        return ($dob->diff(new DateTime("now")))->format("%y");
    }

    public function getExpires()
    {
        return ((new DateTime())->setTimestamp(intval($this->did["exp"])))->format("j/n/Y");
    }

    public function getExpiresSerial()
    {
        if (isset($this->did) && isset($this->did["exp"]) )
            return intval($this->did["exp"]);
        return 0;
    }

    public function getExpiresYMD()
    {
        if (isset($this->did) && isset($this->did["exp"]) )
            return ((new DateTime())->setTimestamp(intval($this->did["exp"])))->format("Y-m-d 00:00:00");
        return null;
    }

    public function verifySignature()
    {
        $this->last_error = "";
        if (! isset($this->data['vdata']) || ! isset($this->data['opensslsig']) )
        {
            $this->last_error = "Missing signature and/or signed data";
            return false;
        }

        //Load the public key
        if (! $this->public_key )
            $this->loadPublicKey();
        if (! $this->public_key )
        {
            $this->last_error = "Unable to get public key";
            return false;
        }

        $r = openssl_verify($this->getSignedData(),$this->getSignature(),$this->public_key,OPENSSL_ALGO_SHA256);
        if ($r === 1)
            return true;
        $this->last_error = "Failed to veify signature";
        return false;
    }

    public function isValid($valid_issuers=null)
    {
        $this->last_error = "";
        //Check for the did
        if (!isset($this->did))
        {
            $this->last_error = "No digital id found";
            return self::ERROR_NO_DID;
        }

        $did = $this->did;
        if (!isset($did["iss"]))
        {
            $this->last_error = "No decentralized identifier [iss] found";
            return self::ERROR_NO_ISS;
        }

        if (substr($did["iss"],0,7) != "did:web")
        {
            $this->last_error = "Invalid decentralized identifier";
            return self::ERROR_INVALID_ISS;
        }

        if ($valid_issuers)
        {
            $cert_issuer = trim(substr($did["iss"],8));
            if ( ! in_array($cert_issuer,$valid_issuers))
            {
                $this->last_error = "Invalid certificate issuer";
                return self::ERROR_INVALID_ISSUER;
            }
        }


        if (! $this->verifySignature())
        {
            return self::ERROR_INVALID_SIGNATURE;
        }

        //Check valid before
        if (!isset ($did["nbf"]))
        {
            $this->last_error = "Missing from date";
            return self::ERROR_NO_NOT_BEFORE_DATE;
        }

        if (!isset($did["exp"]))
        {
            $this->last_error = "Missing expiry date";
            return self::ERROR_NO_EXPIRY_DATE;
        }

        if (  (new DateTime('now'))->getTimestamp() < intval($did["nbf"])  )
        {
            $validfrom = ((new DateTime())->setTimestamp(intval($did["nbf"])))->format("j/n/Y");
            $this->last_error = "Certificate not valid until {$validfrom}";
            return self::ERROR_VALID_IN_FUTURE;
        }
        //Check expires
        if (  (new DateTime('now'))->getTimestamp() > intval($did["exp"])  )
        {
            $expires = ((new DateTime())->setTimestamp(intval($did["exp"])))->format("j/n/Y");
            $this->last_error = "Certificate expired on {$expires}";
            return self::ERROR_EXPIRED;
        }
        return true;
    }

    public function last_error()
    {
        return $this->last_error;
    }


    public static function createPass($givenName,$familyName,$dob,$issueSerial,$expireSerial)
    {
        $a = array();

        $b = array();
        $b['4'] = new CBORByteString("rs5vwy22");
        $b['1'] = -7;


        $a[0] = new CBORByteString(CBOREncoder::encode($b));
        $a[1] = [];

        $c = array();
        $c[1] = "did:web:covidpass.devt.nz";
        $c[5] = $issueSerial;
        $c[4] = $expireSerial;


        $d = array();

        $e = array();
        $e[0] = "https://www.w3.org/2018/credentials/v1";
        $e[1] = "https://nzcp.covid19.health.nz/contexts/v1";

        $d["@context"] = $e;
        $d["version"] = "1.0.0";
        $d["type"] = ["VerifiableCredential","PublicCovidPass"];
        $d["credentialSubject"] = ["givenName" => $givenName,"familyName" => $familyName, "dob" => $dob];
        $c["vc"] = $d;

        $c[7] = new CBORByteString(openssl_random_pseudo_bytes(16));

        $a[2] = new CBORByteString(CBOREncoder::encode($c));


        //Now we need to sign it
        $sigdata = CBOREncoder::encode(["Signature1",new CBORByteString(CBOREncoder::encode($b)),new CBORByteString(''),new CBORByteString(CBOREncoder::encode($c))]);
        $signature = null;
        $pem = file_get_contents("/etc/covidpass/ca/private_key.pem");
        $pkeyid = openssl_pkey_get_private($pem);
        openssl_sign($sigdata,$signature,$pkeyid,OPENSSL_ALGO_SHA256);

        $s1 = null;
        $s2 = null;

        $unpacked = unpack("C*", substr($signature, 3, 1));
        $l1 = array_shift($unpacked);
        if ($l1 == 33)
            $s1 = substr($signature, 5, 32);
        else
            $s1 = substr($signature, 4, 32);

        $unpacked = unpack("C*", substr($signature, 5 + $l1, 1));
        $l2 = array_shift($unpacked);
        if ($l2 == 33)
            $s2 = substr($signature, 7+ $l1, 32);
        else
            $s2 = substr($signature, 6+ $l1, 32);


        $a[3] = new CBORByteString($s1.$s2);

        $data = hex2bin("D2") . CBOREncoder::encode($a);


        $base32 = new Base2n(5, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567', FALSE, TRUE, TRUE);
        $encoded = $base32->encode($data);
        //remove padding
        $encoded = str_replace("=","",$encoded);

        return "NZCP:/1/" . $encoded;

    }

    public static function decodeQR($qr)
    {
        $decode = array();
        $j = array();
        $a = explode("/",$qr);
        if (count($a) != 3)
            throw new Exception("Input string invalid missing header and version");
        if ($a[0] != "NZCP:")
            throw new Exception("Input string invalid invalid header");
        $qrVersion = $a[1];
        $base32 = new Base2n(5, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567', FALSE, TRUE, TRUE);
        $raw = $base32->decode($a[2]);


        if (!$raw)
            throw new Exception("Base32 decode failure");

        $a = CBOREncoder::decode($raw);

        if ($a && gettype($a) == 'array' && isset($a['tag']))
        {
            $certdata = $a['tag'] ['data'];
            $str = $certdata[0]->get_byte_string();
            $header = CBOREncoder::decode($str);
            $alg = null;
            switch ($header[1])
            {
                case -7:
                    $alg = "ES256";
                    break;
            }

            $j["key"] = ["kid" => $header[4]->get_byte_string(), "alg" => $alg];

            $str = $certdata[2]->get_byte_string();
            $payload = CBOREncoder::decode($str);

            if (!isset($payload[7]) || !isset($payload[1]) || !isset($payload[5]) || !isset($payload[4]) || !isset($payload["vc"]))
                 throw new Exception("Invalid decode of CBOR");

            $uuid = bin2hex($payload[7]->get_byte_string());
            $struuid = "urn:uuid:" . substr($uuid,0,8) . "-" . substr($uuid,8,4) . "-" . substr($uuid,12,4) . "-" . substr($uuid,16,4) . "-" . substr($uuid,20,4) . "-" . substr($uuid,24);
            $avc = $payload["vc"];

            $vc = ["@context" => $avc["@context"], "version" => $avc["version"], "type" => $avc["type"] , "credentialSubject" => $avc["credentialSubject"]];

            $j["did"] = ["iss" => $payload[1], "nbf" => $payload[5], "exp" => $payload[4], "jti" => $struuid , "vc" => $vc];

            $j["signature"] = bin2hex($certdata[3]->get_byte_string());

            $r = $certdata[3]->get_byte_string();
            $s = substr($r,-32);
            $r = substr($r,0,32);

            //Create cerificate in dem format
            $dem = digCert::createDEMSignature($r,$s,["swap" => false,"ber" => true,"endianswap" => false]);
            $j["opensslsig"] = base64_encode($dem);

            //Create data verification
            $j["vdata"] = CBOREncoder::encode(["Signature1",$certdata[0],new CBORByteString(''),$certdata[2]]);
            $decode["qrVersion"] = $qrVersion;
            $decode["data"] = $j;
        }
        else
            throw new Exception("Invalid decode of CBOR");

        return new nzVaccinePass($decode["data"],$decode["qrVersion"]);
    }

    private static function var_error_log( $object=null , $text='')
    {
        ob_start();
        var_dump( $object );
        $contents = ob_get_contents();
        ob_end_clean();
        error_log( "{$text} {$contents}" );
    }


}

?>