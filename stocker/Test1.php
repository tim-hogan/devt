<?php
session_start();
header('Content-type: text/csv');
header("Content-Disposition: attachment; filename=\"Regression.csv\"");
require_once dirname(__FILE__) . "/includes/classSecure.php";
require_once dirname(__FILE__) . "/includes/classTime.php";
require_once dirname(__FILE__) . "/includes/classStockerDB.php";
$DB = new stockerDB($devt_environment->getDatabaseParameters());
$days = 1;
$a = $DB->linearRegression('BTC',$days);
$dt = (new DateTime())->getTimestamp();
$y = ($dt * $a['m']) + $a['c'];
$c = $a['m'] * 3600 * 24;
echo "{$a['m']},{$a['c']},{$a['avgx']},{$a['avgy']},{$a['sum1']},{$a['sum2']}\r\n";

$dt = new DateTime();
$dt->setTimestamp($dt->getTimestamp() - (3600*24*$days));
$strTime = $dt->format('Y-m-d H:i:s');

$r = $DB->AllRecordsForStockFrom('BTC',$strTime);
while ($s = $r->fetch_assoc())
{
    $x = (new DateTime($s['record_timestamp']))->getTimestamp();
    echo "{$x},{$s['record_value']}\r\n";
}
?>