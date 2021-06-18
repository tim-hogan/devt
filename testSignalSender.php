<?php
require "classSignal.php";
$sig = new Signal(8888);
echo "About to trigger\n";
$sig->trigger("fred");
echo "About to trigger complete\n";
?>