<?php

class AccountDate
{
	private $_yearEndMonth;
	private $_startYear =
	[
		[-1,0,0,0,0,0,0,0,0,0,0,0],
		[-1,-1,0,0,0,0,0,0,0,0,0,0],
		[-1,-1,-1,0,0,0,0,0,0,0,0,0],
		[-1,-1,-1,-1,0,0,0,0,0,0,0,0],
		[-1,-1,-1,-1,-1,0,0,0,0,0,0,0],
		[-1,-1,-1,-1,-1,-1,0,0,0,0,0,0],
		[-1,-1,-1,-1,-1,-1,-1,0,0,0,0,0],
		[-1,-1,-1,-1,-1,-1,-1,-1,0,0,0,0],
		[-1,-1,-1,-1,-1,-1,-1,-1,-1,0,0,0],
		[-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,0,0],
		[-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,0],
		[0,0,0,0,0,0,0,0,0,0,0,0]
	];

	private $_endYear =
	[
		[0,1,1,1,1,1,1,1,1,1,1,1],
		[0,0,1,1,1,1,1,1,1,1,1,1],
		[0,0,0,1,1,1,1,1,1,1,1,1],
		[0,0,0,0,1,1,1,1,1,1,1,1],
		[0,0,0,0,0,1,1,1,1,1,1,1],
		[0,0,0,0,0,0,1,1,1,1,1,1],
		[0,0,0,0,0,0,0,1,1,1,1,1],
		[0,0,0,0,0,0,0,0,1,1,1,1],
		[0,0,0,0,0,0,0,0,0,1,1,1],
		[0,0,0,0,0,0,0,0,0,0,1,1],
		[0,0,0,0,0,0,0,0,0,0,0,1],
		[0,1,1,1,1,1,1,1,1,1,1,1]
	];

	private static $_months = ["JAN","FEB","MAR","APR","MAY","JUN","JUL","AUG","SEP","OCT","NOV","DEC"];

	function __construct($yearendmonth)
	{
		$this->_yearEndMonth = $yearendmonth;
	}

	public function finacialYear($date)
	{
		if (gettype($date) == "string")
			$date = new Datetime($date);
		$year = intval($date->format('Y'));
		$month = intval($date->format('m'));

		$y1 = $year + ($this->_startYear[$this->_yearEndMonth -1] [$month-1]);
		$y2 = $year + ($this->_endYear[$this->_yearEndMonth -1] [$month-1]);
		$sm = $this->_yearEndMonth+1;
		if ($sm > 12)
			$sm = 1;
		//$sm = sprintf("%02d",$sm);
		$em = sprintf("%02d",$this->_yearEndMonth);
		$start = new DateTime("{$y1}-{$sm}-01");
		$end = new DateTime();
		$end->setTimestamp($start->getTimestamp() - (3600*24));
		$end = new DateTime("{$y2}-{$em}-{$end->format("d")}");
		return [$start,$end];
	}

	public static function addMonths($month,$addition)
	{
		return ((($month -1) + $addition) % 12) + 1;
	}

	public static function subMonths($month,$subtraction)
	{
		$m = ((($month -1) - $subtraction) % 12) + 1;
		if ($m <= 0)
			$m += 12;
		return $m;
	}

	public static function cadenceRangeMonths($year,$month,$cadance,$monthisfirst=true)
	{
		$y = sprintf("%04d",$year);
		$m = sprintf("%02d",$month);

		error_log("cadenceRangeMonths Year {$year} Month {$month}");
		if (! $monthisfirst)
		{
			//The $month is the last month
			$start = new DateTime("{$y}-{$m}-01 00:00:00");
			error_log("Start = " . $start->format("Y-m-d H:i:s") . " DI = " . $di);
			$di = "P" . strval($cadance-1) . "M";
			$start = $start->sub(new DateInterval($di));
			error_log("Start = " . $start->format("Y-m-d H:i:s") . " DI = " . $di);
			$end = new DateTime($start->format("Y-m-d H:i:s"));
			$di = "P" . strval($cadance) . "M";
			$end = $end->add(new DateInterval($di));
			$end = $end->sub(new DateInterval("P1D"));
		}
		else
		{
			$m2 = sprintf("%02d",self::subMonths($month,$cadance-1));
			error_log("Month {$month} m2 {$m2}");
			$start = new DateTime("{$y}{$m2}01 00:00:00");
			$end = new DateTime("{$y}{$m}01 23:59:59");
		}
		return [$start->format("Y-m-d H:i:s"),$end->format("Y-m-d H:i:s")];
	}

	public static function cadenceMonths($cadance,$first)
	{
		$months = array();
		$next = self::subMonths($first,1);
		for ($c=0;$c < 12/$cadance;$c++)
		{
			$months[$next] = self::$_months[$next-1];
			$next = self::addMonths($next,$cadance);
		}
		ksort($months);
		return $months;
	}

	public static function startEndForDate($date,$cadence,$startmonth)
	{
		$dt = new DateTime($date);
		$year = intval($dt->format("Y"));
		$month = intval($dt->format("m"));

		return AccountDate::cadenceRangeMonths($year, $month, $cadence, false);
	}
}

class LedgerAmount
{
	private $_net;
	private $_tax;
	private $_gross;

	function __construct($net=0.0,$tax=0.0,$gross=0.0)
	{
		$this->_net = $net;
		$this->_tax = $tax;
		$this->_gross = $gross;
	}

	public function _createFromGoss($gross,$taxrate)
	{
		$this->_gross = $gross;
		$this->_net = round($gross / (1+$taxrate),2,PHP_ROUND_HALF_DOWN);
		$this->_tax = $this->_gross - $this->_net;
	}

	public static function createFromGoss($gross,$taxrate)
	{
		$a = new LedgerAmount();
		$a->_createFromGoss($gross,$taxrate);
		return $a;
	}

	public function _createFromNet($net,$taxrate)
	{
		$this->_net = $net;
		$this->_tax = round($net * $taxrate,2,PHP_ROUND_HALF_DOWN);
		$this->_gross = $this->_net + $this->_tax;
	}

	public static function createFromNet($net,$taxrate)
	{
		$a = new LedgerAmount();
		$a->_createFromNet($net,$taxrate);
		return $a;
	}

	public static function format1($v)
	{
		$ret = "";
		if ($v < 0)
			$ret .= "(";

		$ret .= "$";
		$v2 = abs($v);
		$ret .= number_format($v2,2);
		if ($v < 0)
			$ret .= ")";

		return $ret;
	}

	public function __get($name)
	{
		switch (strtoupper($name) )
		{
			case "NET":
				return $this->_net;
			case "TAX":
				return $this->_tax;
			case "GROSS":
				return $this->_gross;
		}
		return null;
	}

	public function __set($name,$value)
	{
		switch (strtoupper($name) )
		{
			case "NET":
				$this->_net = $value;
				break;
			case "TAX":
				$this->_tax = $value;
				break;
			case "GROSS":
				$this->_gross = $value;
				break;
		}
	}
}

class Money
{
	/**
	 * Summary of getInputAmount
	 * Returns amount as a float from an string input field.
	 * @param string $str
	 * @return float
	 */
	public static function getInputAmount($str)
	{
		$str = trim($str);
		$str = str_replace("$", "", $str);
		return floatval($str);
	}
}
?>