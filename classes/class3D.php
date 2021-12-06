<?php
namespace devt\threed;
define ("DEBUG", true);
require_once "classBitmap.php";

use devt\bitmap\Bitmap;

function hitsort($a,$b)
{
    return ($a['t'] ['d'] > $b['t'] ['d'] );
}

class Vector3D
{
    public $x;
    public $y;
    public $z;

    function __construct($x,$y=0,$z=0)
    {
        if (gettype($x) == "object")
        {
            $this->x = $x->x;
            $this->y = $x->y;
            $this->z = $x->z;
        }
        else
        {
            $this->x = $x;
            $this->y = $y;
            $this->z = $z;
        }
    }

    public function is($v)
    {
        if ($v->x == $this->x && $v->y == $this->y && $v->z == $this->z)
            return true;
        return false;
    }

    public function nAdd ($v)
    {
        return new Vector3D($this->x + $v->x, $this->y + $v->y, $this->z + $v->z);
    }

    public function nSubtract($v)
    {
        return new Vector3D($this->x - $v->x, $this->y - $v->y, $this->z - $v->z);
    }

    public function nScale($d)
    {
        return new Vector3D($this->x * $d,$this->y * $d ,$this->z * $d);
    }

    public function length()
    {
        return sqrt($this->x * $this->x + $this->y * $this->y + $this->z * $this->z);
    }

    public function lengthTo($v)
    {
        $l = $v->nSubtract($this);
        return $l->length();
    }

    public function nInverse()
    {
        return new Vector3D(-$this->x,-$this->y,-$this->z);
    }

    public function dot($v)
    {
        return $this->x * $v->x  + $this->y * $v->y + $this->z * $v->z;
    }

    public function nCross($v)
    {
        return new Vector3D($this->y * $v->z - $this->z * $v->y,$this->z * $v->x - $this->x * $v->z,$this->x * $v->y - $this->y * $v->x);
    }

    public function nRotateY($theta)
    {
        return new Vector3D($this->x * cos($theta) - $this->z * sin($theta), $this->y, $this->x * sin($theta) + $this->z * cos($theta));
    }

    public function sNormalize()
    {
        $l = $this->length();
        if ($l > 0)
        {
            $this->x = $this->x / $l;
            $this->y = $this->y / $l;
            $this->z = $this->z / $l;
        }
    }

    public function nDirTo($to)
    {
        $o = $to->nSubtract($this);
        $o->sNormalize();
        return $o;
    }

    public function sSwapYZ()
    {
        $t = $this->y;
        $this->y = $this->z;
        $this->z = $t;
    }

    public function toArray()
    {
        return [$this->x,$this->y,$this->z];
    }

    public static function fromArray($a)
    {
        return new Vector3D($a[0],$a[1],$a[2]);
    }

    public function dump()
    {
        return "[{$this->x},{$this->y},{$this->z}]";
    }

}

class Tri3D
{
    public $v1;
    public $v2;
    public $v3;
    public $E1;
    public $E2;
    public $E3;
    public $L1;  //Length of first edge $E1
    public $L2;  //Length of second edge $E2
    public $ex; //E1 and E2 cross

    public $normal;

    function __construct($v1,$v2=null,$v3=null)
    {
        if (gettype($v1) == "object" && get_class($v1) == "Tri3D")
        {
            $this->v1 = new Vector3D($v1->v1);
            $this->v2 = new Vector3D($v1->v2);
            $this->v3 = new Vector3D($v1->v3);
        }
        else
        {
            $this->v1 = $v1;
            $this->v2 = $v2;
            $this->v3 = $v3;
        }

        $this->normal = ($this->v2->nSubtract($this->v1))->nCross($this->v3->nSubtract($this->v1));
        $this->normal->sNormalize();

        $this->E1 = $this->v2->nSubtract($this->v1);
        $this->E2 = $this->v3->nSubtract($this->v1);
        $this->E3 = $this->v3->nSubtract($this->v2);
        $this->ex = $this->E1->nCross($this->E2);
        $this->L1 = $this->E1->length();
        $this->L2 = $this->E3->length();
    }

    public function is($t)
    {
        if ( $t->v1->is($this->v1) && $t->v2->is($this->v2)  && $t->v3->is($this->v3) )
            return true;
        return false;
    }

