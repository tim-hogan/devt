<?php session_start(); ?>
<?php
//devt.Version = 1.0
header('Content-Type: application/json');

require_once dirname(__FILE__) . "/includes/classEnv.php";
require_once dirname(__FILE__) . "/includes/classStockerDB.php";
require_once dirname(__FILE__) . "/includes/classTextMsgNonApache.php";
require_once dirname(__FILE__) . "/includes/classFinance.php";

$env = new Environment('stocker',"220759");

$DB = new stockerDB($env->getDatabaseParameters());

//Diagnostic
function var_error_log( $object=null , $text='')
{
    ob_start();                    // start buffer capture
    var_dump( $object );           // dump the values
    $contents = ob_get_contents(); // put the buffer into a variable
    ob_end_clean();                // end capture
    error_log( "{$text} {$contents}" );        // log contents of the result of var_dump( $object )
}


/*
Repsonse format
meta:
    status: true | false
    request: <request made>
    time:   <timestamp>
    errorcode:  errorcode if error
    errormsg:   error message if error
data:
    <response data>
*/



//Globals
$key = '';
$req = '';
$reqValue1 = '';
$reqValue2 = '';
$reqValue3 = '';

//Functions
function newMetaResponseHdr($status,$req,$errorcode = null,$errormsg = null)
{
    $dt = new DateTime('now');
    $meta = array();
    $meta['status'] = $status;
    $meta['req'] = $req;
    $meta['time'] = $dt->format('Y-m-d') . "T" . $dt->format('H:i:s') . "Z";
    $meta['errorcode'] = $errorcode;
    $meta['errormsg'] = $errormsg;
    return $meta;
}

function newErrorMetaHdr($req,$errorcode,$errormsg)
{
    return newMetaResponseHdr(false,$req,$errorcode,$errormsg);
}

function newOKMetaHdr($req)
{
    return newMetaResponseHdr(true,$req);
}

function returnError($req,$code,$desc)
{
   $rslt = array();
   $meta = newErrorMetaHdr($req,$code,$desc);
   $rslt['meta'] = $meta;
   $rslt['data'] = array();
   echo json_encode($rslt);
   exit();
}

/*
***********************************************************************
GET FUNCTIONS
***********************************************************************
*/

