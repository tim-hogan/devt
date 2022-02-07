<?php
require_once dirname(__FILE__) . "/includes/classEnv.php";
require_once dirname(__FILE__) . "/includes/classStockerDB.php";
require_once dirname(__FILE__) . "/includes/classTextMsgNonApache.php";

$env = new Environment('stocker',"220759");

$DB = new stockerDB($env->getDatabaseParameters());


function getData()
{
    $url="https://www.nzx.com/markets/NZSX";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $header = ["user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.82 Safari/537.36",
               "accept-encoding: identity",
               "cache-control: no-cache",
               "pragma: no-cache",
               "sec-ch-ua: \"Google Chrome\";v=\"89\", \"Chromium\";v=\"89\", \";Not A Brand\";v=\"99\"",
               "sec-ch-ua-mobile: ?0",
               "sec-fetch-dest: document",
               "sec-fetch-mode: navigate",
               "sec-fetch-site: none",
               "sec-fetch-user: ?1",
               "upgrade-insecure-requests: 1",
               "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
               ":authority: www.nzx.com",
               ":method: GET",
               ":path: /markets/NZSX",
               ":scheme: https"
        ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    $result = curl_exec($ch);

    $doc = new DOMDocument();
    $doc->loadHTML($result);

    return $doc;
}

function getPrice($doc,$code)
{
    $list = $doc->getElementsByTagName("tr");
    foreach($list as $tr)
    {
        $attr = $tr->attributes;
        $node = $attr->getNamedItem('title');
        if ($node && $node->nodeValue == $code)
        {
            echo "Found TR\n";
            $tds = $tr->getElementsByTagName("td");
            foreach($tds as $td)
            {
                 $tattr = $td->attributes;
                 $tnode = $tattr->getNamedItem('data-title');
                 if ($tnode && $tnode->nodeValue == 'Price')
                 {
                     $v = $td->nodeValue;
                     $v = str_replace("$","",$v);
                     $v = floatval($v);
                     return $v;
                 }
            }
        }
    }
    return null;
}

echo "Stock quote Daemon Start\n";

$lookupcodes = ['AIR','AMP','ATM','IFT','MEL','MFB','NZK','NZO','RYM','SPK','SML','ZEL'];
$exch = $DB->getLastRecord('NZD');
$doc = getData();
foreach($lookupcodes as $code)
{
    $stock = $DB->getStock($code);
    $value = getPrice($doc,$code);
    if ($value)
    {
        $strTime = (new DateTime())->format('Y-m-d H:i:s');
        $DB->createRecord($code,$strTime,$value,'NZD');

        $r = $DB->allWatchesForStock($stock['stock_code']);
        while ($watch = $r->fetch_array(MYSQLI_ASSOC))
        {
            $dt = new DateTime();
            $dt->setTimezone(new DateTimeZone('Pacific/Auckland'));
            $user = $DB->getUser($watch['watch_user']);
            $phone = trim($user['user_phone1']);

            //Hvae they been triggered and now we reset
            if (! $watch['watch_once'])
            {
                if ($watch['watch_above_triggered'] && $value < $watch['watch_above'])
                    $DB->watchUnTriggerAbove($watch['idwatch']);
                if ($watch['watch_below_triggered'] && $value > $watch['watch_below'])
                    $DB->watchUnTriggerBelow($watch['idwatch']);
            }


            if (! $watch['watch_done'] && $watch['watch_above'] != 0 && ! $watch['watch_above_triggered'] && $value >  $watch['watch_above'] )
            {
                $msg = "Stock: {$dt->format('H:i')} {$stock['stock_code']} Has gone OVER {$watch['watch_above']} to {$value}";

                if ($phone && strlen($phone) > 0)
                {
                    $textmessage = new devt\TextMsg\TextMessage();
                    $textmessage->send($phone,$msg,'stocker');
                }

                $DB->watchTriggeredAbove($watch['idwatch']);
            }
            elseif (! $watch['watch_done'] && $watch['watch_below'] != 0 &&  ! $watch['watch_below_triggered'] && $value <  $watch['watch_below'] )
            {
                echo "Have watch BELOW BT\n";
                $msg = "Stock: {$dt->format('H:i')} {$stock['stcok_code']} Has gone UNDER {$watch['watch_below']} to {$value}";
                
                if ($phone && strlen($phone) > 0)
                {
                    $textmessage = new devt\TextMsg\TextMessage();
                    $textmessage->send($phone,$msg,'stocker');
                }

                $DB->watchTriggeredBelow($watch['idwatch']);
            }
        }
    }
}

?>