    public function Intercept($from,$dir)
    {
        if (DEBUG) echo "Intercept Tri: {$this->dump()} From: {$from->dump()} Dir: {$dir->dump()}\n";
        $d = -($dir->dot($this->ex));
        if (DEBUG) echo "Intercept d {$d}\n";
        if ($d < 1e-6)
            return null;
        $i = 1 / $d;
        $AO = $from->nSubtract($this->v1);
        $t = $AO->dot($this->ex) * $i;

        $DAO = $AO->nCross($dir);
        $u = $this->E2->dot($DAO) * $i;
        $v = -($this->E1->dot($DAO) * $i);


        //Correct for intercept on edge
        if (abs($u) < 1e-6)
            $u = 0.0;
        if (abs($v) < 1e-6)
            $v = 0.0;

        if ($t >= 0.0 && $u >= 0.0 && $v >= 0.0 && ($u + $v) <= 1.0)
        {
            $vhit = $from->nAdd($dir->nScale($t));
            $vhit->d = $t;
            $vhit->u = $u;
            $vhit->v = $v;
            $vhit->w = 1.0 - ($v + $u);

            if (DEBUG) echo "Intercept hit {$vhit->dump()}\n";
            return $vhit;
        }
        else
        {
            if (DEBUG) echo "Intercept No Hit t {$t} u {$u} v {$v}\n";
            return null;
        }
    }

    public function area()
    {
        $AB = $this->v2->nSubtract($this->v1);
        $AC = $this->v3->nSubtract($this->v1);
        $cross = $AB->nCross($AC);
        return $cross->length() / 2.0;
    }

    public function reflection($dir)
    {
        $refv = $dir->nSubtract($this->normal->nScale(2 * $dir->dot($this->normal)));
        if (DEBUG) echo "Reflection vector calc dir {$dir->dump()} normal {$this->normal->dump()} reflection vector {$refv->dump()}\n";
        return $refv;
    }

    public function toArray()
    {
        return [$this->v1->toArray(),$this->v2->toArray(),$this->v3->toArray()];
    }

    public function fromArray($a)
    {
        return new Trid3D(Vector3D::fromArray($a[0]),Vector3D::fromArray($a[1]), Vector3D::fromArray($a[2]));
    }

    public function dump()
    {
        return $this->v1->dump() . " " . $this->v2->dump() . " " . $this->v3->dump() . " Normal " . $this->normal->dump() . " E1 " . $this->E1->dump() . " E2 " . $this->E2->dump() . " ex " . $this->ex->dump();
    }
}

class Colour3D
{
    public $r;
    public $g;
    public $b;
    public $a;

    function __construct($r=null,$g=null,$b=null,$a=1)
    {
        if ($r !== null)
        {
            if (gettype($r) == "string")
            {
                if (strlen($r) < 6)
                    throw new \Exception('Colour3D::__contruct String input must be a minium of 6 characters');
                if (substr($r,0,1) == "#")
                    $r = substr($r,1);
                $this->r = hexdec(substr($r,0,2)) / 255.0;
                $this->g = hexdec(substr($r,2,2)) / 255.0;
                $this->b = hexdec(substr($r,4,2)) / 255.0;
                if (strlen($r) >= 8)
                    $this->a = hexdec(substr($r,6,2)) / 255.0;
            }
            else
            {
                $this->r = $r;
                $this->g = $g;
                $this->b = $b;
                $this->a = $a;
            }
        }
        else
        {
            $this->r = 0.0;
            $this->g = 0.0;
            $this->b = 0.0;
            $this->a = 1.0;
        }
    }

    public function nBlend($colour,$f=1.0)
    {
        $r = new Colour3D();
        $r->r = $colour->r * $this->r * $f;
        $r->g = $colour->g * $this->g * $f;
        $r->b = $colour->b * $this->b * $f;
        return $r;
    }

    public function nAdd($colour)
    {
        return new Colour3D($this->r + $colour->r, $this->g + $colour->g, $this->b + $colour->b);
    }

    public static function nAverage($a)
    {
        $r = 0.0;
        $g = 0.0;
        $b = 0.0;
        foreach($a as $c)
        {
            $r += $c->r;
            $g += $c->g;
            $b += $c->b;
        }
        $r = $r / count($a);
        $g = $g / count($a);
        $b = $b / count($a);
        return new Colour3D($r,$g,$b,1.0);
    }

    public function nDim($f)
    {
        return new Colour3D($this->r * $f, $this->g * $f, $this->b *$f);
    }

