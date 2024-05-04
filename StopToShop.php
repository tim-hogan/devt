<?php
require "./includes/classCollectionCSV.php";

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

$options = getopt("o:r:s:");

if (!isset($options["r"]) || !isset($options["s"]))
{
	echo "You must specify ionput files with -r (for retailers) and -s (for stops";
	exit(1);
}

$outFileName = "RetailerToClosestStop.csv";

$retailers =  new CSVCollection($options["r"]);
$stops = new CSVCollection($options["s"]);

//Now loop for each retailer to closest stop
$fout=fopen($outFileName,"w");
fwrite($fout,"Retailer Name,Retailer Type,Retailer ID,Stop ID,Stop Name,Distance (km)\r\n");

while ($retailer = $retailers->next())
{
    if (strlen($retailer["Latitude"]) > 0 && strlen($retailer["Longitude"]) > 0)
    {

        $mind = PHP_FLOAT_MAX;
        $stops->reset();
        while ($stop = $stops->next()) {
            try {

                $lat1 = floatval($retailer["Latitude"]);
                $lon1 = floatval($retailer["Longitude"]);
                $lat2 = floatval($stop["Latitude"]);
                $lon2 = floatval($stop["Longitude"]);
                $d = DistKM($lat1, $lon1, $lat2, $lon2);
            } catch (Exception $e) {
                var_dump($retailer);
                var_dump($stop);
            }

            if ($d < $mind) {
                $mind = $d;
                $savedstop = $stop;
            }
        }

        $str = "\"{$retailer["Name"]}\",{$retailer["Type"]},{$retailer["ID"]},{$savedstop["ID"]},\"{$savedstop["Name"]}\",{$mind}\r\n";
        fwrite($fout, $str);
    }
}
fclose($fout);


$outFileName = "StopToClosestRetailer.csv";
$retailers->reset();
$stops->reset();

//Now loop for each retailer to closest stop
$fout = fopen($outFileName, "w");
fwrite($fout, "Stop ID,Stop Name,Retailer Name,Retailer Type,Retailer ID,Distance (km)\r\n");

while ($stop = $stops->next())
{
    if (strlen($stop["Latitude"]) > 0 && strlen($stop["Longitude"]) > 0)
    {
        $mind = PHP_FLOAT_MAX;
        $retailers->reset();
        while ($retailer = $retailers->next())
        {
            if (strlen($retailer["Latitude"]) > 0 && strlen($retailer["Longitude"]) > 0)
            {
                try {

                    $lat1 = floatval($retailer["Latitude"]);
                    $lon1 = floatval($retailer["Longitude"]);
                    $lat2 = floatval($stop["Latitude"]);
                    $lon2 = floatval($stop["Longitude"]);
                    $d = DistKM($lat1, $lon1, $lat2, $lon2);
                } catch (Exception $e) {
                    var_dump($retailer);
                    var_dump($stop);
                }

                if ($d < $mind) {
                    $mind = $d;
                    $savedretailer = $retailer;
                }
            }
        }
        
        $str = "{$stop["ID"]},\"{$stop["Name"]}\",\"{$savedretailer["Name"]}\",{$savedretailer["Type"]},{$savedretailer["ID"]},{$mind}\r\n";
        fwrite($fout, $str);
    }
}
fclose($fout);

?>