<?php
require "classSignal.php";
$sig = new Signal(8888);
echo "About to receive data\n";
echo $sig->receive();
echo "\n";
?>