    public function is($colour)
    {
        if ($this->r == $colour->r && $this->g == $colour->g && $this->b == $colour->b)
            return true;
        return false;
    }

    public function toDecArray()
    {
        return [$this->r,$this->g,$this->b];
    }

    public function toArray()
    {
        return [$this->r,$this->g,$this->b,$this->a];
    }

    public static function fromArray($a)
    {
        return new Colour3D($a[0],$a[1],$a[2],$a[3]);
    }

    public function toString()
    {
        return "#" . bin2hex(max(0,min(255,$this->r * 255))) . bin2hex(max(0,min(255,$this->g * 255))) . bin2hex(max(0,min(255,$this->b * 255)));
    }

    public function dump()
    {
        return "[{$this->r},{$this->g},{$this->b},{$this->a}]";
    }
}

class Surface3D
{
    /*
    Surface properties

    'type' 'solid' | 'bitmap'
    'colour' => Colour3D::
    'opaqueness' => 0.0 - 1.0 (1 is completely opaque)
    'reflection' => 0.0 - 1.0 (1 is mirror)
    'bitmapfile' => <filename>
    */

    public $vectors = array();
    public $tris = array();
    public $properties;
    public $bitmap = null;

    function __construct($in,$properties=null)
    {
        if (gettype($in) == "object" && get_class($in) == "Tri3D")
        {
            array_push($this->tris, $in);
            array_push($this->vectors, $in->v1);
            array_push($this->vectors, $in->v2);
            array_push($this->vectors, $in->v3);
        }
        else
        {
            if (count($in) < 3  || count($in) > 4)
                throw new Exception('Surface3D::__contruct Must only be 3 or 4 vectors');

            for ($i = 0; $i < count($in); $i++)
                array_push($this->vectors, $in[$i]);

            for ($i = 0; $i < count($in)-1 ;$i+=2)
            {
                array_push($this->tris, new Tri3D($in[$i],$in[$i+1],$in[($i+2) % count($in)]));
            }
        }
        $this->properties = $properties;

        if ($this->properties['type'] == 'bitmap' && isset($this->properties['bitmapfile']))
        {
            $this->bitmap = new Bitmap(0,0);
            $this->bitmap->load($this->properties['bitmapfile']);
        }
    }

    public function allTris()
    {
        return $this->tris;
    }

    public function getBMColour($tri,$vhit)
    {
        if ($this->bitmap)
        {
            for ($idx = 0; $idx < count($this->tris); $idx++)
            {
                if ($this->tris[$idx]->is($tri))
                {
                    $x = min(intval(round($this->bitmap->w * $vhit->v)), $this->bitmap->w-1);
                    $y = min(intval(round($this->bitmap->h * $vhit->w)), $this->bitmap->h-1);
                    if ($idx > 0)
                    {
                        $x = ($this->bitmap->w -1) - $x;
                        $y = ($this->bitmap->h -1) - $y;
                    }

                    if ( ($y * $this->bitmap->w) + $x  > ($this->bitmap->h * $this->bitmap->w) -1 )
                    {
                        echo "BM Access error x {$x} y {$y} v {$vhit->v} w {$vhit->v}\n";
                        exit();
                    }

                    $a = $this->bitmap->getPixel($x,$y);
                    return new Colour3D($a[0],$a[1],$a[2]);
                }
            }
        }
    }

    public function toArray()
    {
        $ret = array();
        $ret['properties'] = $this->properties;
        if (isset($this->properties['colour']))
            $ret['properties'] ['colour'] = ($this->properties['colour'])->toArray();
        if (isset($this->properties['bitmapreflectioncolour']))
            $ret['properties'] ['bitmapreflectioncolour'] = ($this->properties['bitmapreflectioncolour'])->toArray();
        $vectors = array();
        foreach ($this->vectors as $v)
        {
            array_push($vectors,$v->toArray());
        }
        $ret["vectors"] = $vectors;
        $tris = array();
        foreach($this->tris as $t)
        {
            array_push($tris,$t->toArray());
        }
        $ret["tris"] = $tris;
        return $ret;
    }

    public static function fromArray($a)
    {
        $in = array();
        foreach($a['vectors'] as $v)
            array_push($in,Vector3D::fromArray($v));
        $properties = $a['properties'];
        if (isset($a['properties'] ['colour']))
            $properties['colour'] = Colour3D::fromArray($a['properties'] ['colour']);
        if (isset($a['properties'] ['bitmapreflectioncolour']) )
            $properties['bitmapreflectioncolour'] = Colour3D::fromArray($a['properties'] ['bitmapreflectioncolour']);
        return new Surface3D($in,$properties);
    }

