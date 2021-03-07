<?php
require_once dirname(__FILE__) . "/includes/classEnv.php";
require_once dirname(__FILE__) . "/includes/classStockerDB.php";
require_once dirname(__FILE__) . "/includes/classTextMsgNonApache.php";

$env = new Environment('stocker',"220759");

$DB = new stockerDB($env->getDatabaseParameters());

function getPrice()
{
    $url="https://api.coindesk.com/v1/bpi/currentprice.json";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
    $result = curl_exec($ch);
    if ($result)
    {
        $d = json_decode($result,true);
        $strTime = $d['time'] ['updatedISO'];
        $dt = new DateTime(substr($strTime,0,10) . " " . substr($strTime,11,8));
        $v = floatval(str_replace(",","",$d['bpi'] ['USD'] ['rate']));
       return ["timestamp" => $dt->format("Y-m-d H:i:s"),"rate" => $v];
    }
    return null;
}

echo "BitCoin Daemon Start\n";
if ($data = getPrice() )
{
    
    $DB->createRecord('BTC',$data["timestamp"],$data["rate"]);
    //Check if price below or above target.
    $r = $DB->allWatchesForStock('BTC');
    while ($watch = $r->fetch_array(MYSQLI_ASSOC))
    {
        $dt = new DateTime();
        $dt->setTimezone(new DateTimeZone('Pacific/Auckland'));

        //Hvae they been triggered and now we reset
        if (! $watch['watch_once'])
        {
            if ($watch['watch_above_triggered'] && floatval($data["rate"]) < $watch['watch_above'])
                $DB->watchUnTriggerAbove($watch['idwatch']);
            if ($watch['watch_below_triggered'] && floatval($data["rate"]) > $watch['watch_below'])
                $DB->watchUnTriggerBelow($watch['idwatch']);
        }


        if (! $watch['watch_done'] && $watch['watch_above'] != 0 && ! $watch['watch_above_triggered'] && floatval($data["rate"]) >  $watch['watch_above'] )
        {
            echo "Have watch ABOVE BT\n";
            $msg = "Stock: {$dt->format('H:i')} BTC Has gone OVER {$watch['watch_above']} to {$data["rate"]}";
            $textmessage = new devt\TextMsg\TextMessage();
            $textmessage->send('+64272484626',$msg,'stocker');

            $DB->watchTriggeredAbove($watch['idwatch']);
        }
        elseif (! $watch['watch_done'] && $watch['watch_below'] != 0 &&  ! $watch['watch_below_triggered'] && floatval($data["rate"]) <  $watch['watch_below'] )
        {
            echo "Have watch BELOW BT\n";
            $msg = "Stock: {$dt->format('H:i')} BTC Has gone UNDER {$watch['watch_below']} to {$data["rate"]}";
            $textmessage = new devt\TextMsg\TextMessage();
            $textmessage->send('+64272484626',$msg,'stocker');

            $DB->watchTriggeredBelow($watch['idwatch']);
        }
    }
}

?>