function getGraphData($req,$stock,$days)
{
    global $DB;
    $data = array();

    $stockrecord = $DB->getStock($stock);


    $graphdata = array();
    array_push($graphdata,[["label" => "Time", "type" => "date" ], "Value","Fib 1","Fib 2"]);
    $range = $DB->range($stock,$days);
    $fib1 = $range['max'] - (0.618*($range['max'] - $range['min']));
    $fib2 = $range['max'] - (0.382*($range['max'] - $range['min']));
    $r = $DB->LastRecordsForStock($stock,$days);
    while ($record = $r->fetch_array(MYSQLI_ASSOC))
    {
        $dt = new DateTime($record['record_timestamp']);
        $dt->setTimezone(new DateTimeZone('Pacific/Auckland'));
        $strDate = $dt->format("Y,m,d,H,i,s");
        $entry = array();
        $entry[0] = $dt->getTimestamp() * 1000;
        $entry[1] = $record['record_value'];
        $entry[2] = $fib1;
        $entry[3] = $fib2;
        array_push($graphdata,$entry);
    }

    $range = $DB->range($stock,28);

    $data['stock'] = $stock;
    $data['title'] = $stockrecord['stock_name'];

    $data['history'] = array();
    $data['history'] ['values'] = array();
    $data['history'] ['strings'] = array();
    $data['history'] ['change'] = array();
    $data['history'] ['ranges'] = array();
    $data['history'] ['change'] ['values'] = array();
    $data['history'] ['change'] ['strings'] = array();

    $data['history'] ['values'] ['1D'] = ($DB->firstXDaysBach($stock,1)) ['record_value'];
    $data['history'] ['values'] ['7D'] = ($DB->firstXDaysBach($stock,7)) ['record_value'];
    $data['history'] ['values'] ['28D'] = ($DB->firstXDaysBach($stock,28)) ['record_value'];
    $data['history'] ['values'] ['1H'] = ($DB->firstXHoursBach($stock,1)) ['record_value'];
    $data['history'] ['values'] ['LAST'] = ($DB->getLastRecord($stock)) ['record_value'];

    $data['history'] ['strings'] ['1D'] = "$" . number_format($data['history'] ['values'] ['1D'],3);
    $data['history'] ['strings'] ['7D'] = "$" . number_format($data['history'] ['values'] ['7D'],3);
    $data['history'] ['strings'] ['28D'] = "$" . number_format($data['history'] ['values'] ['28D'],3);
    $data['history'] ['strings'] ['1H'] = "$" . number_format($data['history'] ['values'] ['1H'],3);
    $data['history'] ['strings'] ['LAST'] = "$" . number_format($data['history'] ['values'] ['LAST'],3);

    if ($data['history'] ['values'] ['1D'])
    {
        $data['history'] ['change'] ['values'] ['D1'] = ($data['history'] ['values'] ['LAST'] / $data['history'] ['values'] ['1D']) -1.0;
        $data['history'] ['change'] ['strings'] ['D1'] = number_format((($data['history'] ['values'] ['LAST'] / $data['history'] ['values'] ['1D'])-1.0)*100.0,2) . "%";
    }
    else
    {
        $data['history'] ['change'] ['values'] ['D1'] = 0.0;
        $data['history'] ['change'] ['strings'] ['D1'] = "0.0%";
    }

    if ($data['history'] ['values'] ['7D'])
    {
        $data['history'] ['change'] ['values'] ['D7'] = ($data['history'] ['values'] ['LAST'] / $data['history'] ['values'] ['7D']) -1.0;
        $data['history'] ['change'] ['strings'] ['D7'] = number_format((($data['history'] ['values'] ['LAST'] / $data['history'] ['values'] ['7D'])-1.0)*100.0,2) . "%";
    }
    else
    {
        $data['history'] ['change'] ['values'] ['D7'] = 0.0;
        $data['history'] ['change'] ['strings'] ['D7'] = "0.0%";
    }

    if ($data['history'] ['values'] ['28D'])
    {
        $data['history'] ['change'] ['values'] ['D28'] = ($data['history'] ['values'] ['LAST'] / $data['history'] ['values'] ['28D']) -1.0;
        $data['history'] ['change'] ['strings'] ['D28'] = number_format((($data['history'] ['values'] ['LAST'] / $data['history'] ['values'] ['28D'])-1.0)*100.0,2) . "%";
    }
    else
    {
        $data['history'] ['change'] ['values'] ['D28'] = 0.0;
        $data['history'] ['change'] ['strings'] ['D28'] = "0.0%";
    }

    if ($data['history'] ['values'] ['1H'])
    {
        $data['history'] ['change'] ['values'] ['H1'] = ($data['history'] ['values'] ['LAST'] / $data['history'] ['values'] ['1H']) -1.0;
        $data['history'] ['change'] ['strings'] ['H1'] = number_format((($data['history'] ['values'] ['LAST'] / $data['history'] ['values'] ['1H'])-1.0)*100.0,2) . "%";
    }
    else
    {
        $data['history'] ['change'] ['values'] ['H1'] = 0.0;
        $data['history'] ['change'] ['strings'] ['H1'] = "0.0%";
    }

    $range = $DB->range($stock,28);

    $data['history'] ['ranges'] ['28'] ['min'] = $range['min'];
    $data['history'] ['ranges'] ['28'] ['max'] = $range['max'];
    $data['history'] ['ranges'] ['28'] ['fib1'] = $range['max'] - (0.618 * ($range['max']-$range['min']));
    $data['history'] ['ranges'] ['28'] ['fib2'] = $range['max'] - (0.382 * ($range['max']-$range['min']));

    $data['graphdata'] = $graphdata;

    $ret = array();
    $ret['meta'] = newOKMetaHdr($req);
    $ret['data'] = $data;
    echo json_encode($ret);
    exit();

}

function getFutureData($req,$stock,$daysback,$daysfwd,$buyprice,$buydate,$desiredRti,$comissionrate)
{

    global $DB;
    $data = array();

    $stockrecord = $DB->getStock($stock);
    $exch = $DB->getLastRecord('NZD');
    $toUS = 1.0/$exch['record_value'];
    $dtbuy = new DateTime($buydate);
    $dtbuy->setTimezone(new DateTimeZone('Pacific/Auckland'));

    $graphdata = array();
    array_push($graphdata,[["label" => "Time", "type" => "date" ], "Value","Required"]);


    $r = $DB->LastRecordsForStock($stock,$daysback);
    while ($record = $r->fetch_array(MYSQLI_ASSOC))
    {
        $conversion = 1.0;
        if ($record['record_currency'] != 'NZD')
            $conversion = $toUS;

        $dt = new DateTime($record['record_timestamp']);
        $dt->setTimezone(new DateTimeZone('Pacific/Auckland'));

        $strDate = $dt->format("Y,m,d,H,i,s");
        $entry = array();
        $entry[0] = $dt->getTimestamp() * 1000;
        $entry[1] = $record['record_value'];
        $entry[2] = null;
        if ($dt > $dtbuy)
        {
            $years = ($dt->getTimestamp() - $dtbuy->getTimestamp()) / (3600*24*365);
            $v = devt\finance\Finance::futureValue($buyprice,$desiredRti,$years,$comissionrate);
            $entry[2] = $v * $conversion;
        }


        array_push($graphdata,$entry);
    }

    $dtnow = new DateTime();
    $dtnow->setTimezone(new DateTimeZone('Pacific/Auckland'));

    $dtend = new DateTime();
    $dtend->setTimezone(new DateTimeZone('Pacific/Auckland'));
    $dtend->setTimestamp($dtend->getTimestamp() + (3600*24*$daysfwd) );


    while ($dtnow < $dtend)
    {
        $dtnow->setTimestamp($dtnow->getTimestamp() + (3600*24) );
        $entry = array();
        $entry[0] = $dtnow->getTimestamp() * 1000;
        $entry[1] = null;
        $years = ($dtnow->getTimestamp() - $dtbuy->getTimestamp()) / (3600*24*365);
        $entry[2] = devt\finance\Finance::futureValue($buyprice,$desiredRti,$years,$comissionrate) * $conversion;
        array_push($graphdata,$entry);
    }

    $data['graphdata'] = $graphdata;

    $ret = array();
    $ret['meta'] = newOKMetaHdr($req);
    $ret['data'] = $data;
    echo json_encode($ret);
    exit();

}