    public function dump()
    {

    }
}

class Shape3D
{
    public $name = "";
    public $surfaces = array();

    function __construct()
    {

    }

    public function allTris()
    {
        $tris = array();
        foreach($this->surfaces as $s)
        {
            $tris = array_merge($tris,$s->allTris());
        }
        return $tris;
    }

    public function allSurfaces()
    {
        return $this->surfaces;
    }

    public function toArray()
    {
        $ret = array();
        $ret["type"] = get_class($this);
        $ret["name"] = $this->name;
        $surfaces = array();
        foreach($this->surfaces as $s)
            array_push($surfaces,$s->toArray());
        $ret['surfaces'] = $surfaces;
        return $ret;
    }

    public static function fromArray($a)
    {
        $s = new Shape3D();
        $s->name = $a['name'];
        foreach($a['surfaces'] as $su)
        {
            array_push($s->surfaces,Surface3D::fromArray($su));
        }
        return $s;
    }

}

class Floor3D extends Shape3D
{
    public $w;
    public $h;
    public $d;
    public $offset;
    public $points = array();
    public $properties = null;

    function __construct($w,$h,$d,$offset,$properties=null,$name=null)
    {
        $this->w = $w;
        $this->h = $h;
        $this->d = $d;
        $this->offset = $offset;
        if ($name)
            $this->name = $name;

        $this->points[0] = (new Vector3D(0,0,0))->nAdd($offset);
        $this->points[1] = (new Vector3D(0,0,$d))->nAdd($offset);
        $this->points[2] = (new Vector3D($w,0,$d))->nAdd($offset);
        $this->points[3] = (new Vector3D($w,0,0))->nAdd($offset);

        if (!$properties)
            $properties = ["colour" => new Colour3D(0,0,0),"type" => "solid"];

        $this->surfaces[0] = new Surface3D([$this->points[0],$this->points[1],$this->points[2],$this->points[3]],$properties);
    }
}

class Box3D extends Shape3D
{
    public $w;
    public $h;
    public $d;
    public $points = array();
    public $offset;


    function __construct($w,$h,$d,$offset,$properties=null,$name=null)
    {
        $this->w = $w;
        $this->h = $h;
        $this->d = $d;
        $this->offset = $offset;

        if ($name)
            $this->name = $name;

        if (!$properties)
            $properties = ["type" => "solid" , "colour" => new Colour3D(0,0,0)];
        if (isset($properties['faces']))
        {
            $faces = $properties['faces'];
        }
        else
        {
            $faces['front'] = $properties;
            $faces['bottom'] = $properties;
            $faces['back'] = $properties;
            $faces['top'] = $properties;
            $faces['right'] = $properties;
            $faces['left'] = $properties;
        }

        //Create the vectors
        $this->points[0] = (new Vector3D(0,0,0))->nAdd($offset);
        $this->points[1] = (new Vector3D(0,$h,0))->nAdd($offset);
        $this->points[2] = (new Vector3D($w,$h,0))->nAdd($offset);
        $this->points[3] = (new Vector3D($w,0,0))->nAdd($offset);
        $this->points[4] = (new Vector3D(0,0,$d))->nAdd($offset);
        $this->points[5] = (new Vector3D(0,$h,$d))->nAdd($offset);
        $this->points[6] = (new Vector3D($w,$h,$d))->nAdd($offset);
        $this->points[7] = (new Vector3D($w,0,$d))->nAdd($offset);

        $this->surfaces[0] = new Surface3D([$this->points[0],$this->points[1],$this->points[2],$this->points[3]],$faces['front']);
        $this->surfaces[0]->name = "front";
        $this->surfaces[1] = new Surface3D([$this->points[0],$this->points[3],$this->points[7],$this->points[4]],$faces['bottom']);
        $this->surfaces[1]->name = "bottom";
        $this->surfaces[2] = new Surface3D([$this->points[7],$this->points[6],$this->points[5],$this->points[4]],$faces['back']);
        $this->surfaces[2]->name = "back";
        $this->surfaces[3] = new Surface3D([$this->points[1],$this->points[5],$this->points[6],$this->points[2]],$faces['top']);
        $this->surfaces[3]->name = "top";
        $this->surfaces[4] = new Surface3D([$this->points[3],$this->points[2],$this->points[6],$this->points[7]],$faces['right']);
        $this->surfaces[4]->name = "right";
        $this->surfaces[5] = new Surface3D([$this->points[4],$this->points[5],$this->points[1],$this->points[0]],$faces['left']);
        $this->surfaces[5]->name = "left";

    }
}

