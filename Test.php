<?php
require_once "./includes/classTime.php";
$webname = "test1.tranzpayments.nz";
$b = classTimeHelpers::getDNSNameIP($webname);

echo "IP address for {$webname} follows:\n";
var_dump($b);

$th = new classTimeHelpers();

$tld = $th->getTLD($webname);

echo "tld = {$tld}\n";

$prefix = substr($webname,0, (strlen($webname) - strlen($tld))-1);

echo "prefix = {$prefix}\n";



$b = explode(".",$prefix);
$domain = $b[count($b)-1] . "." . $tld;
$domain = trim($domain,".");

echo "domain = {$domain}\n";


$webname = str_replace($domain,"",$webname);
$webname = trim($webname,".");
echo "webname = {$webname}\n";

//Now we need to find a list of Name servers
$nameServers = classTimeHelpers::getNameServers($domain);

var_dump($nameServers);

echo "Final answer is {$webname} at {$domain}   or {$webname}.{$domain}\n";


$b = classTimeHelpers::getDNSNameIP($webname);
?>