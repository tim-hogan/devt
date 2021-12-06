<?php
namespace devt\bitmap;
use Exception;

class Bitmap
{
    public $file;
    public $header;
    public $dataOffset;
    public $w;
    public $h;
    public $nPixels;

    private $pixels = array();

    function __construct($w,$h,$background = null)
    {
        $this->file = null;
        $this->header = null;
        $this->dataOffset = null;
        $this->w = $w;
        $this->h = $h;
        $this->nPixels = $w * $h;
        if ($background)
        {
            $this->setBackground($background);
        }
    }

    function __destruct()
    {
        if ($this->file)
            fclose($this->file);
    }

    function close()
    {
        if ($this->file)
            fclose($this->file);
        $this->file = null;
    }

    private function Endian($i,$bits)
    {
        switch ($bits)
        {
            case 16:
                $v = intval($i);
                $v = pack('v',$v);
                return $v;

            case 32:
                $v = intval($i);
                $v = pack('V',$v);
                return $v;
        }
    }

    private function createHeader()
    {
        $r = pack("C2",66,77);
        $sz = $this->w * $this->h * 3 + 0x36;
        $r .= $this->Endian($sz,32);
        $r .= $this->Endian(0,32);
        $r .= $this->Endian(0x36,32);
        $r .= $this->Endian(40,32);
        $r .= $this->Endian($this->w,32);
        $r .= $this->Endian($this->h,32);
        $r .= $this->Endian(1,16);
        $r .= $this->Endian(24,16);
        $r .= $this->Endian(0,32);
        $sz = $this->w * $this->h * 3;
        $r .= $this->Endian($sz,32);
        $r .= $this->Endian(0,32);
        $r .= $this->Endian(0,32);
        $r .= $this->Endian(0,32);
        $r .= $this->Endian(0,32);

        return $r;
    }

    public function setPixel($x,$y,$v)
    {
        //v is deciaml colour array as [r,g,b]
        $this->pixels[($y*$this->w)+$x] = $v;
    }

    public function setPixelFromFile($x,$y,$v)
    {
        if (! $this->file)
            throw (new Exception("No file open for getPicelFromFile"));
        $y = ($this->h - 1) - $y;
        $idx = $this->dataOffset + ((($y*$this->w)+$x) * 3);
        fseek($this->file,$idx);
        $r = pack("C1",round(max(min($v[2] * 255,255),0)));
        $r .= pack("C1",round(max(min($v[1] * 255,255),0)));
        $r .= pack("C1",round(max(min($v[0] * 255,255),0)));
        fwrite($this->file,$r,strlen($r));
    }

    public function getPixel($x,$y)
    {
        return $this->pixels[($y*$this->w)+$x];
    }

    public function getPixelFromFile($x,$y)
    {
        $ret = array();
        if (! $this->file)
            throw (new Exception("No file open for getPixelFromFile"));
        $y = ($this->h - 1) - $y;
        $idx = $this->dataOffset + ((($y*$this->w)+$x) * 3);
        fseek($this->file,$idx);
        $data = fread($this->file,3);
        if ($data)
        {
            $ret [2] = ((unpack("Ca",substr($data,0,1)) ['a'])) / 255.0;
            $ret [1] = ((unpack("Ca",substr($data,1,1)) ['a'])) / 255.0;
            $ret [0] = ((unpack("Ca",substr($data,2,1)) ['a'])) / 255.0;
            return $ret;
        }
        else
            return [0,0,0];
    }

    public function isPixelBlack($x,$y)
    {
        $v = $this->getPixel($x,$y);
        if ($v[0] == 0.0 && $v[1] == 0.0 && $v[2] == 0.0)
            return true;
        return false;
    }

    public function isPixelBlackFromFile($x,$y)
    {
        $v = $this->getPixelFromFile($x,$y);
        if ($v[0] == 0.0 && $v[1] == 0.0 && $v[2] == 0.0)
            return true;
        return false;
    }

    public function setBackground($colour)
    {
        //$colour is a deciaml array [r,g,b,a];
        for ($y = 0; $y < $this->h;$y++)
        {
            for ($x = 0; $x < $this->w;$x++)
            {
                $this->pixels[($y*$this->w)+$x] = $colour;
            }
        }
    }

