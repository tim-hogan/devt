<?php

class CHUE
{
	private $_controller;
	private $_key;
	private $_port;

	static public $product_types = [
				"bridge_v2" => ["major_type" => "bridge"],
				"unknown_archetype" => ["major_type" => "unknown"],
				"classic_bulb" => ["major_type" => "light"],
				"sultan_bulb" => ["major_type" => "light"],
				"flood_bulb" => ["major_type" => "light"],
				"spot_bulb" => ["major_type" => "light"],
				"candle_bulb" => ["major_type" => "light"],
				"luster_bulb" => ["major_type" => "light"],
				"pendant_round" => ["major_type" => "light"],
				"pendant_long" => ["major_type" => "light"],
				"ceiling_round" => ["major_type" => "light"],
				"ceiling_square" => ["major_type" => "light"],
				"floor_shade" => ["major_type" => "light"],
				"floor_lantern" => ["major_type" => "light"],
				"table_shade" => ["major_type" => "light"],
				"recessed_ceiling" => ["major_type" => "light"],
				"recessed_floor" => ["major_type" => "light"],
				"single_spot" => ["major_type" => "light"],
				"double_spot" => ["major_type" => "light"],
				"table_wash" => ["major_type" => "light"],
				"wall_lantern" => ["major_type" => "light"],
				"wall_shade" => ["major_type" => "light"],
				"flexible_lamp" => ["major_type" => "light"],
				"ground_spot" => ["major_type" => "light"],
				"wall_spot" => ["major_type" => "light"],
				"plug" => ["major_type" => "switch"],
				"hue_go" => ["major_type" => "light"],
				"hue_lightstrip" => ["major_type" => "light"],
				"hue_iris" => ["major_type" => "light"],
				"hue_bloom" => ["major_type" => "light"],
				"bollard" => ["major_type" => "light"],
				"wall_washer" => ["major_type" => "light"],
				"hue_play" => ["major_type" => "light"],
				"vintage_bulb" => ["major_type" => "light"],
				"vintage_candle_bulb" => ["major_type" => "light"],
				"ellipse_bulb" => ["major_type" => "light"],
				"triangle_bulb" => ["major_type" => "light"],
				"small_globe_bulb" => ["major_type" => "light"],
				"large_globe_bulb" => ["major_type" => "light"],
				"edison_bulb" => ["major_type" => "light"],
				"christmas_tree" => ["major_type" => "light"],
				"string_light" => ["major_type" => "light"],
				"hue_centris" => ["major_type" => "light"],
				"hue_lightstrip_tv" => ["major_type" => "light"],
				"hue_lightstrip_pc" => ["major_type" => "light"],
				"hue_tube" => ["major_type" => "light"],
				"hue_signe" => ["major_type" => "light"],
				"pendant_spot" => ["major_type" => "light"],
				"ceiling_horizontal" => ["major_type" => "light"],
				"ceiling_tube" => ["major_type" => "light"],
				"up_and_down" => ["major_type" => "light"],
				"up_and_down_up" => ["major_type" => "light"],
				"up_and_down_down" => ["major_type" => "light"],
				"hue_floodlight_camera" => ["major_type" => "camera"]
	];

	public function __construct( $controller, $key , $port = 443)
	{
		$this->_port = $port;
		$this->_controller = $controller;
		$this->_key = $key;


	}

	private function p_var_error_log( $object=null , $text='')
	{
		ob_start();
		var_dump( $object );
		$contents = ob_get_contents();
		ob_end_clean();
		error_log( "{$text} {$contents}" );
	}


