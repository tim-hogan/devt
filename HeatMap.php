<?php

function var_error_log( $object=null ,$text='')
{
    ob_start();
    var_dump( $object );
    $contents = ob_get_contents();
    ob_end_clean();
    error_log( "{$text} {$contents}" );
}

function removeUnicodeString($s)
{
    if ( substr(bin2hex($s),0,6) == "efbbbf" )
        return substr($s,3);
    else
        return $s;
}


$schema = [
    [
        "heading" => "Christchuch",
        "file" => "Metro-Christchurch.csv",
        "centre" => ["lat" => -43.53203300434073,"lng" => 172.63058265193908],
        "zoom" => 12,
        "data" => null
    ],

    [
        "heading" => "Timaru",
        "file" => "",
        "centre" => ["lat" => -44.39035548404226, "lng" => 171.23726038590044],
        "zoom" => 13,
        "data" => null
    ]

];

$cnt = 0;
foreach($schema as $scheme)
{
    if ($scheme["file"])
    {
        $fields = array();
        $data = array();
        $f = fopen($scheme["file"],"r");
        if ($f)
        {
            while (($row = fgetcsv($f, 32000)) !== false)
            {
                $lat = 0;
                $lon = 0;
                $tag=0;
                $top=0;

                if (empty($fields))
                {
                    $fields = $row;
                    //var_error_log($fields,"CSV Fields");
                    continue;
                }

                foreach ($row as $k=>$v)
                {

                    $field = removeUnicodeString(trim($fields[$k]));
                    switch (strtoupper($field))
                    {
                        case "LAT":
                            $lat = floatval($v);
                            break;
                        case "LONG":
                            $lon = floatval($v);
                            break;
                        case "TAG":
                            $tag = floatval($v);
                            break;
                        case "TOP":
                            $top = floatval($v);
                            break;
                    }

                }

                $data[] = ["LAT" => $lat,"LON" => $lon, "TAG" => $tag, "TOP" => $top];
            }
            $schema[$cnt] ["data"] = $data;
        }
    }
    $cnt++;
}

