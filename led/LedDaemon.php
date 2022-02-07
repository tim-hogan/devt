#!/usr/bin/env php
<?php
require dirname(__FILE__) . "/includes/classGPIOLED.php";

use devt\GPIOLed\Led;
use devt\GPIOLed\LedInterface;

$address = "127.0.0.1";
$port = 2207;
$iaction = 0;
$sleeptime = 0.001; // 1/1000 of a second
$usleeptime = $sleeptime * 1000000;
$duration_seconds = 0.0;
$mod = 0;
$start_time = 0.0;
$wait_time = 0.0;
$led = new Led(LedInterface::LED_TYPE_TRICOLOUR);


$sock = socket_create(AF_INET, SOCK_STREAM, 0);
socket_bind($sock, $address, $port) or die('Could not bind to address');
socket_listen($sock);
socket_set_nonblock($sock);

//Main loop
while (true)
{
    if ($newsock = socket_accept($sock)) {
        if (is_resource($newsock))
        {

            socket_set_nonblock($newsock);


            //Read form socket
            $data=null;
            $l = socket_recv($newsock,$data,1024,MSG_WAITALL);
            if ($l > 0)
            {
                $a = json_decode($data,true);
                if (isset($a["action"]) )
                {
                    $colour = "red";
                    $rate = 2.0;
                    $duration = 5000;
                    switch($a["action"])
                    {
                        case "off":
                            $iaction = 0;
                            $led->off();
                            break;
                        case "on":
                            $duration = 0;
                            $iaction = 0;
                            if (isset($a["colour"]))
                                $colour = $a["colour"];
                            if (isset($a["duration"]))
                            {
                                $duration = $a["duration"];
                                $duration_seconds = $duration / 1000.0;
                                $iaction = 1;
                                $start_time = microtime(true);
                            }
                            $led->off();
                            $led->on($colour);
                            break;
                        case "blink":
                            $iaction = 2;
                            if (isset($a["colour"]))
                                $colour = $a["colour"];
                            if (isset($a["rate"]))
                                $rate = $a["rate"];
                            if (isset($a["duration"]))
                                $duration = $a["duration"];
                            $ratio = 0.5;
                            if (isset($a["ratio"]))
                                $ratio = floatval($a["ratio"]);
                            $ratio = max(0,$ratio);
                            $ratio = min(1.0,$ratio);
                            $duration_seconds = $duration / 1000.0;
                            $start_time = microtime(true);
                            $last_action = $start_time;
                            $wait_time = 1.0/$rate;
                            $waitime_on = $wait_time * $ratio;
                            $waitime_off = $wait_time * (1-$ratio);
                            $led->off();
                            $mod = 0;
                            break;
                    }
                }
            }
            socket_close($newsock);
        }
    }

    if ($iaction)
    {
        if ($iaction == 1)
        {
            $atime=microtime(true);
            if (($atime-$start_time) > $duration_seconds)
            {
                $led->off();
                $iaction = 0;
            }
        }
        if ($iaction == 2)
        {
            $atime=microtime(true);
            //Check if we have finished
            $continue = $duration_seconds > 0 ? ($atime-$start_time) < $duration_seconds : true;
            if ($continue)
            {
                
                $wait_time = $mod ? $waitime_on : $waitime_off;
                if (($atime - $last_action) > $wait_time)
                {

                    if ($mod)
                    {
                        $led->off();
                        $mod = 0;
                    }
                    else
                    {
                        $led->on($colour);
                        $mod = 1;
                    }
                    $last_action = $atime;
                }
            }
            else
            {
                $iaction = 0;
                $led->off();
            }
        }
    }

    usleep($usleeptime);

}
?>