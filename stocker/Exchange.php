<?php
require_once dirname(__FILE__) . "/includes/classEnv.php";
require_once dirname(__FILE__) . "/includes/classStockerDB.php";

$env = new Environment('stocker',"220759");

$DB = new stockerDB($env->getDatabaseParameters());

function getExcahnge()
{
    $url="http://data.fixer.io/api/latest?access_key=bf4748d67c69fd75ec0716694e3b1af0&symbols=USD,NZD";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
    $result = curl_exec($ch);
    if ($result)
    {
        $d = json_decode($result,true);
        if ($d && $d['success'])
        {
            $dt = new DateTime();
            $dt->setTimestamp($d['timestamp']);
            $rate1 = $d['rates'] ['NZD'] / $d['rates'] ['USD'];
            return ["timestamp" => $dt->format("Y-m-d H:i:s"),"rates" => ["NZD" => $rate1] ];
        }
    }
    return null;
}

if ($data = getExcahnge() )
{
    $DB->createRecord('NZD',$data["timestamp"],$data["rates"] ["NZD"] );
}
?>