    public function load($fullpath)
    {
        $data = file_get_contents($fullpath);
        if (substr($data,0,1) != "B" && substr($data,1,1) != "M")
            return false;
        $offset = (unpack("Va",substr($data,10,4))) ['a'] ;
        $this->dataOffset = $offset;
        $w = (unpack("Va",substr($data,18,4))) ['a'] ;
        $h = (unpack("Va",substr($data,22,4))) ['a'] ;
        $this->w = $w;
        $this->h = $h;

        $cnt = 0;

        for ($y=$this->h-1;$y>=0;$y--)
        {
            for ($x = 0; $x < $this->w; $x++)
            {
                $idx = ($y*$this->w)+$x;
                $this->pixels[$idx] [2] = ((unpack("Ca",substr($data,$offset+($cnt*3),1)) ['a'])) / 255.0;
                $this->pixels[$idx] [1] = ((unpack("Ca",substr($data,$offset+($cnt*3)+1,1)) ['a'])) / 255.0;
                $this->pixels[$idx] [0] = ((unpack("Ca",substr($data,$offset+($cnt*3)+2,1)) ['a'])) / 255.0;
                $cnt++;
            }
        }
    }

    function loadHeader($fullpath)
    {
        $this->file = fopen($fullpath,"r+");
        $this->header = fread($this->file,26);
        if (substr($this->header,0,1) != "B" && substr($this->header,1,1) != "M")
            return false;

        $this->dataOffset = (unpack("Va",substr($this->header,10,4))) ['a'] ;
        $w = (unpack("Va",substr($this->header,18,4))) ['a'] ;
        $h = (unpack("Va",substr($this->header,22,4))) ['a'] ;
        $this->w = $w;
        $this->h = $h;
    }

    public function save($fullpath)
    {
        $fout = fopen($fullpath,"w");
        $hdr = $this->createHeader();
        fwrite($fout,$hdr,strlen($hdr));
        for ($y=$this->h-1;$y>=0;$y--)
        {
            for ($x = 0; $x < $this->w; $x++)
            {
                $idx = ($y*$this->w)+$x;
                $r = pack("C1",round(max(min($this->pixels[$idx] [2] * 255,255),0)));
                $r .= pack("C1",round(max(min($this->pixels[$idx] [1] * 255,255),0)));
                $r .= pack("C1",round(max(min($this->pixels[$idx] [0] * 255,255),0)));
                fwrite($fout,$r,strlen($r));
            }
        }
        fclose($fout);
    }

    public function writeheader($fullpath)
    {
        $b = false;
        if (! $this->file)
        {
            $b = true;
            $this->file = fopen($fullpath,"w");
        }
        $hdr = $this->createHeader();
        fseek($this->file,0);
        fwrite($this->file,$hdr,strlen($hdr));
        if ($b)
        {
            fclose($this->file);
            $this->file = fopen($fullpath,"r+");
        }
    }

    public function merge($fullpaths)
    {
        foreach($fullpaths as $path)
        {
            echo "Creating new bitmap for load\n";
            $bm = new Bitmap(0,0,[0.0,0.0,0.0,0.0]);
            echo "Created, now loading\n";
            $bm->loadHeader($path);
            echo "Loaded new bitmap - start merge\n";
            $w = min($this->w,$bm->w);
            $h = min($this->h,$bm->h);
            for ($y = 0;$y < $h;$y++)
            {
                for($x = 0; $x < $w;$x++)
                {
                    if ($this->isPixelBlackFromFile($x,$y) )
                        $this->setPixelFromFile($x,$y,$bm->getPixelFromFile($x,$y));
                }
            }
            echo "Merged and return\n";
            $bm->close();
            unset($bm);
        }
    }

    public static function mergeFiles($fout,$filename1,$filename2)
    {
        //Merges filename2 into filename1

        $btycnt = 0;

        $bm1 = new Bitmap(0,0);
        $bm2 = new Bitmap(0,0);
        $bm1->loadHeader($filename1);
        $bm2->loadHeader($filename2);

        //Check that the size is the same
        if ($bm1->w != $bm2->w)
            throw (new Exception("Width of files is different"));
        if ($bm1->h != $bm2->h)
            throw (new Exception("Height of files is different"));

        $bm3 = new Bitmap($bm1->w,$bm1->h);
        $bm3->file = fopen("tmp.bmp","w");
        $hd3 = $bm3->createHeader();
        fwrite($bm3->file,$hd3,strlen($hd3));

        fseek($bm1->file,$bm1->dataOffset);
        fseek($bm2->file,$bm2->dataOffset);
        fseek($bm3->file,$bm2->dataOffset);

        $cnt = $bm1->w * $bm1->h * 3;
        for ($idx = 0; $idx < $cnt; $idx++)
        {
            fwrite($bm3->file,chr(min(255,ord(fread($bm1->file,1)) + ord(fread($bm2->file,1)))),1);
        }
        fseek($bm3->file,0);
        $hd3 = $bm3->createHeader();
        fwrite($bm3->file,$hd3,strlen($hd3));
        $bm1->close();
        $bm2->close();
        $bm3->close();
        
        rename("tmp.bmp", $fout);

        echo "Merge complete from {$filename2} to {$filename1} Bytes written {$btycnt}\n";
    }
}
?>