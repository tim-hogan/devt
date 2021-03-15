<?php
require_once dirname(__FILE__) . "/includes/classEnv.php";
require_once dirname(__FILE__) . "/includes/classStockerDB.php";
require_once dirname(__FILE__) . "/includes/classTextMsgNonApache.php";

$env = new Environment('stocker',"220759");

$DB = new stockerDB($env->getDatabaseParameters());

function getPrice($code)
{
    $url="https://www.nzx.com/markets/NZSX";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
    $result = curl_exec($ch);
    var_dump($result);

    $doc = new DOMDocument();
    $doc->loadHTML($result);

    $list = $doc->getElementsByTagName("tr");
    echo "Count of TR items is {$list->count()}\n";
    for ($c = 0; $c < $list->count(); $c++)
    {
        $n = $list->item($c);
        $map = $n->DOMNamedNodeMap;
        for ($a = 0; $a < $map->count(); $a++)
        {
            $at = $map->index($a);
            if ($at->nodeName == 'title' && $at->nodeValue == $code)
            {
                echo "Found table row for {$code}\n";
            }
        }
    }

}

echo "Stcok quote Daemon Start\n";

$lookupcodes = ['AIR'];
$exch = $DB->getLastRecord('NZD');
foreach($lookupcodes as $code)
{
    $stock = $DB->getStock($code);
    getPrice($code);
    exit();

    //$data = getPrice($stock['stock_international_code']);
    if ($data)
    {
        $v = $data["rate"] * $exch['record_value'];
        $DB->createRecord($stock['stock_code'],$data["timestamp"],$v,'NZD');
        $r = $DB->allWatchesForStock($stock['stock_code']);
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
                $msg = "Stock: {$dt->format('H:i')} {$stock['stcok_code']} Has gone OVER {$watch['watch_above']} to {$data["rate"]}";
                $textmessage = new devt\TextMsg\TextMessage();
                $textmessage->send('+64272484626',$msg,'stocker');

                $DB->watchTriggeredAbove($watch['idwatch']);
            }
            elseif (! $watch['watch_done'] && $watch['watch_below'] != 0 &&  ! $watch['watch_below_triggered'] && floatval($data["rate"]) <  $watch['watch_below'] )
            {
                echo "Have watch BELOW BT\n";
                $msg = "Stock: {$dt->format('H:i')} {$stock['stcok_code']} Has gone UNDER {$watch['watch_below']} to {$data["rate"]}";
                $textmessage = new devt\TextMsg\TextMessage();
                $textmessage->send('+64272484626',$msg,'stocker');

                $DB->watchTriggeredBelow($watch['idwatch']);
            }
        }
    }
}

?>