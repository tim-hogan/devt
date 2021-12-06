#!/usr/bin/env php
<?php
use devt\threed\Scene3D;
use devt\threed\Vector3D;
use devt\bitmap\Bitmap;


require_once "./includes/class3D.php";
require_once "./includes/classBitmap.php";

define("RED","\e[31m");
define("NC","\e[0m");

function useage()
{
    echo "BitmapUtil -- Usage\n";
    echo " BitmapUtil -h\n";
    echo "   For help\n";
    echo " BitmapUtil [command] [options] [filelist]\n";
    echo " Command\n";
    echo "   merge -- Merges a set of bitmap files\n";
    echo "       merge filename1 filename2 -- Merges filename2 into filename1 ignores black\n";
    exit();
}

//The command is $argv[1]
if (! isset($argv[1]))
{
    echo RED . "No command given" .NC . "\n";
    useage();
}

$outFileName = "BitmapUtilOut.bmp";


switch (strtoupper($argv[1]))
{
    case "MERGE":
        if ($argc > 3)
        {
            Bitmap::mergeFiles("Full.bmp",$argv[2],$argv[3]);
        }
        break;
    default:
        echo RED . "Invalid command" . NC . "\n";
        exit(1);
}


echo "Command Complete\n";
?>