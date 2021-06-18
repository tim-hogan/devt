<?php
require "classSignal.php";
$sig = new Signal(8888);
echo "About to listen\n";
$data = $sig->listen();
if ($data)
    echo "Received on lister {$data}\n";
else
    echo "Nothing received\n";
?>