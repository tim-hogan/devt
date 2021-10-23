<?php
//Common options
define("PARSETEXT_REMOVEBADTAGS",1);       //Remove bad tags

//Input options
define("PARSETEXT_TRIM_SPACE",16);         //Trims spaces from ionput field
define("PARSETEXT_HTMLSPECIALDECODE",32);   //Decodes HTML speical chatacters

//Output options
define("PARSETEXT_EOL_TO_BR",16);            //Converts any eol characters to a <br/>
define("PARSETEXT_REMOVE_EOL",32);           //Removes and eol characters  \r\n \n \r


class ParseText
{
    private $text;

    function __construct($text=null)
    {
        $this->text = $text;
    }

    public function length()
    {
        if ($this->text)
            return strlen($this->text);
        else
            return 0;
    }

    public function raw()
    {
        return $this->text;
    }

    public function toHTML($options=null)
    {
        $v = $this->text;
        if ($options && $options & PARSETEXT_REMOVEBADTAGS)
        {
            $v = ParseText::removeTags($v);
        }
        $v = htmlspecialchars($v);
        if ($options)
        {
            if ($options & PARSETEXT_REMOVE_EOL)
                $v = str_replace(["\r\n", "\n", "\r"],"",$v);

            if ($options & PARSETEXT_EOL_TO_BR)
                $v = str_replace(["\r\n", "\n", "\r"],"<br/>",$v);
        }
        return $v;
    }

    public function toWord($options=null)
    {
        $v = $this->text;
        return $v;
    }

    static public function createFromPost($field,$type='input',$options=PARSETEXT_TRIM_SPACE)
    {
        return new ParseText(ParseText::parsePostField($field,$type,$options));
    }

    static public function removeTags($text)
    {
        $TAGS = ['A','APPLET','AUDIO','BASE','BUTTON','CANVAS','DATA','DATALIST','DIALOG','EMBED','FRAME','FRAMESET','HEAD','HTML','FORM','IFRAME','IMAGE','INPUT','LINK','META','NAV','OBJECT','SCRIPT','SELECT','STYLE','SVG','TEXTAREA','TIME','VAR','VIDEO'];
        $ret = '';
        $idx = 0;
        $start = 0;
        $l = strlen($text);

        $pos = strpos($text,"<",$idx);
        if ($pos === false)
            return $text;

        while ($pos !== false && ($l > $pos+1))
        {
            $tl = $pos-$start;
            $ret .= substr($text,$start,$tl);
            $start += $tl;

            $idx = $pos + 1;
            $tok1 = strtok(strtoupper(trim(substr($text,$pos+1)))," >='\"");
            $tok1 = trim($tok1,"/");
            $tok1 = trim($tok1);
            $pos2 = strpos($text,">",$idx);
            //if (in_array($tok1,$TAGS) && $pos2 !== false)
            if (in_array($tok1,$TAGS))
            {
                if (pos2 !== false)
                    $start += (($pos2 - $pos) +1);
                else
                    $start = $l;
            }
            else
            {
                $ret .= substr($text,$start,1);
                $start++;
            }
            $pos = strpos($text,"<",$idx);
        }
        if ($start < $l)
            $ret .= substr($text,$start,($l-$start)+1);
        return $ret;
    }

    static public function parseFromHTMLInput($text,$type="input",$options=PARSETEXT_TRIM_SPACE)
    {
        $v = $text;
        if ($options)
        {
            if ($options & PARSETEXT_TRIM_SPACE)
                $v = trim($v);
            if ($options & PARSETEXT_HTMLSPECIALDECODE)
                $v = htmlspecialchars_decode($v);
            if ($options & PARSETEXT_REMOVEBADTAGS)
            {
                $v = htmlspecialchars_decode($v);
                $v = ParseText::removeTags($v);
            }
        }
        return  $v;
    }

    static public function parsePostField($field,$type='input',$options=PARSETEXT_TRIM_SPACE)
    {
        $v = null;
        if (isset($_POST[$field]))
            $v = ParseText::parseFromHTMLInput($_POST[$field],$type,$options);
        return $v;
    }
}

?>