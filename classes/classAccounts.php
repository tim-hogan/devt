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
        $this->_net = round($gross / (1+$taxrate),2);
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
        $this->_tax = round($net * $taxrate,2);
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

?>