class Pyramid3D extends Shape3D
{
    public $w;
    public $h;
    public $sides;
    public $points = array();
    public $offset;

    function __construct($w,$h,$sides,$offset,$properties=null,$name=null)
    {
        $this->w = $w;
        $this->h = $h;
        $this->sides = $sides;
        $this->offset = $offset;

        if ($name)
            $this->name = $name;

        if (!$properties)
            $properties = ["type" => "solid" , "colour" => new Colour3D(0,0,0)];

        $theta = pi()-(2*pi())/$sides;

        //Create base
        $this->points[0] = new Vector3D(0,0,0);
        $this->points[1] = new Vector3D(cos($theta)*$w,0,sin($theta)*$w);
        for ($i = 2; $i < $sides;$i++)
        {
            $this->points[$i] =  (($this->points[$i-1])->nDirTo($this->points[$i-2]))->nRotateY($theta);
            $this->points[$i] = $this->points[$i]->nScale($w);
            $this->points[$i] = $this->points[$i]->nAdd($this->points[$i-1]);
        }

        $sumx = 0.0;
        $sumz = 0.0;
        for ($i = 0; $i < $sides;$i++)
        {
            $sumx += $this->points[$i]->x;
            $sumz += $this->points[$i]->z;
        }

        $this->points[$sides] = new Vector3D($sumx/$sides,$h,$sumz/$sides);

        //Add all the offsets
        for ($i = 0; $i <= $sides;$i++)
        {
            $this->points[$i] = $this->points[$i]->nAdd($offset);
        }

        //Now build the faces
        for ($i = 0; $i < $sides;$i++)
        {
            $this->surfaces[$i] = new Surface3D([$this->points[$i],$this->points[($i+1)%$sides],$this->points[$sides]],$properties);
        }
    }
}

class Camera3D
{
    public $eye;
    public $dir;
    public $focalLength = 1.0;

    function __construct($eye,$dir,$focalLength=1.0)
    {
        $this->eye = $eye;
        $this->dir = $dir;
        $this->focalLength = $focalLength;
    }

    public function toArray()
    {
        return ["eye" => $this->eye->toArray(),"dir" => $this->dir->toArray(),"focallength" => $this->focalLength];
    }

    public static function fromArray($a)
    {
        return new Camera3D(Vector3D::fromArray($a['eye']),Vector3D::fromArray($a['dir']),$a['focallength']);
    }

    public function dump()
    {
        $e = "null";
        if ($this->eye !== null)
            $e = $this->eye->dump();
        $d = "null";
        if ($this->dir !== null)
            $d = $this->dir->dump();
        echo "Eye: {$e} Dir: {$d} F: {$this->focalLength}";
    }
}

class Light3D
{
    public $type;
    public $colour;
    public $location;
    public $direction;

    function __construct($type,$colour,$location=null,$direction=null)
    {
        $this->type = $type;
        $this->colour = $colour;
        $this->location = $location;
        $this->direction = $direction;
    }

    public function toArray()
    {
        $ret = array();
        $ret["type"] = $this->type;
        $ret["colour"] = $this->colour->toArray();
        if ($this->location)
            $ret["location"] = $this->location->toArray();
        if ($this->direction)
            $ret["direction"] = $this->direction->toArray();
        return $ret;
    }

    public static function fromArray($a)
    {
        $l = null;
        $d = null;
        if (isset($a['location']))
            $l = Vector3D::fromArray($a['location']);
         if (isset($a['direction']))
            $d = Vector3D::fromArray($a['direction']);
       return new Light3D($a['type'],Colour3D::fromArray($a['colour']),$l,$d);
    }
}

class Scene3D
{
    public $background;
    public $camera;
    public $lights = array();
    public $shapes = array();
    public $name = "";

    function __construct($background,$camera,$name="")
    {
        $this->background = $background;
        $this->camera = $camera;
        $this->name = $name;
    }

    public function addLight($light)
    {
        array_push($this->lights,$light);
    }

