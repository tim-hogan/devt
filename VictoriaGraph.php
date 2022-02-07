<?php
require "./classSVG.php";

$data = [
    ["name" => "Vitmain A", "unit" => "ug", "EAR" => 500, "RDI" => 700, "UL" => 3000, "days" => [["vic" => 11.50],["vic" => 32.85],["vic" => 4.75]] ],
    ["name" => "Calcium", "unit" => "mg", "EAR" => 1100, "RDI" => 1300, "UL" => 2500, "days" => [["vic" => 581.47],["vic" => 365.73],["vic" => 350.11]] ],
    ["name" => "Iodine", "unit" => "ug", "EAR" => 100, "RDI" => 150, "UL" => 1100, "days" => [["vic" => 93.19],["vic" => 32.33],["vic" => 10.50]] ],
    ["name" => "Niacin", "unit" => "mg", "EAR" => 11, "RDI" => 14, "UL" => 35, "days" => [["vic" => 9.84],["vic" => 10.02],["vic" => 10.19]] ],
    ["name" => "Zinc", "unit" => "ug", "EAR" => 6.5, "RDI" => 8.0, "UL" => 40, "days" => [["vic" => 5.84],["vic" => 5.81],["vic" => 6.32]] ],
    ["name" => "Thiamin", "unit" => "mg", "EAR" => 0.9, "RDI" => 1.1, "UL" => 0, "days" => [["vic" => 0.6],["vic" => 1.39],["vic" => 0.79]] ],
    ["name" => "Riboflavin", "unit" => "mg", "EAR" => 0.9, "RDI" => 1.1, "UL" => 0, "days" => [["vic" => 0.91],["vic" => 1.15],["vic" => 0.84]] ],
    ["name" => "Magnesium", "unit" => "mg", "EAR" => 265, "RDI" => 320, "UL" => 350, "days" => [["vic" => 339.19],["vic" => 276.16],["vic" => 340.17]] ],
    ["name" => "Iron", "unit" => "mg", "EAR" => 5.0, "RDI" => 8.0, "UL" => 45.0, "days" => [["vic" => 9.56],["vic" => 7.09],["vic" => 8.01]] ],
    ["name" => "Vitamin C", "unit" => "mg", "EAR" => 30.0, "RDI" => 45.0, "UL" => 1000, "days" => [["vic" => 178.87],["vic" => 104.46],["vic" => 80.9]] ]
];

$canvas_width = 510;
$canvas_height = 65;
$bar_offsetX = 5;
$bar_offsetY = 5;


$bar_width = 500;
$bar_height = 55;
$data_height = round($bar_height * 0.5);
$border_width = 2;

$C_RED = "#c40000";    //196,0,0
$C_GREEN = "#00b500";  //0,181,0
$C_MEAUVE = "#e8ded1"; //232,222,209
$C_YELLOW = "#FFD300"; //255,211,0
$C_BORDER = "#888888";

$cnt = 0;
foreach($data as $nutriant)
{

    $name = $nutriant["name"];
    $day = 1;

    //Calculate max
    $max = 0;
    foreach($nutriant["days"] as $d)
    {
        if (floatval($d["vic"]) > $max)
            $max = floatval($d["vic"]);
    }
    $ear = floatval($nutriant["EAR"]);
    $rdi = floatval($nutriant["RDI"]);
    $s1 = ($bar_width - ($border_width *2)) / $max;
    $scale = (($bar_width - ($border_width *2)) * 0.75) / $rdi;
    if ($s1 < $scale)
        $scale = $s1;

    foreach($nutriant["days"] as $d)
    {

        $svg = new devt\svg\svg($canvas_width,$canvas_height);

        //Create linear gradiant 1
        $svg->createLinGradiant("grad1","vertical",
            ["offset" => "0%","style" => "stop-color:rgb(196,0,0);stop-opacity:1"],
            ["offset" => "50%","style" => "stop-color:rgb(255,128,128);stop-opacity:1"],
            ["offset" => "100%","style" => "stop-color:rgb(196,0,0);stop-opacity:1"]
            );
        $svg->createLinGradiant("grad2","vertical",
            ["offset" => "0%","style" => "stop-color:rgb(0,181,0);stop-opacity:1"],
            ["offset" => "50%","style" => "stop-color:rgb(128,255,128);stop-opacity:1"],
            ["offset" => "100%","style" => "stop-color:rgb(0,181,0);stop-opacity:1"]
            );



        //Draw outside
        $svg->rect($bar_offsetX,$bar_offsetY,$bar_width,$bar_height,"#ffffff",$C_BORDER,$border_width,"1");


        $xoff = $bar_offsetX + $border_width;
        $yoff = $bar_offsetY + $border_width;

        $w1 = round($ear * $scale);
        $h1 = $bar_height-($border_width*2);
        $svg->rect($xoff,$yoff,$w1,$h1,$C_MEAUVE,$C_MEAUVE,$border_width,"1");

        $w2 = round($rdi * $scale);
        $svg->rect($xoff+$w1,$yoff,$w2-$w1,$h1,$C_YELLOW,$C_YELLOW,$border_width,"1");

        $colour = "url(#grad2)";
        $vic = floatval($d["vic"]);
        $w1 = round($vic * $scale);
        if ($vic < $ear)
            $colour = "url(#grad1)";

        echo "Darw vic as {$w1}/{$data_height} colour {$colour}\n";
        $y = $yoff + round(($bar_height-$data_height)/2);
        $svg->rect($xoff,$y,$w1,$data_height,$colour,$colour,$border_width,"1");

        //$svg->text(10,17,$name . " Day " . $day,'left',"#000000","10pt","Calibri");

        $strCnt = sprintf("%04d",$cnt);
        $svg->save("Image{$strCnt}.svg");

        $cnt++;
        $day++;
    }
}
