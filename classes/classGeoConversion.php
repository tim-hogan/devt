<?php

define('NZTM_A',6378137);
define('NZTM_RF',298.257222101);
define('NZTM_CM',173.0);
define('NZTM_OLAT',0.0);
define('NZTM_SF',0.9996);
define('NZTM_FE',1600000.0);
define('NZTM_FN',10000000.0);

define('TWOPI',(2.0*pi()));
define('rad2deg',(180/pi()));

class tmProjection
{
    public $meridian;
    public $scalef;
    public $orglat;
    public $falsee;
    public $falsen;
    public $utom;
    public $a;
    public $rf;
    public $f;
    public $e2;
    public $ep2;
    public $om;

    public function __construct($a,$rf,$cm,$sf,$lto,$fe,$fn,$utom)
    {
        $f = 0.0;
        $this->meridian = $cm;
        $this->scalef = $sf;
        $this->orglat = $lto;
        $this->falsee = $fe;
        $this->falsen = $fn;
        $this->utom = $utom;
        if($rf != 0.0 ) $f = 1.0/$rf; else $f = 0.0;
        $this->a = $a;
        $this->rf = $rf;
        $this->f = $f;
        $this->e2 = 2.0*$f - $f*$f;
        $this->ep2 = $this->e2/( 1.0 - $this->e2 );

        $this->om = tmProjection::meridian_arc( $this, $this->orglat );

    }

    public static function meridian_arc($projection,$lt)
    {
        $e2 = $projection->e2;
        $a = $projection->a;
        $e4 = 0.0;
        $e6 = 0.0;
        $A0 = 0.0;
        $A2 = 0.0;
        $A4 = 0.0;
        $A6 = 0.0;

        $e4 = $e2*$e2;
        $e6 = $e4*$e2;

        $A0 = 1 - ($e2/4.0) - (3.0*$e4/64.0) - (5.0*$e6/256.0);
        $A2 = (3.0/8.0) * ($e2+$e4/4.0+15.0*$e6/128.0);
        $A4 = (15.0/256.0) * ($e4 + 3.0*$e6/4.0);
        $A6 = 35.0*$e6/3072.0;

        return  $a*($A0*$lt-$A2*sin(2*$lt)+$A4*sin(4*$lt)-$A6*sin(6*$lt));
   }
}


class geoConversion
{
    public static function geod_tm($tm,$lt,$ln,&$ce,&$cn)
    {
        $fn = $tm->falsen;
        $fe = $tm->falsee;
        $sf = $tm->scalef;
        $e2 = $tm->e2;
        $a = $tm->a;
        $cm = $tm->meridian;
        $om = $tm->om;
        $utom = $tm->utom;

        $dlon  =  $ln - $cm;
        while ( $dlon > pi() ) $dlon -= TWOPI;
        while ( $dlon < -pi() ) $dlon += TWOPI;

        $m = tmProjection::meridian_arc($tm,$lt);

        $slt = sin($lt);

        $eslt = (1.0-$e2*$slt*$slt);
        $eta = $a/sqrt($eslt);
        $rho = $eta * (1.0-$e2) / $eslt;
        $psi = $eta/$rho;

        $clt = cos($lt);
        $w = $dlon;

        $wc = $clt*$w;
        $wc2 = $wc*$wc;

        $t = $slt/$clt;
        $t2 = $t*$t;
        $t4 = $t2*$t2;
        $t6 = $t2*$t4;

        $trm1 = ($psi-$t2)/6.0;

        $trm2 = (((4.0*(1.0-6.0*$t2)*$psi
                      + (1.0+8.0*$t2))*$psi
                      - 2.0*$t2)*$psi+$t4)/120.0;

        $trm3 = (61 - 479.0*$t2 + 179.0*$t4 - $t6)/5040.0;

        $gce = ($sf*$eta*$dlon*$clt)*((($trm3*$wc2+$trm2)*$wc2+$trm1)*$wc2+1.0);
        $ce = $gce/$utom+$fe;

        $trm1 = 1.0/2.0;

        $trm2 = ((4.0*$psi+1)*$psi-$t2)/24.0;

        $trm3 = ((((8.0*(11.0-24.0*$t2)*$psi
                    -28.0*(1.0-6.0*$t2))*$psi
                    +(1.0-32.0*$t2))*$psi
                    -2.0*$t2)*$psi
                    +$t4)/720.0;

        $trm4 = (1385.0-3111.0*$t2+543.0*$t4-$t6)/40320.0;

        $gcn = ($eta*$t)*(((($trm4*$wc2+$trm3)*$wc2+$trm2)*$wc2+$trm1)*$wc2);
        $cn = ($gcn+$m-$om)*$sf/$utom+$fn;

        return;
   }