    public function createLight($type,$colour,$location=null,$direction=null)
    {
        array_push($this->lights,new Light3D($type,$colour,$location,$direction));
    }

    public function addShape($s)
    {
        array_push($this->shapes,$s);
    }

    public function allSurfaces()
    {
        $s = array();
        foreach($this->shapes as $shape)
        {
            $s = array_merge($s,$shape->allSurfaces());
        }
        return $s;
    }

    private static function hitsort($a,$b)
    {
        return ($a['v']->d > $b['v']->d);
    }

    private function hit($from,$to,$surfaces)
    {
        $hits = array();
        //Find any intercepts
        foreach($surfaces as $surface)
        {
            foreach($surface->allTris() as $tri)
            {
                $v = $tri->Intercept($from,$to);
                if ($v)
                {
                    //Check that the hit is not the same as the from vector
                    if ($from->lengthTo($v) > 1e-6)
                        array_push($hits,["v" => $v,"t" => $tri,"s" => $surface]);
                }
            }
        }
        $hitCount = count($hits);
        if (DEBUG) echo "Hit: Count {$hitCount}\n";
        if ($hitCount == 0)
            return null;

        //Sort hits by closest

        if ($hitCount > 1)
        {
            //var_dump($hits);
            uasort($hits, array('\devt\threed\Scene3D','hitsort') );
            //var_dump($hits);
        }

        //$hits[0] is the closest hit
        if (DEBUG) echo "Following is the tri we have hit\n";
        $hit = array_shift($hits);
        if (DEBUG) var_dump($hit);
        return $hit;
    }

    public function RayTrace($from,$to,$surfaces)
    {
        $ambient = new Colour3D(0,0,0);
        $hit = $this->hit($from,$to,$surfaces);
        if ($hit)
        {
            //Hit contains a surface and tri and vector og th hit.
            $surface = $hit["s"];
            $surface_properties = $surface->properties;
            $hitColour = null;
            $refColor = new Colour3D(0,0,0,1);

            switch ($surface_properties["type"])
            {
                case "solid":
                    $hitColour = $surface_properties["colour"];
                    break;
                case "bitmap":
                    $hitColour = $surface->getBMColour($hit["t"],$hit["v"]);
                    break;
            }

            if (isset($surface_properties["reflection"]) && $surface_properties["reflection"] > 0.0)
            {
                $tri = $hit['t'];

                //We need to get the refelction
                if ($surface_properties["type"] == "bitmap")
                {
                    if( isset($surface_properties["bitmapreflectioncolour"]) && $hitColour->is($surface_properties["bitmapreflectioncolour"]))
                    {
                        $reflectionV = $tri->reflection($to);
                        $cref = $this->RayTrace($hit['v'],$reflectionV,$surfaces);
                        if (!$cref)
                        {
                            $refColor = $this->background->nDim($surface_properties["reflection"]);
                        }
                        else
                        {
                            $refColor = $cref->nDim($surface_properties["reflection"]);
                        }
                    }
                }
                else
                {
                    //Now get refelction colour and amount
                    $reflectionV = $tri->reflection($to);
                    $cref = $this->RayTrace($hit['v'],$reflectionV,$surfaces);
                    if (!$cref)
                        $refColor = $this->background->nDim($surface_properties["reflection"]);
                    $refColor = $cref->nDim($surface_properties["reflection"]);
                }
            }


            //Loop for each light
            $others = array();

            foreach($this->lights as $light)
            {
                switch ($light->type)
                {
                    case 'ambient':
                        $ambient = $hitColour->nBlend($light->colour, 1.0);
                        break;
                    case 'omni':
                        //First can we see the light
                        //echo "Doing second hit for imni light\n";
                        $dirToLight = $hit['v']->nDirTo($light->location);
                        if (! $this->hit( $hit['v'] , $dirToLight, $surfaces) )
                        {
                            //Need dot product of light
                            $dot = $hit['t']->normal->dot($dirToLight);
                            if (DEBUG) echo "Dot product for light is {$dot}\n";
                            if ($dot > 0)
                                array_push($others,$hitColour->nBlend($light->colour, $dot));
                        }
                        break;
                }
            }

            foreach($others as $o)
            {
                $ambient = $ambient->nAdd($o);
            }

            return $ambient->nAdd($refColor);

        }
        return null;
    }