/*
***********************************************************************
PUT AND POST FUNCTIONS
***********************************************************************
*/
function setSessionData($req,$params)
{
    $data = array();

    $_SESSION[$params['name']] =  $params['value'];

    $ret = array();
    $ret['meta'] = newOKMetaHdr($req);
    $ret['data'] = $data;
    echo json_encode($ret);
    exit();
}

function enableWatch($req,$params)
{
    global $DB;
    $data = array();

    $watchid = $params['watch'];
    $value = $params['value'];

    $DB->setWatch($watchid,$value);

    $ret = array();
    $ret['meta'] = newOKMetaHdr($req);
    $ret['data'] = $data;
    echo json_encode($ret);
    exit();
}

function setWatchBelow($req,$params)
{
    global $DB;
    $data = array();

    $watchid = $params['watch'];
    $value = $params['value'];

    $DB->setWatchBelow($watchid,$value);

    $ret = array();
    $ret['meta'] = newOKMetaHdr($req);
    $ret['data'] = $data;
    echo json_encode($ret);
    exit();

}

function setWatchAbove($req,$params)
{
    global $DB;
    $data = array();

    $watchid = $params['watch'];
    $value = $params['value'];

    $DB->setWatchAbove($watchid,$value);

    $ret = array();
    $ret['meta'] = newOKMetaHdr($req);
    $ret['data'] = $data;
    echo json_encode($ret);
    exit();

}

function archivePortfolio($req,$params)
{
    global $DB;
    $data = array();

    $portid = $params['tranid'];
    $value = $params['value'];

    $DB->setPortfolioArchive($portid,$value);

    $ret = array();
    $ret['meta'] = newOKMetaHdr($req);
    $ret['data'] = $data;
    echo json_encode($ret);
    exit();

}

//Start
if (!isset($_GET['r']))
    returnError(null,1000,"Invalid parameter");

$r = $_GET['r'];
$tok = strtok($r,"/");
if (strlen($tok) == 16)
{
    $key = $tok;
    $req = strtok("/");
}
else
    $req = $tok;
$reqValue1 =strtok("/");
$reqValue2 =strtok("/");
$reqValue3 =strtok("/");

if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $result = array();
    switch (strtolower($req))
    {
        case 'graphdata':
            getGraphData($req,$reqValue1,$reqValue2);
            break;
        default:
            returnError($req,1000,"Invalid parameter");
            break;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'PUT'  || $_SERVER['REQUEST_METHOD'] == 'POST')
{

    $contents = file_get_contents('php://input');
    $params = array();
    $params = json_decode($contents,true);

    switch (strtolower($req))
    {
        case 'future':
            $stock = $params['stock'];
            $daysback = $params['backdays'];
            $daysfwd = $params['fwddays'];
            $buyprice = $params['buyprice'];
            $buydate = $params['buydate'];
            $desiredRti = $params['rti'];
            $comissionrate = $params['ratecommision'];
            getFutureData($req,$stock,$daysback,$daysfwd,$buyprice,$buydate,$desiredRti,$comissionrate);
            break;
    case 'setsession':
        setSessionData($req,$params);
        break;
    case 'enablewatch':
        enableWatch($req,$params);
        break;
    case 'setwatchbelow':
        setWatchBelow($req,$params);
        break;
    case 'setwatchabove':
        setWatchAbove($req,$params);
        break;
    case 'archivetran':
        archivePortfolio($req,$params);
        break;
    default:
        returnError($req,1000,"Invalid parameter");
        break;
    }
}
?>