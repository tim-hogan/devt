<?php
namespace devt\finance;
/**
*
* Class Finance is a group fo static finance functions
*
* @author Tim Hogan
*/
class Finance
{
    public static function rti($vstart,$vnow,$years)
    {
        return pow(($vnow/$vstart),(1.0/$years)) -1;
    }

    public static function rtiNow($valueStart,$valueNow,$startTimestamp,$comissionrate=0.0)
    {
        $when = null;
        if (gettype($startTimestamp) == "string")
            $when = (new \DateTime($startTimestamp))->getTimestamp();
        else
            $when = $startTimestamp->getTimestamp();
        $t = ((new \DateTime())->getTimestamp() - $when) / (3600*24*365);
        $v2 = $valueNow * (1.0 - $comissionrate);
        if ($valueStart > 0)
            return pow(($v2/$valueStart),(1.0/$t)) -1;
        else
            return null;
    }

    public static function futureValue($vstart,$rti,$years,$comissionrate=0.0)
    {
        return ($vstart * pow(($rti+1),$years)) / (1-$comissionrate);
    }
}
?>