<?php

namespace devt\svg;

class svg
{
    private $width;
    private $height;
    private $doc;
    private $svg;

    function __construct($width=1000,$height=1000)
    {
        $this->width = $width;
        $this->height = $height;
        $this->doc = new \DOMDocument();
        $this->svg = $this->doc->createElement('svg');
        $this->doc->appendChild($this->svg);

        $this->addAttribute($this->svg,'width',$width);
        $this->addAttribute($this->svg,'height',$height);

    }

    private function addAttribute($node,$name,$value)
    {
        $attr = $this->doc->createAttribute($name);
        $attr->value = $value;
        $node->appendChild($attr);
    }

    public function text($x,$y,$text,$align='left',$fill="#000000",$fontsize="10pt",$fontfmaily="Arial, Helvetica, sans-serif")
    {
        $text = $this->doc->createElement('text',$text);
        $anchor = 'start';
        switch ($align)
        {
        case "left":
            $anchor = 'start';
            break;
        case "right":
            $anchor = 'end';
            break;
        case "centre":
            $anchor = 'middle';
            break;
        case "center":
            $anchor = 'middle';
            break;
        default:
            $anchor = 'start';
        }
        $this->svg->appendChild($text);
        $this->addAttribute($text,'x',$x);
        $this->addAttribute($text,'y',$y);
        $this->addAttribute($text,'fill',$fill);
        $this->addAttribute($text,'font-family',$fontfmaily);
        $this->addAttribute($text,'font-size',$fontsize);
        $this->addAttribute($text,'text-anchor',$anchor);
    }

    public function line($x1,$y1,$x2,$y2,$stroke="#000000",$strokewidth="1")
    {
        $line = $this->doc->createElement('line');
        $this->svg->appendChild($line);
        $this->addAttribute($line,'x1',$x1);
        $this->addAttribute($line,'y1',$y1);
        $this->addAttribute($line,'x2',$x2);
        $this->addAttribute($line,'y2',$y2);
        $strstyle = "stroke:{$stroke};stroke-width:{$strokewidth};";
        $this->addAttribute($line,'style',$strstyle);
    }

    public function rect($x,$y,$w,$h,$fill="#ffffff",$stroke="#000000",$strokewidth="1",$opacity="1",$nodeid=null)
    {
        $rect = $this->doc->createElement('rect');
        if ($nodeid)
        {
            $n = $this->doc->getElementById($nodeid);
            if ($n)
                $n->appendChild($rect);
        }
        else
            $this->svg->appendChild($rect);
        $this->addAttribute($rect,'x',$x);
        $this->addAttribute($rect,'y',$y);
        $this->addAttribute($rect,'width',$w);
        $this->addAttribute($rect,'height',$h);
        $strstyle = "fill:{$fill};stroke:{$stroke};stroke-width:{$strokewidth};opacity:{$opacity};";
        $this->addAttribute($rect,'style',$strstyle);

    }

    public function createLinGradiant($id,$direction, ...$stops)
    {
        //direction "vertical | "horizontal"
        //Each stop must have value for offset , style, stop-opacity

        $grad = $this->doc->createElement('linearGradient');
        $this->svg->appendChild($grad);

        $this->addAttribute($grad,'id',$id);
        if ($direction == "vertical")
        {
            $this->addAttribute($grad,'x1',"0%");
            $this->addAttribute($grad,'y1',"0%");
            $this->addAttribute($grad,'x2',"0%");
            $this->addAttribute($grad,'y2',"100%");
        }
        else
        {
            $this->addAttribute($grad,'x1',"0%");
            $this->addAttribute($grad,'y1',"0%");
            $this->addAttribute($grad,'x2',"100%");
            $this->addAttribute($grad,'y2',"0%");
        }

        foreach ($stops as $stop)
        {
            $s = $this->doc->createElement('stop');
            $grad->appendChild($s);

            //<stop offset="0%" style="stop-color:rgb(255,255,0);stop-opacity:1" />

            $this->addAttribute($s,'offset',$stop["offset"]);
            $this->addAttribute($s,'style',$stop["style"]);
        }
    }

    public function save($filename)
    {
        $l = $this->doc->save($filename);
    }
}

?>