?>
<!DOCTYPE HTML>
<html>
<head>
    <meta name="viewport" content="width=device-width" />
    <meta name="viewport" content="initial-scale=1.0" />
    <title>MAP</title>
    <style>
        body {font-family: Arial, Helvetica, sans-serif; font-size: 10pt; margin: 0;height: 100%;background-color: black;}
        #container {}
        #left {display: inline-block; width: 200px;vertical-align: top;border: solid 1px #aaa;margin: 2px;margin-top: 6px;padding: 4px;background-color: #eeeeee;}
        #right {display: inline-block;}
        #gmap {}
        #options .hdlabel1 {display: block;font-size: 9pt;margin-bottom: 0;text-align: center;}
        #options fieldset {margin-bottom: 20px;}
        #options legend {font-size: 9pt;}
        #options label {font-size: 9pt;}
        #options input[type="range"] {display: block;margin: auto;}
        #options select {font-size: 9pt; margin: auto;display: block;margin-bottom: 10px;}
    </style>
    <script>
        var heatmap = null;
        var test = null;
        var g_set = 0;
        var g_circles = [];
        var g_maptype = "maptypeHeat";
        var g_datatype = "datatypeTag";
        var g_criclescale = 2000;
        var g_centres = [
            <?php
            foreach($schema as $scheme)
            {
                echo "[{$scheme['centre'] ['lat']}, {$scheme['centre'] ['lng']}],";
            }
            ?>
        ];

        function getRadius() {
            let r = parseInt(document.getElementById("radius").value);
            g_criclescale = (100 - parseInt(r)) * 40;
            return r;
        }
        function setRadius(n) {
            if (heatmap) {
                heatmap.set("radius", parseInt(n.value));
            }
            
            if (g_maptype == "maptypeCirc")
                displayMap();
        }

        function setRadius2(n) {
            if (heatmap) {
                heatmap.set("radius", parseInt(n.value));
            }
            g_criclescale = (100-parseInt(n.value)) * 80;
        }

        function resetAll() {
            if (heatmap) {
                radius = heatmap.get("radius");
                heatmap.setMap(null);
                heatmap = null;
            }
            if (g_circles.length > 0) {
                for (let c of g_circles) {
                    c.setMap(null);
                }
                g_circles = [];
            }
        }

        function createHeatMap(datatype) {
            let radius = getRadius();
            resetAll();
            if (datatype == "datatypeTag") {
                heatmap = new google.maps.visualization.HeatmapLayer({
                    data: getPointsTag(g_set),
                    map: map,
                });
            }
            if (datatype == "datatypeTop") {
                heatmap = new google.maps.visualization.HeatmapLayer({
                    data: getPointsTop(g_set),
                    map: map,
                });
            }
            if (heatmap) {
                heatmap.set("dissipating", true);
                heatmap.set("radius", radius);
            }
        }

        function createCircleMap(datatype) {
            resetAll();
            getRadius();
            let points = null;
            if (datatype == "datatypeTag") {
                points = getPointsTag(g_set);
            }
            if (datatype == "datatypeTop") {
                points = getPointsTop(g_set);
            }
            if (points) {
                for (let pt of points) {
                    if (pt.weight > 0) {
                        let r = pt.weight / g_criclescale;
                        //let r = Math.log(pt.weight) * 10;
                        let cen = pt.location;
                        let cir = new google.maps.Circle(
                            {
                                strokeColor: "#00FF00",
                                strokeOpacity: 0.8,
                                strokeWeight: 2,
                                fillColor: "#00FF00",
                                fillOpacity: 0.35,
                                map,
                                center: cen,
                                radius: r
                            }
                        );
                        g_circles.push(cir);
                    }
                }
            }
        }

        function setDataSet(n) {
            g_set = n.value;
            map.setCenter(new google.maps.LatLng(g_centres[g_set] [0], g_centres[g_set] [1]));
            let idTag = document.getElementById("datatypeTag");
            let idTop = document.getElementById("datatypeTop");
            if (idTag.checked)
                setDataType(idTag);
            else
                setDataType(idTop);
        }

        function displayMap() {
            switch (g_maptype) {
                case "maptypeHeat":
                     createHeatMap(g_datatype);
                    break;
                case "maptypeCirc":
                    createCircleMap(g_datatype);
                    break;
            }
        }

        function setDataType(n) {
            g_datatype = n.id;
            displayMap();
        }

        function setMapType(n) {
            g_maptype = n.id;
            displayMap();
        }

        function performScaling() {
            var dleft = document.getElementById("left");
            var _gmap = document.getElementById("gmap");
            _gmap.style.width = (document.body.clientWidth - (dleft.clientWidth + 48)) + "px";
            _gmap.style.height = window.innerHeight + "px";
        }

    </script>
</head>
<body>
    <div id="container">
        <div id="left">
            <div id="options">
                <label for="dataset" class="hdlabel1">DATA SET</label>
                <select id="dataset" onchange="setDataSet(this)">
                    <?php
                    $idx = 0;
                    foreach($schema as $scheme)
                    {
                        echo "<option value='{$idx}'>{$scheme["heading"]}</option>";
                        $idx++;
                    }
                    ?>
                </select>
                <fieldset id="fs1">
                    <legend>DATA TYPE</legend>
                    <input id ="datatypeTag" type="radio" name="datatype" onchange="setDataType(this)" checked/><label for="datatypeTag">TAG ON</label>
                    <input id= "datatypeTop" type="radio" name="datatype" onchange="setDataType(this)" /><label for="datatypeTop">TOP UP</label>
                </fieldset>
                <fieldset id="fs2">
                    <legend>MAP TYPE</legend>
                    <input id="maptypeHeat" type="radio" name="maptype" onchange="setMapType(this)" checked/ /><label for="maptypeHeat">HEAT</label>
                    <input id="maptypeCirc" type="radio" name="maptype" onchange="setMapType(this)" /><label for="maptypeCirc">CIRCLE</label>
                </fieldset>
                <label for="radius" class="hdlabel1">SCALING</label>
                <input id="radius" type="range" min="1" max="100" value="50" class="slider" onchange="setRadius(this)" oninput="setRadius2(this)"/>
            </div>
        </div>
        <div id="right">
            <div id="gmap">
            </div>
        </div>
    </div>
