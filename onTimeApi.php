<?php session_start(); ?>
<?php
//devt.Version = 1.0
header('Content-Type: application/json');

require './includes/classSecure.php';
require_once "./includes/classRolling.php";
require_once "./includes/classCSV.php";

//require './includes/classEditDB.php';
//$DB = new editDB($devt_environment->getDatabaseParameters());

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
   $data = array();
   $rslt['meta'] = $meta;
	$rslt['data'] = array();
	echo json_encode($rslt);
	exit();
}

function DistKM($lat, $long, $dlat, $dlong)
{
	if ($lat == $dlat && $long == $dlong)
		return 0.0;
	$toRadians = (3.14159265358979 / 180.0);
	$latFrom = $lat * $toRadians;
	$longFrom = $long * $toRadians;
	$latTo = $dlat * $toRadians;
	$longTo = $dlong * $toRadians;
	$theta = 0;
	$theta = sin($latFrom) * sin($latTo) + (cos($latFrom) * cos($latTo) * cos($longFrom - $longTo));
	return (6378.15 * acos($theta));
}

function findNearestStop($lat,$lon,$stoptimes)
{
	$dmin = PHP_FLOAT_MAX;
	$savedstop = null;
	$stop = $stoptimes->resetPosition();
	while($stop = $stoptimes->next())
	{
		$d = DistKM($lat, $lon, $stoptimes->stop_lat, $stoptimes->stop_lon);
		if ($d < $dmin)
		{
			$dmin = $d;
			$savedstop = $stop;
		}
	}
	error_log("Min distance km = {$dmin}");

	return $savedstop;
}

function findNearestShape($lat, $lon, $shapes)
{
	$dmin = PHP_FLOAT_MAX;
	$savedshape = null;
	$stop = $shapes->resetPosition();
	while ($shape = $shapes->next())
	{
		$d = DistKM($lat, $lon, $shapes->shape_pt_lat, $shapes->shape_pt_lon);
		if ($d < $dmin)
		{
			$dmin = $d;
			$savedshape = $shape;
		}
	}
	return $savedshape;
}

function crcreateShapeFromStop($stop,$shape_id)
{
	$shape = array();
    $shape["shape_id"] = $shape_id;
	$shape["shape_pt_lat"] = $stop["stop_lat"];
	$shape["shape_pt_lon"] = $stop["stop_lon"];
	$shape["shape_pt_sequence"] = 0;
    $shape["shape_dist_traveled"] = 0.0;
}
function stopwithin50($lat,$lon,$stoptimes)
{
	$dmin = PHP_FLOAT_MAX;
	$savedstop = null;
	$stop = $stoptimes->resetPosition();
	while($stop = $stoptimes->next())
	{
		$d = DistKM($lat, $lon, $stoptimes->stop_lat, $stoptimes->stop_lon);
		if ($d < $dmin)
		{
			$dmin = $d;
			if ($d <= 0.05)
				$savedstop = $stop;
		}
	}
	return $savedstop;
}

function removeUnicodeString($s)
{
	if (substr(bin2hex($s), 0, 6) == "efbbbf")
		return substr($s, 3);
	else
		return $s;
}

function loadCSV($filename,&$keys,&$data)
{
	$f = fopen($filename, "r");
	if ($f)
	{
		while (($row = fgetcsv($f, 32000)) !== false)
		{
			if (empty($keys)) {
				$keys = $row;
				$keys[0] = removeUnicodeString($keys[0]);
				continue;
			}
			$data[] = $row;
		}

	}
	fclose($f);
}

function getTrip($filename, $tripid)
{
	$trip = array();
	$trip["fields"] = array();
	$trip["data"] = array();

	$idxTripId = null;
	$f = fopen($filename, "r");
	if ($f) {
		while (($row = fgetcsv($f, 32000)) !== false) {
			if (empty($trip["fields"])) {
				$trip["fields"] = $row;
				$trip["fields"][0] = removeUnicodeString($trip["fields"][0]);
				$idxTripId = array_search("trip_id", $trip["fields"]);
				continue;
			}
			if ($row[$idxTripId] == $tripid)
			{
				$trip["data"] = $row;
				break;
			}
		}
	}

	fclose($f);

	return $trip;
}