    public function renderToPlane($bl,$scale,$bm,$options=null)
    {
        //Inputs $bl = plane offset
        //$bm = bitmap

        //Plane us defined as
        // $bl - bottom left + $w width and $h height
        //options ["paintbackground" => true,"smoothe" => false,"scetch" => false]   Paints the background on a ray miss

        $w = $bm->w;
        $h = $bm->h;

        $incr = 1;
        if ($options && isset($options['scetch']) && $options['scetch'])
            $incr = 2;

        $surfaces = $this->allSurfaces();

        $start = 0;
        $end = $h-1;
        if (isset($options["startline"]))
            $start = intval($options["startline"]);
        if (isset($options["endline"]))
            $end = intval($options["endline"]);



        for ($y = $start; $y <= $end; $y+= $incr)
        {
            for ($x = 0; $x < $w; $x+= $incr)
            {

                $x1 = $bl->x + ($x/$scale);
                $y1 = $h/$scale - ($bl->y + ($y/$scale));

                if ($options && isset($options["smoothe"]) && $options["smoothe"])
                {
                    $cs = array();
                    for ($y2 = $y1 - 0.5;$y2 < $y1+1;$y2+=0.5)
                    {
                        for ($x2 = $x1 - 0.5;$x2 < $x1+1;$x2+=0.5)
                        {
                            $dirTo = $this->camera->eye->nDirTo(new Vector3D($x2,0,$y2));
                            $c = $this->RayTrace($this->camera->eye,$dirTo,$surfaces);
                            if ($c)
                            {
                                array_push($cs,$c);
                            }
                        }
                    }
                    if (count($cs) > 0)
                    {
                        $c = Colour3D::nAverage($cs);
                        $bm->setPixel($x,$y,[$c->r,$c->g,$c->b]);
                    }
                    else
                    {
                        if (isset($options) && isset($options["paintbackground"]) && $options["paintbackground"])
                            $bm->setPixel($x,$y,$this->background->toDecArray());
                    }
                }
                else
                {

                    $dirTo = $this->camera->eye->nDirTo(new Vector3D($x1,0,$y1));
                    $c = $this->RayTrace($this->camera->eye,$dirTo,$surfaces);
                    if ($c)
                    {
                        if ($x == 2  && $y == 20)
                        {
                            echo "Debug: setPixel x {$x} y {$y} colour {$c->dump()}\n";
                        }
                        $bm->setPixel($x,$y,[$c->r,$c->g,$c->b]);
                    }
                    else
                    {
                        if (isset($options) && isset($options["paintbackground"]) && $options["paintbackground"])
                            $bm->setPixel($x,$y,$this->background->toDecArray());
                    }
                }
            }

            if (isset($options["partialimage"]))
            {
                $modu = intval($options["partialLineMod"]);
                $filename = $options["partialimagename"];
                if ($y % $modu == 0)
                {
                    $bm->save($filename);
                }
            }

        }
    }

    public function renderToPlaneTest($bl,$w,$h,$x,$y)
    {
        //Tests for a specific pixel
        $x1 = $bl->x + $x;
        $y1 = $h - ($bl->y + $y);

        if (DEBUG) echo "renderToPlaneTest: Translate of {$x},{$y} to [{$x1},0,{$y1}]\n";
        $surfaces = $this->allSurfaces();
        $dirTo = $this->camera->eye->nDirTo(new Vector3D($x1,0,$y1));
        return $this->RayTrace($this->camera->eye,$dirTo,$surfaces);
    }

    public function toArray()
    {
        $scene = array();
        $scene['name'] = $this->name;
        $scene['background'] = $this->background->toArray();

        $lights = array();
        foreach($this->lights as $l)
            array_push($lights,$l->toArray());
        $scene['lights'] = $lights;

        $scene['camera'] = $this->camera->toArray();

        $shapes = array();
        foreach($this->shapes as $s)
            array_push($shapes,$s->toArray());
        $scene['shapes'] = $shapes;
        return $scene;
    }

    public static function fromArray($a)
    {
        $s = new Scene3D(Colour3D::fromArray($a['background']),Camera3D::fromArray($a['camera']),$a['name']);
        foreach ($a['lights'] as $l)
        {
            $s->addLight(Light3D::fromArray($l));
        }
        foreach ($a['shapes'] as $sh)
        {
            $s->addShape(Shape3D::fromArray($sh));
        }

        return $s;
    }
}

?>