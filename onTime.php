<?php

$routes = array();
$routes["fields"] = array();
$routes["data"] = array();

function var_error_log($object = null, $text = '')
{
	ob_start();
	var_dump($object);
	$contents = ob_get_contents();
	ob_end_clean();
	error_log("{$text} {$contents}");
}

function removeUnicodeString($s)
{
	if (substr(bin2hex($s), 0, 6) == "efbbbf")
		return substr($s, 3);
	else
		return $s;
}

function loadRoutes($filename,&$routes)
{
	$fields = array();
	$f = fopen($filename, "r");
	if ($f)
	{
		while (($row = fgetcsv($f, 32000)) !== false)
		{
			if (empty($fields) )
			{
				$fields = $row;
				continue;
			}
			$routes["data"][] = $row;
		}
	}
	foreach($fields as $f)
	{
		$routes["fields"][] = removeUnicodeString($f);
	}
}

loadRoutes("./data/routes.txt", $routes);
foreach($routes["fields"] as $f)
{
	error_log(removeUnicodeString($f));
}

?>
<!DOCTYPE HTML>
<html>
<head>
	<meta name="viewport" content="width=device-width" />
	<meta name="viewport" content="initial-scale=1.0" />
	<title>ON TIME</title>
	<script type="text/javascript" src="js/apiClass2.js"></script>
	<script>
		var ot = {
			ge: function (t) {
				return document.getElementById(t);
			},
			ce: function (t) {
				return document.createElement(t);
			},
			cea: function (t, p) {
				var e = ot.ce(t);
				p.appendChild(e);
				return e;
			},
			keyOf: function (v, a) {
				for (let i = 0; i < a.length; i++) {
					if (a[i] == v)
						return i;
				}
				return -1;
			},
			rc: function (n) {
				while (n.firstChild) {
					n.removeChild(n.firstChild);
				}

			},
			pad: function (v, l) {
				var s = v + "";
				while (s.length < l) s = "0" + s;
				return s;
			},
		}
		var g_apihost = "devt.nz"
		var g_api = new apiJSON(g_apihost, 'onTimeApi.php?r=', '1234567890123456', true, { getrips: "retgetrips", timedshapedata: "rettimedshapedata" });
		var g_cnt = 0;
		var g_trip = null;
		var g_watch = null;
		var g_timer = null;
		var g_nearesetStop = null;
		var g_nearestStopDistance = null;
		var g_nearesetPoint = null;
		var g_nearesetPointDist = null;
		var g_Interval = 1000;
		var g_headsign = { route: "", trip_id: "", trip_name: ""};


		function getrips(route_id) {
			let params = {};
			params.route_id = route_id;
			g_api.queueReq("POST", "getrips", params);
		}

		function retgetrips(d) {
			console.log("Reply from gettrips");
			ot.ge("divtrip").style.display = "block";
			let tsel = ot.ge("trip");
			ot.rc(tsel);
			let o = ot.cea("option", tsel);
			o.value = 0;
			o.innerHTML = "[SELECT TRIP]";
			let kId = ot.keyOf("trip_id", d.keys);
			let kSn = ot.keyOf("trip_headsign", d.keys);
			let kT = ot.keyOf("departure_time", d.keys);
			let kTimestamp = ot.keyOf("timestamp", d.keys);
			for (let trip of d.trips) {
				o = ot.cea("option", tsel);
				o.value = trip[kId];
				o.className = "current";
				o.innerHTML = trip[kT].substr(0, 5) + " " + trip[kId] + " " + trip[kSn];
				if (trip[kTimestamp] < ((Date.now() / 1000.0) - 300)) {
					o.className = "past";
                }
					
			}
			g_headsign.route = d.headsign.route;
		}

		function getTimeShapeData(tripid) {
			let params = {};
			params.trip_id = tripid;
			g_api.queueReq("POST", "timedshapedata", params);
		}

		function rettimedshapedata(d) {
			console.log("Reply from timedShapeData");
			g_trip = d;
			//Mark all stops as not visited
			let stops = g_trip.stops;
			for (let s of stops) {
				s.visited = false;
			}
		    g_headsign.trip_id = d.headsign.trip_id;
		    g_headsign.trip_name = d.headsign.name;

			buildHeadSign();
			startTrack();
		}

		function routeChange(n) {
			console.log("Route change");
			getrips(n.value);
		}

		function tripChange(n) {
			console.log("Trip change");
			getTimeShapeData(n.value);
		}

		function distKm(lat1, lon1, lat2, lon2) {
			let toRadians = (3.14159265358979 / 180.0);
			let latFrom = lat1 * toRadians;
			let longFrom = lon1 * toRadians;
			let latTo = lat2 * toRadians;
			let longTo = lon2 * toRadians;
			let theta = Math.sin(latFrom) * Math.sin(latTo) + (Math.cos(latFrom) * Math.cos(latTo) * Math.cos(longFrom - longTo));
			return (6378.15 * Math.acos(theta));
		}

		function buildHeadSign() {
			let h = ot.ge("headsign");
			ot.rc(h);
			let p = ot.cea("p", h);
			p.innerHTML = g_headsign.route + " [" + g_headsign.trip_id +"]";
			p = ot.cea("p", h);
			p.innerHTML = g_headsign.trip_name;
		}

		function timeout() {
			if (g_nearesetStop) {
				//What is closer, a point or a stop
				let diff = 0;
				if (g_nearestStopDistance < g_nearesetPointDist)
					diff = (Date.now() / 1000) - g_nearesetStop.t;
				else
					diff = (Date.now() / 1000) - g_nearesetPoint.t;

				//Scale the diff
				if (Math.abs(diff) >= 10)
					logdiff = (Math.log10(Math.abs(diff)) - 1.0) * 70;
				else
					logdiff = 0.0;

				if (logdiff > 160.0)
					logdiff = 160;

				let graph = ot.ge("graph");
				ot.rc(graph);
				if (logdiff > 0.0) {
					let b = ot.cea("div", graph);
					if (diff < 0) {
						b.style.backgroundColor = "#ff0000";
						b.style.position = "relative";
						b.style.width = Math.floor(logdiff) + "px";
						b.style.height = "12px";
						b.style.left = "160px";
					} else {
						let b = ot.cea("div", graph);
						b.style.backgroundColor = "#00aa00";
						if (diff > 120.0)
							b.style.backgroundColor = "#e78012";
						if (diff > 1200.0)
							b.style.backgroundColor = "#e74512";

						b.style.position = "relative";
						b.style.width = Math.floor(logdiff) + "px";
						b.style.height = "12px";
						b.style.left = (160 - Math.floor(logdiff)) +  "px";
					}
				}

				//Are we ahead or behind.
				let ah = ot.ge("adherence");
				ot.rc(ah);
				if (diff < 0) {
					let p2 = ot.cea("p", ah);
					p2.innerHTML = "WAIT";
					p2.className = "ahead";
					ah.className = "ahead"
				}
				else
					ah.className = "";
				let p1 = ot.cea("p", ah);
				let df = Math.abs(diff);
				min = Math.floor(df / 60);
				sec = Math.floor(df % 60);
				if (diff > 90 || diff < 0)
					p1.innerHTML = ot.pad(min, 2) + ":" + ot.pad(sec, 2);
				if (diff < 0) {
					p1.className = "ahead";
					if (diff > -60 && g_Interval > 1000) {
						g_Interval = 1000;
						setTimer(g_Interval);
					}
					if (diff <= -60 && g_Interval <= 1000) {
						g_Interval = 5000;
						setTimer(g_Interval);
					}
				}
				else {
					if (g_Interval <= 1000) {
						g_Interval = 5000;
						setTimer(g_Interval);
                    }
                }
			}
		}

		function setTimer(i) {
			if (g_timer) {
				clearInterval(g_timer);
				g_timer = null;
			}
			g_timer = setInterval(timeout, i);
		}

		function setMode(mode) {
			if (mode == "running") {
				ot.ge("form").style.display = "none";
				ot.ge("track").style.display = "block";
			}
			if (mode == "form") {
				ot.ge("form").style.display = "block";
				ot.ge("track").style.display = "none";
			}
		}

		function startTrack() {
			//We need to start a timer
			setMode("running");
			setTimer(g_Interval);
			if (g_watch) {
				navigator.geolocation.clearWatch(g_watch);
				g_watch = null;
			}
			if (navigator.geolocation) {
				g_watch = navigator.geolocation.watchPosition(showPosition, error, {enableHighAccuracy: true,timeout: 1000,maximumAge: 0});
			} else {
				let x = ot.ge("status");
				o.innerHTML = "This browser does not support the onTime app.";
			}
		}

		function EndTrip(n) {
			g_nearesetStop = null;
			g_nearestStopDistance = null;
			g_nearesetPoint = null;
			g_nearesetPointDist = null;

			let graph = ot.ge("graph");
			ot.rc(graph);

			let ah = ot.ge("adherence");
			ah.className = "";
			ot.rc(ah);

			ot.ge("divtrip").style.display = "none";
			if (g_timer) {
				clearInterval(g_timer);
				g_timer = null;
			}
			if (g_watch) {
				navigator.geolocation.clearWatch(g_watch);
				g_watch = null;
			}
			ot.rc(ot.ge("trip"));
			let selroute = ot.ge("route");
			let l = selroute.getElementsByTagName("option");
			if (l.length > 0)
				l[0].selected = true;
			if (g_trip) {
				g_trip = null;
			}
			setMode("form");
		}

		function showPosition(position) {
			let s1 = ot.ge("status1");
			let s2 = ot.ge("status2");
			let divstops = ot.ge("divstops");

			g_cnt++

			//Update counter
			ot.ge("cnt").innerHTML = g_cnt;

			//find nearest stop
			let points = g_trip.points;
			let stops = g_trip.stops;
			let minD = 1.7976931348623158e+308;
			let saveStop = null;
			for (let s of stops) {
				let d = distKm(position.coords.latitude, position.coords.longitude, s.lat, s.lon);
				if (d < minD) {
					minD = d;
					saveStop = s;
				}
			}
			g_nearesetStop = saveStop;
			g_nearestStopDistance = minD;

			//Find nearest shape point
			minD = 1.7976931348623158e+308;
			for (let p of points) {
				let d = distKm(position.coords.latitude, position.coords.longitude, p.lat, p.lon);
				if (d < minD) {
					minD = d;
					savePoint = p;
				}
			}

			g_nearesetPoint = savePoint;
			g_nearesetPointDist = minD;

			s1.innerHTML = "Count: " + g_cnt + "<br/>Lat/Lon " +  position.coords.latitude + "/" + position.coords.longitude
			//Neareest stop is saveStop at distance in km minD;
			s2.innerHTML = "Nearest stop " + saveStop.stop_id + " " + saveStop.name + " dsiatnce " + Math.round(minD * 1000) / 1000 + "km";


			//Create a list of stops
			let idx = 0;
			let start_idx = 0;
			let end_idx = 0;
			for (s of stops) {
				if (g_nearesetStop && s.stop_id == g_nearesetStop.stop_id) {
					stop_idx = idx;
					start_idx = idx - 4;
					end_idx = idx + 4;
					if (start_idx < 0)
						start_idx = 0;
				}
				idx++;
			}

			idx = 0;
			ot.rc(divstops);
			for (s of stops) {
				if (idx >= start_idx && idx <= end_idx) {
					let p = ot.cea("p", divstops);
					p.innerHTML = s.stop_id + " " + s.name;
					if (s.visited)
						p.className = 'visited';
					if (s.stop_id == saveStop.stop_id) {
						p.innerHTML += (" " + s.time);
						p.className = "nearest";
						if (g_nearestStopDistance < 0.05) {
							p.className = "onstop";
							s.visited = true;
						}
					}
				}
				idx++;
			}
		}

		function error() {

		}
	</script>
	<style>
		body {margin: 0;font-family: Arial, Helvetica, sans-serif;font-size: 10pt;}
		#container {width: 372px; margin: auto;border: solid 1px #888;border-radius: 8px;padding: 4px;}
		#heading h1 {margin: 0.25em; font-size: 2.2em; text-align: center;}
		#headsign p {margin: 0.5em;text-align: center;font-size: 1.5em;color: #2b9f75;}
		#graph {margin: auto;width: 320px; height: 12px; border:inset 1px;}
		#divtrip {display: none;}
		#divstops p {color: #555; margin: 0;}
		#divstops p.visited {color: #aaa;}
		#divstops p.nearest {color: #777; font-weight: bold;}
		#divstops p.onstop {color: #6060ff}
		#debug {margin: 10px; padding: 8px;border: solid 1px #888;display: none;}
		#debug h2 {margin-top: 0;font-size: 12pt; color: #7777c1; text-align: center;}
		#track {display: none;}
		#adherence {width: 10em;height: 72px;margin: auto;padding: 6px; margin-bottom: 8px;}
		div.ahead {background-color: darkred;}
		#adherence p {text-align: center;font-size: 2em;margin-top: 4px; margin-bottom: 4px;}
		#adherence p.ahead {background-color: darkred; color: white;}
		select {padding: 4px; font-size: 12pt;margin-bottom: 2em;}
		#divstops {margin-bottom: 16px;}
		div.divselect {text-align: center;}
		#cnt {font-size: 8pt;display: inline-block;}
		#divtrip select{}
		#divtrip option {}
		.past {-webkit-appearance: none; color: #888;}
		.current {-webkit-appearance: none;color: #88F;}
	</style>
</head>
<body>
	<div id="container">
		<div id="heading">
			<h1>onTime</h1>
		</div>
		<div id="main">
			<div id="form">
				<form>
					<div class="divselect">
					<select id="route" name="route" onchange="routeChange(this)">
						<option value='0'>[SELECT ROUTE]</option>
						<?php
						$keyId = array_search("route_id", $routes["fields"]);
						$keyName = array_search("route_short_name", $routes["fields"]);
						foreach($routes["data"] as $row)
						{
							$n = htmlspecialchars($row[$keyName]);
							$id = htmlspecialchars($row[$keyId]);
							echo "<option value='{$id}'>{$n}</option>";
						}
						?>
					</select>
					</div>
					<div id="divtrip" class="divselect">
						<select id="trip" name="trip" onchange="tripChange(this)">
						</select>
					</div>
				</form>
			</div>
			<div id='track'>
				<div id="headsign">
				</div>
				<div id="graph"></div>
				<div id="debug">
					<h2>DEBUG AREA</h2>
					<p id="status1"></p>
					<p id="status2"></p>
				</div>
				<div id="adherence"></div>
				<div id="divstops"></div>
				<div id="end">
					<button onclick="EndTrip(this)">FINISH</button>
					<p id="cnt"></p>
				</div>
			</div>
		</div>
	</div>
</body>
</html>