function getStopTimes($filename, $tripid)
{
	$stop_times = array();
	$stop_times["fields"] = array();
	$stop_times["data"] = array();

	$idxTripId = null;
	$f = fopen($filename, "r");
	if ($f) {
		while (($row = fgetcsv($f, 32000)) !== false) {
			if (empty($stop_times["fields"])) {
				$stop_times["fields"] = $row;
				$stop_times["fields"][0] = removeUnicodeString($stop_times["fields"][0]);
				$idxTripId = array_search("trip_id", $stop_times["fields"]);
				continue;
			}
			if ($row[$idxTripId] == $tripid) {
				$stop_times["data"][] = $row;
			}
		}
	}

	fclose($f);

	return $stop_times;
}

function getStops($filename)
{
	$stops = array();
	$stops["fields"] = array();
	$stops["data"] = array();

	$idxTripId = null;
	$f = fopen($filename, "r");
	if ($f) {
		while (($row = fgetcsv($f, 32000)) !== false) {
			if (empty($stops["fields"])) {
				$stops["fields"] = $row;
				$stops["fields"][0] = removeUnicodeString($stops["fields"][0]);
				$idxTripId = array_search("trip_id", $stops["fields"]);
				continue;
			}
			$stops["data"][] = $row;
		}
	}

	fclose($f);

	return $stops;
}

function findstop($stop_id,$stops)
{
	$idxStopId = array_search("stop_id",$stops["fields"]);
	foreach($stops["data"] as $stop)
	{
		if ($stop[$idxStopId] == $stop_id)
			return $stop;
	}
	return null;
}

function augmentStopsTimes($filename,&$stoptimes)
{
	$stops = getStops($filename);

	$stoptimes["fields"] [] = "stop_lat";
	$stoptimes["fields"] [] = "stop_lon";

	$idxStopLat = array_search("stop_lat",$stops["fields"]);
	$idxStopLon = array_search("stop_lon",$stops["fields"]);

	$idxStopId = array_search("stop_id",$stoptimes["fields"]);
	$idxSTLat = array_search("stop_lat",$stoptimes["fields"]);
	$idxSTLon= array_search("stop_lon",$stoptimes["fields"]);


	$stoptms = $stoptimes["data"];
	for($idx = 0; $idx < count($stoptms);$idx++)
	{
		$stop = findstop($stoptms[$idx] [$idxStopId],$stops);
		if ($stop)
		{
			$stoptimes["data"] [$idx] [$idxSTLat] =  $stop[$idxStopLat];
			$stoptimes["data"] [$idx] [$idxSTLon] =  $stop[$idxStopLon];
		}
	}
}

function loadTripsForRoute($filename,$routeId,&$trips)
{
	$fields = array();
	$data = array();
	$rettrips = array();

	loadCSV($filename, $fields, $data);

	$rout_idx = array_search("route_id",$fields);
	foreach($data as $t)
	{
		if ($t[$rout_idx] == $routeId)
		{
			$rettrips[] = $t;
		}
	}

	$trips["fields"] = $fields;
	return $rettrips;
}

function loadStopTimesForTrip($filename,$trip_id,&$stop_times)
{
	$fields = array();
	$data = array();
	$rettrips = array();

	loadCSV($filename, $fields, $data);
	$stop_times["fields"] = $fields;
	$stop_times["data"] = $data;
}

function searchDepartTimeForTrip($filename,$trip_id)
{
	$idxTripId = 0;
	$idxSeq = 0;
	$idxDepTime = 0;
	$f = fopen($filename, "r");
	if ($f)
	{
		while (($row = fgetcsv($f, 32000)) !== false)
		{
			if (empty($keys))
			{
				$keys = $row;
				$keys[0] = removeUnicodeString($keys[0]);
				$idxTripId = array_search("trip_id",$keys);
				$idxSeq = array_search("stop_sequence",$keys);
				$idxDepTime = array_search("departure_time",$keys);
				continue;
			}
			if ($row[$idxTripId] == $trip_id && $row[$idxSeq] == 1)
				return $row[$idxDepTime];
		}
	}
	return "00:00:00";
}

