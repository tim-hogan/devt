<?php
class Point
{
    public $x;
    public $y;
    public $z;

    function __construct($x=null,$y=null,$z=null)
    {
        //Check if x is a point
        if (gettype($x) == "object" && get_class($x) == "Point")
        {
            $this->x = $x->x;
            $this->y = $x->y;
            $this->z = $x->z;
        }
        else
        {
            if ($x)
                $this->x = doubleval($x);
            if ($y)
                $this->y = doubleval($y);
            if ($z)
                $this->z = doubleval($z);
        }
    }

    public function add($x,$y=null,$z=null)
    {
        if (gettype($x) == "object" && get_class($x) == "Point")
        {
            $this->x += $x->x;
            $this->y += $x->y;
            $this->z += $x->z;
        }
        else
        {
            $this->x += $x;
            if($y)
                $this->y += $y;
            if($z)
                $this->y += $z;
        }
        return $this;
    }

    public function distance($p,$p2 = null)
    {
        if ($p2)
        {
            return sqrt(pow(($p2->x - $p->x),2) + pow(($p2->y - $p->y),2));
        }
        else
        {
            return sqrt(pow(($this->x - $p->x),2) + pow(($this->y - $p->y),2));
        }
    }
}

class Circle
{
    public $centre = null;
    public $radius = null;

    function __construct($centre=null,$radius=null)
    {
        //Check if x is a circle
        if (gettype($centre) == "object" && get_class($centre) == "Circle")
        {
            $this->centre = $centre->centre;
            $this->radius = $centre->radius;
        }
        elseif (gettype($centre) == "object" && get_class($centre) == "Point" && $radius)
        {
            $this->centre = $centre;
            $this->radius = $radius;
        }
    }

    /**
     * Summary of intersectCircle
     * @param Circle $c1 
     * @param Circle $c2 
     * @return null|Point[]
     */
    public static function intersectCircle($c1,$c2)
    {
        $x1 = $c1->centre->x;
        $x2 = $c2->centre->x;
        $y1 = $c1->centre->y;
        $y2 = $c2->centre->y;


        $R = $c1->centre->distance($c2->centre);
        if ($R > ($c1->radius + $c2->radius))
            return null;
        if ($R < abs($c1->radius - $c2->radius))
            return null;
        if ($R == 0)
            return null;
        $R2 = pow($R,2);
        $a = (pow($c1->radius,2) - pow($c2->radius,2) +  $R2) / (2*$R);
        $h = sqrt(pow($c1->radius,2) - pow($a,2));

        $P2x = $x1 + ($a *( $x2 - $x1 ) / $R);
        $P2y = $y1 + ($a * ( $y2 - $y1 ) / $R);

        $x3 = $P2x + ($h *( $y2 - $y1 ) / $R);
        $x4 = $P2x - ($h *( $y2 - $y1 ) / $R);

        $y3 = $P2y - ($h *( $x2 - $x1 ) / $R);
        $y4 = $P2y + ($h *( $x2 - $x1 ) / $R);

        $rslt=array();
        $rslt[0] = new Point($x3,$y3);
        $rslt[1] = new Point($x4,$y4);
        return $rslt;

    }
}

class Math
{
    /**
    @name intersectCircle
    @param $c1,$c2 classCircle
    */
    public static function intersectCircle($c1,$c2)
    {

    }
}
?>