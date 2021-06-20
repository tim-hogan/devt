<?php
require "classSignal.php";
$sig = new Signal(8888);
echo "About to queue\n";
$sig->queue("New data");
?>