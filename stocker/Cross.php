<?php
session_start();
header('Content-type: text/csv');
header("Content-Disposition: attachment; filename=\"cross.csv\"");
require_once dirname(__FILE__) . "/includes/classSecure.php";
require_once dirname(__FILE__) . "/includes/classTime.php";
require_once dirname(__FILE__) . "/includes/classStockerDB.php";
$DB = new stockerDB($devt_environment->getDatabaseParameters());

//$firstbitcoin = $DB->FirstRecordForStock('BTC');
//$firstcardano = $DB->FirstRecordForStock('ADA');


$firstbitcoin = $DB->firstXDaysBach('BTC',28);

$cnt = 0;
$values = array();
$r = $DB->AllRecordsForStockFrom('BTC',$firstbitcoin['record_timestamp']);
while ($stock1 = $r->fetch_assoc())
{
    $stock2 = $DB->findNearestTimeWithin("ADA",$stock1['record_timestamp']);
    if ($stock2)
    {
        if ($cnt == 0)
            $startRatio = $stock1['record_value'] / $stock2['record_value'];
        $values[$cnt] = (($stock1['record_value'] / $stock2['record_value']) / $startRatio) - 1.0;
        echo "{$stock1['record_timestamp']},{$stock1['record_value']},{$stock2['record_value']},{$values[$cnt]}\r\n";
        $cnt++;
    }
}
?>