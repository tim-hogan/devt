<?php

$f = fopen("temprecord.txt", "r");
$fw = fopen("temprecord.csv", "w");
while (($row = fgetcsv($f, 32000)) !== false)
{
if ($row[0])
    fwrite($fw, $row[0] . "," . $row[1] . "\r\n");
}
fclose($f);
fclose($fw);
?>