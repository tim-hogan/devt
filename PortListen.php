<?php
require "classSocket.php";
$options = getopt("p:");

if (!isset($options["p"]))
{
	echo "ERROR: You must speficy a port with -p <port number>\n";
	exit(1);
}

$port = intval($options["p"]);
echo "Creating listening socket on port {$port}\n";

$sock = new CSocket(CSOCKET_SERVER, $port);
if ($sock->accept())
{
	echo "We have an incomming connection\n";
	$line = $sock->readLine();
	echo "Read line {$line}\n";
	$sock->close();
}
?>