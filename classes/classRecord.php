<?php
class CRecord
{
    private $_fields = null;
    public function __construct($fields)
    {
        //Fields look like
        //["name" => "fred" , "type" => "unit16_t", "length" => 50], ...

        $this->_fields = $fields;
    }

    public function uint8_t(int $v)
    {
        if ($v > 255)
            return false;
        return hex2bin(str_pad(dechex($v), 2, "0", STR_PAD_LEFT));
    }

    public function uint16_t(int $v)
    {
        if ($v > 65536)
            return false;
        $t = hex2bin(str_pad(dechex($v), 4, "0", STR_PAD_LEFT));
        return substr($t, 1, 1) . substr($t, 0, 1);
    }

    public function uint32_t(int $v)
    {
        if ($v > 4294967296)
            return false;
        $t = hex2bin(str_pad(dechex($v), 8, "0", STR_PAD_LEFT));
        return substr($t, 3, 1) . substr($t, 2, 1) . substr($t, 1, 1) . substr($t, 0, 1);
    }

    public function uint64_t(int $v)
    {
        $t = hex2bin(str_pad(dechex($v), 16, "0", STR_PAD_LEFT));
        return substr($t, 7, 1) . substr($t, 6, 1) . substr($t, 5, 1) . substr($t, 4, 1) . substr($t, 3, 1) . substr($t, 2, 1) . substr($t, 1, 1) . substr($t, 0, 1);
    }

    public function char(string $v, $l)
    {
        $sz = strlen($v);
        if ($sz > $l - 1)
            return false;
        for ($i = 0; $i < $l - $sz; $i++)
            $v .= hex2bin("00");
        return $v;
    }

    public function construct($data)
    {
        $ret = "";
        foreach($this->_fields as $field)
        {
            switch($field["type"])
            {
                case "uint8_t":
                    $ret .= $this->uint8_t($data[$field["name"]]);
                    break;
                case "uint16_t":
                    $ret .= $this->uint16_t($data[$field["name"]]);
                    break;
                case "uint32_t":
                    $ret .= $this->uint32_t($data[$field["name"]]);
                    break;
                case "uint64_t":
                    $ret .= $this->uint64_t($data[$field["name"]]);
                    break;
                case "char":
                    $ret .= $this->char($data[$field["name"]],$field["length"]);
                    break;
            }
        }
        return $ret;
    }
}
?>