	private function get($command)
	{

		$url = "https://{$this->_controller}/clip/v2/{$command}";
		$headers = ["hue-application-key: {$this->_key}"];
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_PORT, $this->_port);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);

		if ($result === false)
			error_log("CURL ERROR: " . curl_error($ch));

		return $result;

	}

	private function getV1($command)
	{

		$url = "https://{$this->_controller}/api/{$this->_key}/{$command}";

		error_log("GET v1: {$url}");

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_PORT, $this->_port);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

		$result = curl_exec($ch);

		if ($result === false)
			error_log("CURL ERROR: " . curl_error($ch));

		return $result;

	}

	private function post($command, $params)
	{
		$url = "https://{$this->_controller}/clip/v2/{$command}";
		$headers = ["hue-application-key: {$this->_key}", "Content-Type: application/json"];
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_PORT, $this->_port);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($params));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		return curl_exec($ch);

	}

	private function put($command, $params)
	{
		$url = "https://{$this->_controller}/clip/v2/{$command}";
		$headers = ["hue-application-key: {$this->_key}", "Content-Type: application/json"];
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_PORT, $this->_port);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		return curl_exec($ch);

	}

	static public function XY2RGB($x,$y,$b)
	{
		$z = 1.0 - $x - $y;
		$Y = $b;
		$X = ($Y / $y) * $x;
		$Z = ($Y / $y) * $z;
		$r = $X * 1.656492 - $Y * 0.354851 - $Z * 0.255038;
		$g = -$X * 0.707196 + $Y * 1.655397 + $Z * 0.036152;
		$b =  $X * 0.051713 - $Y * 0.121364 + $Z * 1.011530;

		$r = $r <= 0.0031308 ? 12.92 * $r : (1.0 + 0.055) * pow($r, (1.0 / 2.4)) - 0.055;
		$g = $g <= 0.0031308 ? 12.92 * $g : (1.0 + 0.055) * pow($g, (1.0 / 2.4)) - 0.055;
		$b = $b <= 0.0031308 ? 12.92 * $b : (1.0 + 0.055) * pow($b, (1.0 / 2.4)) - 0.055;

		return ["r" => $r, "g" => $g, "b" => $b];

	}

	static public function RGB2XY($r,$g,$b)
	{
		$red = ($r > 0.04045) ? pow(($r + 0.055) / (1.0 + 0.055), 2.4) : ($r / 12.92);
		$green = ($g > 0.04045) ? pow(($g + 0.055) / (1.0 + 0.055), 2.4) : ($g / 12.92);
		$blue = ($b > 0.04045) ? pow(($b + 0.055) / (1.0 + 0.055), 2.4) : ($b / 12.92);
		$X = $red * 0.4124 + $green * 0.3576 + $blue * 0.1805;
		$Y = $red * 0.2126 + $green * 0.7152 + $blue * 0.0722;
		$Z = $red * 0.0193 + $green * 0.1192 + $blue * 0.9505;
		$x = X / (X + Y + Z);
		$y = Y / (X + Y + Z);
		$brightness = Y;
		return ["x" => $x, "y" => $y, "b" => $brightness];
	}

	public function allLights()
	{
		$ret =  $this->get("resource/light");
		$d = json_decode($ret,true);
		$v1 = $this->getV1("lights");
		$d2 = json_decode($v1, true);
        foreach ($d2 as $key => $light)
        {
			foreach($d["data"] as &$l)
            {
				if ($l["id_v1"] == "/lights/{$key}" )
                {
                    $l["reachable"] = $light["state"]["reachable"];
                }
            }
        }
        return json_encode($d);
	}

	public function allRooms()
	{
		return $this->get("resource/room");
	}

	public function allDevices()
	{
		return $this->get("resource/device");
	}

	public function allScenes()
	{
		return $this->get("resource/scene");
	}

	public function getDevice($id)
	{
		return $this->get("resource/device/{$id}");
	}

	public function lightOnOff($id,$on)
	{
		$params = [];
		if ($on)
			$params = ["on" => ["on" => true]];
		else
			$params = ["on" => ["on" => false]];

		return $this->put("resource/light/{$id}", $params);
	}

	public function lightBrightness($id,$brightness)
	{
		$brightness = intval($brightness);
		$brightness = max(min($brightness, 100), 0);
		$params = [];
		$params = ["dimming" => ["brightness" => $brightness]];

		return $this->put("resource/light/{$id}", $params);
	}

	public function lightColourBright($id,$x,$y,$b)
	{
		$b = intval($b);
		$b = max(min($b, 100), 0);
		$params = [];
		$params = ["color" => ["xy" => ["x" => $x, "y" => $y] ], "dimming" => ["brightness" => $b]];
		return $this->put("resource/light/{$id}", $params);
	}

	public function lightEffect($id,$effect)
	{
		$params = ["effects" => ["effect" => $effect]];
		return $this->put("resource/light/{$id}", $params);
	}
}
?>