</body>
<script>
    function initMap() {
        console.log("initMap");

        performScaling();
        window.addEventListener("resize", performScaling);

        map = new google.maps.Map(document.getElementById('gmap'), {
            center: {lat: -43.53203300434073,lng: 172.63058265193908},
            zoom: 12,
            disableDefaultUI: true,
            mapTypeId: 'terrain',
                styles: [
      //{ elementType: "geometry", stylers: [{ color: "#242f3e" }] },
      { elementType: "geometry", stylers: [{ color: "#12181f" }] },
      { elementType: "labels.text.stroke", stylers: [{ color: "#242f3e" }] },
      { elementType: "labels.text.fill", stylers: [{ color: "#746855" }] },
      {
        featureType: "administrative.locality",
        elementType: "labels.text.fill",
        stylers: [{ color: "#6a4a32" }], //stylers: [{ color: "#d59563" }],
      },
      {
        featureType: "poi",
        //elementType: "labels.text.fill",
        //stylers: [{ color: "#d59563" }],
        stylers: [{ visibility: "off" }],
      },
      {
        featureType: "poi.park",
        elementType: "geometry",
        stylers: [{ color: "#263c3f" }],
      },
      {
        featureType: "poi.park",
        elementType: "labels.text.fill",
        stylers: [{ color: "#6b9a76" }], // stylers: [{ color: "#6b9a76" }],
      },
      {
        featureType: "road",
        elementType: "geometry",
        stylers: [{ color: "#38414e" }],
      },
      {
        featureType: "road",
        elementType: "geometry.stroke",
        stylers: [{ color: "#212a37" }],
      },
      {
        featureType: "road",
        elementType: "labels.text.fill",
        stylers: [{ color: "#9ca5b3" }],
      },
      {
        featureType: "road.highway",
        elementType: "geometry",
        stylers: [{ color: "#746855" }],
      },
      {
        featureType: "road.highway",
        elementType: "geometry.stroke",
        stylers: [{ color: "#1f2835" }],
      },
      {
        featureType: "road.highway",
        elementType: "labels.text.fill",
        stylers: [{ color: "#f3d19c" }],
      },
      {
        featureType: "transit",
        elementType: "geometry",
        stylers: [{ color: "#2f3948" }],
      },
      {
        featureType: "transit.station",
        elementType: "labels.text.fill",
        stylers: [{ color: "#d59563" }],
      },
      {
        featureType: "water",
        elementType: "geometry",
        stylers: [{ color: "#0c131e" }], //stylers: [{ color: "#17263c" }],
      },
      {
        featureType: "water",
        elementType: "labels.text.fill",
        stylers: [{ color: "#515c6d" }],
      },
      {
        featureType: "water",
        elementType: "labels.text.stroke",
        stylers: [{ color: "#17263c" }],
      },
    ],
        });

        displayMap();
    }

    function getPointsTag(set) {
        let gTags = [
                    <?php
            foreach($schema as $scheme)
            {
                echo "[";
                foreach($scheme["data"] as $d)
                {
                    echo "{";
                    echo "location: new google.maps.LatLng({$d['LAT']},{$d['LON']}), weight: {$d['TAG']}";
                    echo "},";
                }
                echo "],";
            }
                ?>
        ];
        return gTags[set];
    }

    function getPointsTop(set) {
        let gTop = [
                <?php
            foreach($schema as $scheme)
            {
                echo "[";
                foreach($scheme["data"] as $d)
                {
                    echo "{";
                    echo "location: new google.maps.LatLng({$d['LAT']},{$d['LON']}), weight: {$d['TOP']}";
                    echo "},";
                }
                echo "],";
            }
            ?>
        ];
        return gTop[set];
    }

</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAVvuSORY6mhfPAuEeFWSC1bLgXkmmxqCQ&libraries=visualization&callback=initMap" async defer></script>
</html>
