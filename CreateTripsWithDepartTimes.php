<?php

function removeUnicodeString($s)
{
    if (substr(bin2hex($s), 0, 6) == "efbbbf")
        return substr($s, 3);
    else
        return $s;
}

function searchDepartTimeForTrip($filename, $trip_id)
{
    $idxTripId = 0;
    $idxSeq = 0;
    $idxDepTime = 0;
    $f = fopen($filename, "r");
    if ($f) {
        while (($row = fgetcsv($f, 32000)) !== false) {
            if (empty($keys)) {
                $keys = $row;
                $keys[0] = removeUnicodeString($keys[0]);
                $idxTripId = array_search("trip_id", $keys);
                $idxSeq = array_search("stop_sequence", $keys);
                $idxDepTime = array_search("departure_time", $keys);
                continue;
            }
            if ($row[$idxTripId] == $trip_id && $row[$idxSeq] == 1)
                return $row[$idxDepTime];
        }
    }
    return "00:00:00";
    fclose($f);
}




$f1 = fopen("./data/trips.txt", "r");
if ($f1)
{
    $keys = array();
    $data = array();
    while (($row = fgetcsv($f1, 32000)) !== false)
    {
        if (empty($keys))
        {
            $keys = $row;
            $keys[0] = removeUnicodeString($keys[0]);
            $keys[] = "departure_time";
            continue;
        }
        $data[] = $row;
    }

    $idxTripId = array_search("trip_id", $keys);
    $idx_dep = array_search("departure_time", $keys);

    for($idx = 0; $idx < count($data);$idx++)
    {
        $row = $data[$idx];
        $dep_time = searchDepartTimeForTrip("./data/stop_times.txt", $row[$idxTripId]);
        $data[$idx] [$idx_dep] = $dep_time;

        if ($idx % 100 == 0)
            echo "Completed {$idx}\n";
    }

    echo "Saving file";
    $fw = fopen("./data/trips2.txt","w");
    $str = "";
    foreach($keys as $k)
        $str .= "{$k},";
    $str = trim($str,",");
    $str .= "\r\n";
    fwrite($fw,$str);

    foreach($data as $d)
    {
        $str = "";
        foreach($d as $r)
            $str .= "{$r},";
        $str = trim($str,",");
        $str .= "\r\n";
        fwrite($fw,$str);
    }


    fclose($fw);

}
?>