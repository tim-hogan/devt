<?php
require_once dirname(__FILE__) . "/includes/classEnv.php";
require_once dirname(__FILE__) . "/includes/classStockerDB.php";
require_once dirname(__FILE__) . "/includes/classTextMsgNonApache.php";
require_once dirname(__FILE__) . "/includes/classFinance.php";

$env = new Environment('stocker',"220759");

$DB = new stockerDB($env->getDatabaseParameters());
$exch = $DB->getLastRecord('NZD');
$toNZ = $exch['record_value'];
$dt = new DateTime();
$dt->setTimezone(new DateTimeZone('Pacific/Auckland'));

$r = $DB->allActiveWatchesWithUser();
while ($watch = $r->fetch_array(MYSQLI_ASSOC))
{
    if ($watch['watch_rti'] != 0)
    {
        //Look for portfolio for user and stock
        $r2 = $DB->allPortfolioBuyForUserStock($watch['watch_user'],$watch['watch_stock']);
        while ($port = $r2->fetch_array(MYSQLI_ASSOC))
        {
            $currentPrice = 0.0;
            $last = $DB->getLastRecord(intval($watch['watch_stock']));
            if ($last)
            {
                if ($last['record_currency'] != 'NZD')
                    $currentPrice = $last['record_value'] * $toNZ;
                else
                    $currentPrice = $last['record_value'];

                $rti = devt\finance\Finance::rtiNow($port['portfolio_price'],$currentPrice,$port['portfolio_timestamp'],$port['stock_margin']);
                if ($rti > $watch['watch_rti'])
                {
                    $strrti = number_format($rti*100.0,0) . "%";
                    $phone = trim($watch['user_phone1']);

                    $msg = "Stock **SELL**: {$dt->format('H:i')} {$watch['stock_name']} has a RTI of {$strrti}";

                    if ($phone && strlen($phone) > 0)
                    {
                        $textmessage = new devt\TextMsg\TextMessage();
                        $textmessage->send($phone,$msg,'stocker');
                    }

                    $DB->watchDone($watch['idwatch']);
                }
            }
        }
    }
}
?>