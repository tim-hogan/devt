<?php
class CRecord
{
	private $_fields = null;
	private $_data = null;

	public function __construct($fields)
	{
		//Fields look like
		//["name" => "fred" , "type" => "unit16_t", "length" => 50], ...

		$this->_fields = $fields;
		$this->_data = array();
	}

	private function uint8_t(int $v)
	{
		if ($v > 255)
			return false;
		return hex2bin(str_pad(dechex($v), 2, "0", STR_PAD_LEFT));
	}

    private function uint16_t(int $v)
	{
		if ($v > 65536)
			return false;
		$t = hex2bin(str_pad(dechex($v), 4, "0", STR_PAD_LEFT));
		return substr($t, 1, 1) . substr($t, 0, 1);
	}

    private function uint32_t(int $v)
	{
		if ($v > 4294967296)
			return false;
		$t = hex2bin(str_pad(dechex($v), 8, "0", STR_PAD_LEFT));
		return substr($t, 3, 1) . substr($t, 2, 1) . substr($t, 1, 1) . substr($t, 0, 1);
	}

    private function uint64_t(int $v)
	{
		$t = hex2bin(str_pad(dechex($v), 16, "0", STR_PAD_LEFT));
		return substr($t, 7, 1) . substr($t, 6, 1) . substr($t, 5, 1) . substr($t, 4, 1) . substr($t, 3, 1) . substr($t, 2, 1) . substr($t, 1, 1) . substr($t, 0, 1);
	}

    private function char(string $v, $l)
	{
		$sz = strlen($v);
		if ($sz > $l - 1)
			return false;
		for ($i = 0; $i < $l - $sz; $i++)
			$v .= hex2bin("00");
		return $v;
	}

    private function strto_uint8_t(string $v)
	{
		return hexdec(bin2hex($v));
	}

    private function strto_uint16_t(string $v)
	{
		return hexdec(bin2hex(substr($v, 0, 1))) + (hexdec(bin2hex(substr($v, 1, 1))) * 256);
	}

    private function strto_uint32_t(string $v)
    {
        return hexdec(bin2hex(substr($v, 0, 1))) + (hexdec(bin2hex(substr($v, 1, 1))) * 256) + (hexdec(bin2hex(substr($v, 2, 1))) * 65536 ) + (hexdec(bin2hex(substr($v, 3, 1))) * 16777216);
    }

    private function strto_uint64_t(string $v)
    {
        return hexdec(bin2hex(substr($v, 0, 1))) + (hexdec(bin2hex(substr($v, 1, 1))) * 256) + (hexdec(bin2hex(substr($v, 2, 1))) * 65536) + (hexdec(bin2hex(substr($v, 3, 1))) * 16777216) +
            (hexdec(bin2hex(substr($v, 4, 1))) * (16777216 * 256)) + (hexdec(bin2hex(substr($v, 5, 1))) * (16777216 * 65536)) + (hexdec(bin2hex(substr($v, 6 ,1))) * (16777216 * 16777216)) + 
			(hexdec(bin2hex(substr($v, 7, 1))) * (16777216 * 16777216 * 256));
    }

    private function strto_char(string $v)
    {
        $ret = "";
		$l = strlen($v);
		for ($i = 0; $i < $l;$i++)
        {
            if (hexdec(bin2hex(substr($v, $i, 1))) == 0)
                break;
            $ret .= substr($v, $i, 1);
        }
        return $ret;
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

	public function parse($str)
	{

		foreach($this->_fields as $field)
		{
			switch ($field["type"])
			{
				case "uint8_t":

					$this->_data[$field["name"]] = $this->strto_uint8_t(substr($str, 0, 1));
                    $str = substr($str, 1);
					break;
				case "uint16_t":
					$this->_data[$field["name"]] = $this->strto_uint16_t(substr($str, 0, 2));
					$str = substr($str, 2);
					break;
                case "uint32_t":
                    $this->_data[$field["name"]] = $this->strto_uint32_t(substr($str, 0, 4));
                    $str = substr($str, 4);
                    break;
                case "uint64_t":
                    $this->_data[$field["name"]] = $this->strto_uint64_t(substr($str, 0, 8));
                    $str = substr($str, 4);
                    break;
                case "char":
                    $this->_data[$field["name"]] = $this->strto_char(substr($str, 0, $field["length"]));
                    $str = substr($str, $field["length"]);
                    break;
            }
		}
	}

	public function sizeof()
    {
        $sum = 0;
		foreach($this->_fields as $field)
        {
            $sum += $field["length"];
        }
        return $sum;
    }

	public function __get($name)
	{
		if (isset($this->_data[$name]))
		{
			return $this->_data[$name];
		}
		return null;
	}
}
?>