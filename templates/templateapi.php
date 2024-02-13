<?php session_start(); ?>
<?php
//devt.Version = 1.0
header('Content-Type: application/json');

require './includes/classSecure.php';
require_once "./includes/classRolling.php";

require './includes/classEditDB.php';
$DB = new editDB($devt_environment->getDatabaseParameters());

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
function getSomething($req)
{
    $data = array();


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
function postSomething($req,$params)
{
    $data = array();

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
        case 'getsomething':
            getSomething($req);
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
    case 'postsoemthing':
        postSomething($req,$params);
        break;
    default:
        returnError($req,1000,"Invalid parameter");
        break;
    }
}
?>