function getFirstStopSeqForTrip($trip_id,$stop_times)
{
	$idxTripId = array_search("trip_id",$stop_times["fields"]);
	$idxSeq = array_search("stop_sequence",$stop_times["fields"]);
	$idxDepTime = array_search("departure_time",$stop_times["fields"]);

	foreach($stop_times["data"] as $st)
	{
		if ($st[$idxTripId] == $trip_id && $st[$idxSeq] == 1)
		{
			return $st[$idxDepTime];
		}
	}
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
function getTripsForRoute($req,$params)
{
	$timeZone = new DateTimeZone("Pacific/Auckland");
	$strToday = (new DateTime('now', $timeZone))->format("Y-m-d");

	$today = new DateTime('now', new DateTimeZone("Pacific/Auckland"));
	$dayname = strtolower($today->format("l"));

	$calendar = array();
	$calendar["fields"] = array();
	$calendar["data"] = array();
    $routes = new CSVRec("./data/routes.txt");



    loadCSV("./data/calendar.txt", $calendar["fields"], $calendar["data"]);

	$validServiceIds = array();
	$idxServiceId  = array_search("service_id",$calendar["fields"]);
	$idxDay = array_search($dayname,$calendar["fields"]);
	foreach($calendar["data"] as $ce)
	{
		if ($ce[$idxDay] == "1")
			$validServiceIds[] = $ce[$idxServiceId];
	}


	$trips = array();
	$trips["fields"] = array();
	$trips["data"] = array();

	$data = array();
	$rtrips = loadTripsForRoute("./data/trips.txt", $params["route_id"], $trips);

	//Get valid service ids
	$rtrips2 = array();
	$idxSId = array_search("service_id", $trips["fields"]);


	foreach($rtrips as $t)
	{
		if (in_array($t[$idxSId], $validServiceIds))
			$rtrips2[] = $t;
	}


	usort($rtrips2, function ($a, $b) {
		$i = count($a) - 1;
		return $a[$i] <=> $b[$i];
	});

	//Convert timestamp to serial
	$trips["fields"][] = "timestamp";
	for ($idx = 0; $idx < count($rtrips2);$idx++)
	{
		$fc = count($rtrips2[$idx]);
		$time = (new DateTime($strToday . " " . $rtrips2[$idx] [$fc-1], $timeZone))->getTimestamp();
		$rtrips2[$idx][$fc] = $time;
	}


	$data = array();

    $routes->find("route_id", $params["route_id"]);
	$data["headsign"] = ["route" => $routes->route_short_name];
	$data["keys"] = $trips["fields"];
	$data["trips"] = $rtrips2;



	$ret = array();
	$ret['meta'] = newOKMetaHdr($req);
	$ret['data'] = $data;
	echo json_encode($ret);
	exit();
}

function getTimedShapeData($req, $params)
{

	$timeZone = new DateTimeZone("Pacific/Auckland");
	$strToday = (new DateTime('now', $timeZone))->format("Y-m-d");
	$trips = new CSVRec("./data/trips.txt", "trip_id", $params["trip_id"]);
    $trips->first();
	$shapes = new CSVRec("./data/shapes.txt", "shape_id", $trips->shape_id);
	$stops = new CSVRec("./data/stops.txt");
	$stoptimes = new CSVRec("./data/stop_times.txt","trip_id",$params["trip_id"]);

	$stoptimes->blend($stops,"stop_id",["stop_lat","stop_lon","stop_name"]);
	$stoptimes->resetPosition();
	$firststop = $stoptimes->next();

	error_log("Core tables built");

	//Build a combined list of shapes and stoptimes
	$shapes->resetPosition();
    $firstshape = $shapes->next();


	//Loop here for each shape, find nearest stop and predict timing point.
	//Find the nearest first stop

	error_log(" updating shapes within 50 metres");
	//Update shapes with within 50 meters of stop id
	$shapes->resetPosition();
	while($shape = $shapes->next())
	{
		$stop = stopWithin50($shapes->shape_pt_lat,$shapes->shape_pt_lon,$stoptimes);
		if ($stop)
			$shapes->addColumn("near_stop", $stop["stop_id"]);
	}

	error_log(" updating shapes withd distances");
	//Update shapes with distances
	$last = null;
	$acu_dist = 0;
	$shapes->resetPosition();
	while ($shapes->next())
	{
		if ($last)
		{
			$acu_dist += DistKM($shapes->shape_pt_lat, $shapes->shape_pt_lon, $last[0], $last[1]);
			$shapes->addColumn("acu_dist", $acu_dist);
			if ($shapes->near_stop)
			{
				$stoptimes->find("stop_id",$shapes->near_stop);
				$stoptimes->addColumn("acu_dist",$acu_dist);
			}
		}
		else
			$shapes->addColumn("acu_dist", 0.0);
		$last = [$shapes->shape_pt_lat, $shapes->shape_pt_lon];
	}

	//Make sure first stop is distance 0
	$stoptimes->resetPosition();
	$stoptimes->next();
	$stoptimes->addColumn("acu_dist",0.0);


	error_log(" updating stoptimes with distances");
	//Now check any stops that dont have a distance, find the nearest shape
	while ($stoptimes->next())
	{
		if ($stoptimes->acu_dist === null)
		{
			//Find the nearest
			$shape = findNearestShape($stoptimes->stop_lat, $stoptimes->stop_lon, $shapes);
			if ($shape)
			{
				$shape["near_top"] = $stoptimes->stop_id;
				$stoptimes->acu_dist = $shape["acu_dist"];
			}
		}
	}

	error_log(" calculating tiomes on route");
	$retdata = array();
	$last_stop = null;
	$lastStopdist = 0;
	$time = (new DateTime($strToday . " " . $firststop["departure_time"],$timeZone))->getTimestamp();
	$shapes->resetPosition();
	while ($shape = $shapes->next())
	{
		if ($shapes->near_stop)
		{
			$stop = $stoptimes->find("stop_id", $shapes->near_stop);
			$time = (new DateTime($strToday . " " . $stop["departure_time"],$timeZone))->getTimestamp();
			$lastStopdist = $shapes->acu_dist;
			$last_stop = $stop;
		}
		else
		{
			//Calculate time
			if ($last_stop)
			{
				$nextstop = $stoptimes->next($last_stop["__idx__"]);
				if ($nextstop)
				{
					if ($nextstop["acu_dist"] && $last_stop["acu_dist"] !== null)
					{

						$t1ast = (new DateTime($strToday . " " .$last_stop["departure_time"],$timeZone))->getTimestamp();
						$tnext = (new DateTime($strToday . " " .$nextstop["departure_time"],$timeZone))->getTimestamp();
						$dist_delta = $nextstop["acu_dist"] - $last_stop["acu_dist"];
						$time_delta = $tnext - $t1ast;
						$perc = ($shapes->acu_dist - $last_stop["acu_dist"]) / $dist_delta;
						$time_delta = $time_delta * $perc;
						$t = (new DateTime())->setTimestamp(intval($t1ast+$time_delta));
						$time = $t->getTimestamp();
					}
				}
			}
		}
		$retdata[] = ["lat" => $shapes->shape_pt_lat, "lon" => $shapes->shape_pt_lon,"t" =>$time, "stop" => $shapes->near_stop];

	}

	$data["points"] = $retdata;
	$allstops = array();
	//Dump all stops
	$stoptimes->resetPosition();
	while ($stoptimes->next())
	{
		$time = (new DateTime($strToday . " " . $stoptimes->departure_time, $timeZone))->getTimestamp();
		$allstops[] = ["stop_id" => $stoptimes->stop_id, "name" =>  $stoptimes->stop_name, "time" => $stoptimes->departure_time, "t" => $time,"lat" => $stoptimes->stop_lat, "lon" => $stoptimes->stop_lon, "dist" => $stoptimes->acu_dist];
	}
	$data["stops"] = $allstops;

	$trips->first();
    $data["headsign"] = ["trip_id" => $trips->trip_id, "name" => $trips->trip_headsign];

	error_log(" ready to send json");

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
	case 'getrips':
		getTripsForRoute($req,$params);
		break;
	case 'timedshapedata':
		getTimedShapeData($req,$params);
		break;
	default:
		returnError($req,1000,"Invalid parameter");
		break;
	}
}
?>