    public static function geod_nztm ($lat, $lon)
    {
        $rslt = array();
        $e = 0.0;
        $n = 0.0;

        $tm = new tmProjection(NZTM_A,NZTM_RF,NZTM_CM/rad2deg,NZTM_SF,NZTM_OLAT/rad2deg,NZTM_FE,NZTM_FN,1.0);
        geoConversion::geod_tm($tm,$lat/rad2deg,$lon/rad2deg,$e,$n);

        $rslt['n'] = $n;
        $rslt['e'] = $e;
        return $rslt;
    }

    public static function test()
    {
        $r = geoConversion::geod_nztm (-41.344071, 174.742869);
        echo "Results: Easting {$r['e']} Northing {$r['n']}" . "\n";
        echo "Should be: 1745816.0	5421581.3" . "\n";
    }

    public static function geodeticToGrid($dataumName,$lat,$lon)
    {
        switch (strtoupper($dataumName))
        {
            case 'NZTM':
                return geoConversion::geod_nztm ($lat, $lon);
                break;
            default:
                return ['error' => true, 'message' => 'Invalid Datatum Name' ];
        }
    }

    public static function displayLat($lat,$format)
    {
        $neg = false;
        $str = "";
        switch ($format)
        {
            case "decimal":
                return sprintf("%10.7f",$lat);
                break;
            case "degrees":
                if ($lat < 0.0)
                {
                    $lat = -$lat;
                    $neg = true;
                }
                $de = floor($lat);
                $m  = floor(($lat-$de) * 60.0);
                $s = ($lat * 3600.0) % 60.0;
                $str = sprintf("%d",$de) . "&#176;" . " " . sprintf("%02d",$m) . '&#39;' . " " . sprintf("%6.3f",$s) . '&#34;';
                if ($neg)
                    $str .= "S";
                else
                    $str .= "N";
                return $str;
                break;
            case "degreesdecmin":
                if ($lat < 0.0)
                {
                    $lat = -$lat;
                    $neg = true;
                }
                $de = floor($lat);
                $m  = ($lat-$de) * 60.0;
                $str = sprintf("%d",$de) . "&#176;" . " " . sprintf("%6.3f",$m) . '&#39;' ;
                if ($neg)
                    $str .= "S";
                else
                    $str .= "N";
                return $str;
                break;
            default:
                return floatval($lat);
                break;
        }
    }

    public static function displayLon($lon,$format)
    {
        $neg = false;
        $str = "";
        switch ($format)
        {
            case "decimal":
                return sprintf("%10.7f",$lon);
                break;
            case "degrees":
                if ($lon < 0.0)
                {
                    $lon = -$lon;
                    $neg = true;
                }
                $de = floor($lon);
                $m  = floor(($lon-$de) * 60.0);
                $s = ($lon * 3600.0) % 60.0;
                $str = sprintf("%d",$de) . "&#176;" . " " . sprintf("%02d",$m) . '&#39;' . " " . sprintf("%6.3f",$s) . '&#34;';
                if ($neg)
                    $str .= "W";
                else
                    $str .= "E";
                return $str;
                break;
            case "degreesdecmin":
                if ($lon < 0.0)
                {
                    $lon = -$lon;
                    $neg = true;
                }
                $de = floor($lon);
                $m  = ($lon-$de) * 60.0;
                $str = sprintf("%d",$de) . "&#176;" . " " . sprintf("%6.3f",$m) . '&#39;' ;
                if ($neg)
                    $str .= "W";
                else
                    $str .= "E";
                return $str;
                break;
            default:
                return floatval($lon);
                break;
        }
    }

}
?>