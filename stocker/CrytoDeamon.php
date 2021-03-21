<?php
require_once dirname(__FILE__) . "/includes/classEnv.php";
require_once dirname(__FILE__) . "/includes/classStockerDB.php";
require_once dirname(__FILE__) . "/includes/classTextMsgNonApache.php";

$env = new Environment('stocker',"220759");

$DB = new stockerDB($env->getDatabaseParameters());
$whatcrytos = ["1027" =>"ETH","2010"=>'ADA'];
$quotes = array();

function isoToDateTime($str)
{
    return substr(str_replace("T"," ",$str),0,19);
}

function getPrice()
{
    global $whatcrytos;
    $url="https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest";
    $url .= "?id=";
    $done1 = false;
    foreach($whatcrytos as $id => $code)
    {
        if ($done1)
            $url .= ",";
        $url .= $id;
        $done1 = true;
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","X-CMC_PRO_API_KEY: fcffca79-055d-46fa-8745-ddcef596f2e2"));
    $result = curl_exec($ch);
    if ($result)
    {
        $d = json_decode($result,true);
        if ($d['status'] ['error_code'] == 0)
        {
            return $d;
        }
    }
    return null;
}

echo "Cryto Daemon Start\n";
if ($data = getPrice() )
{

    //Loop here for all
    $strT = $data['status'] ['timestamp'];
    $strT = substr(str_replace("T"," ",$strT),0,19);
    $dt = new DateTime($strT);

    $data = $data['data'];
    foreach($whatcrytos as $id => $code)
    {
        $stock = $data[intval($id)];
        if ($stock)
        {
            $quote = $stock['quote'] ['USD'] ['price'];
            $quotes[$code] = floatval($quote);
            $strT = isoToDateTime($stock['quote'] ['USD'] ['last_updated']);
            $DB->createRecord($code,isoToDateTime($stock['quote'] ['USD'] ['last_updated']),$quote);
        }
    }

    //Check if price below or above target.
    foreach($whatcrytos as $id => $code)
    {
        $r = $DB->allWatchesForStock($code);
        while ($watch = $r->fetch_array(MYSQLI_ASSOC))
        {
            $dt = new DateTime();
            $dt->setTimezone(new DateTimeZone('Pacific/Auckland'));
            $user = $DB->getUser($watch['watch_user']);
            $phone = trim($user['user_phone1']);

            //Hvae they been triggered and now we reset
            if (! $watch['watch_once'])
            {
                if ($watch['watch_above_triggered'] && $quotes[$code] < $watch['watch_above'])
                    $DB->watchUnTriggerAbove($watch['idwatch']);
                if ($watch['watch_below_triggered'] && $quotes[$code] > $watch['watch_below'])
                    $DB->watchUnTriggerBelow($watch['idwatch']);
            }


            if (! $watch['watch_done'] && $watch['watch_above'] != 0 && ! $watch['watch_above_triggered'] && $quotes[$code] >  $watch['watch_above'] )
            {
                echo "Have watch ABOVE BT\n";
                $msg = "Stock: {$dt->format('H:i')} {$code} Has gone OVER {$watch['watch_above']} to {$quotes[$code]}";
                if ($phone && strlen($phone) > 0)
                {
                    $textmessage = new devt\TextMsg\TextMessage();
                    $textmessage->send($phone,$msg,'stocker');
                }

                $DB->watchTriggeredAbove($watch['idwatch']);
            }
            elseif (! $watch['watch_done'] && $watch['watch_below'] != 0 &&  ! $watch['watch_below_triggered'] && $quotes[$code] <  $watch['watch_below'] )
            {
                echo "Have watch BELOW BT\n";
                $msg = "Stock: {$dt->format('H:i')} {$code} Has gone UNDER {$watch['watch_below']} to $quotes[$code]}";
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