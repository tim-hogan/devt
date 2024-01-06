<?php
require './includes/classSignal.php';
require_once "./includes/classScanDB.php";

function var_error_log( $object=null , $text='')
{
	ob_start();                    // start buffer capture
	var_dump( $object );           // dump the values
	$contents = ob_get_contents(); // put the buffer into a variable
	ob_end_clean();                // end capture
	error_log( "{$text} {$contents}" );        // log contents of the result of var_dump( $object )
}

function reply($msg)
{
	$json = json_encode($msg);
	echo "data: " . $json ."\n\n";
	while (ob_get_level() > 0)
	{
		if (! ob_end_flush() )
		{
			error_log("ob_end_flush failed");
			return false;
		}
	}
	flush();
	return true;
}

function replyPoll()
{
	//Send a poll message reply
	reply(["type" => "poll"]);
}

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');


set_time_limit(0);
ignore_user_abort(false);

$sig = new Signal(5489);

while (true)
{
	error_log("Worker.php enter listen source id: {$src->idsource}");

	$data = $sig->listen();

	error_log("Worker.php woke up on event data {$data} source id: {$src->idsource}");

	if ($data)
	{
		$signal_value = intval($data);
		if ($signal_value > 0)
		{
			//Do something based on value
		}
		else
		{
			if ($signal_value < 0)
				replyPoll();
		}
	}

	if(connection_status() != CONNECTION_NORMAL)
	{
		error_log("Worker listener connection aborted for source id {$src->idsource}");
		break;
	}
}
?>