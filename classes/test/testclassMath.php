<?php
include "classMath.php";

$c1 = new Circle(new Point(0.8,-1.3),1);
$c2 = new Circle(new Point(1.3,-0.2),1);

$pt = Circle::intersectCircle($c1,$c2);

$pt = null;
$pinlength = 0.5;

while (!$pt)
{
    $pinlength += 0.01;
    $c1 = new Circle(new Point(0.8,-1.3),$pinlength);
    $c2 = new Circle(new Point(1.3,-0.2),$pinlength);
    $pt = Circle::intersectCircle($c1,$c2);
}

//Now binomial it
$p1 = $pinlength - 0.01;
$p2 = $pinlength;
$high = true;

while (abs($p2-$p1) > 0.00000001)
{
    $d =  ($p2 - $p1) /2;
    $pinlength = $p1 + $d;
    $c1 = new Circle(new Point(0.8,-1.3),$pinlength);
    $c2 = new Circle(new Point(1.3,-0.2),$pinlength);
    $pt = Circle::intersectCircle($c1,$c2);
    if($pt)
        $p2 = $pinlength;
    else
        $p1 = $pinlength;
}
if (!$pt)
{
    $c1 = new Circle(new Point(0.8,-1.3),$p2);
    $c2 = new Circle(new Point(1.3,-0.2),$p2);
    $pt = Circle::intersectCircle($c1,$c2);
    $pinlength = $p2;
}

//echo "Minimum pin length = {$pinlength}\n";
//echo "Intercept = {$pt[0]->x},{$pt[0]->y}  and {$pt[1]->x},{$pt[1]->y}\n";
//Now get interecpt


$l1 = 1.4;
$l2 = 2.6;
$c1 = new Circle(new Point(1,1),$l1);
$c2 = new Circle(new Point(2.121320344,	-1.414213562),$l2);
$pt = Circle::intersectCircle($c1,$c2);
if ($pt)

    echo "Intercept = {$pt[0]->x},{$pt[0]->y}  and {$pt[1]->x},{$pt[1]->y}\n";
else
    echo "No Intercept\n";



?>