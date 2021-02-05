<?php
namespace devt\HTML;
class htmlElement
{
    private $_tag;
    private $_value;
    private $_attributes = array();
    private $_children = array();

    function __construct($tag,$value=null,$parent=null,$id=null,$class=null,$attributes=null)
    {
        $this->_tag = $tag;
        $this->_value = $value;
        if ($id)
            $this->_attributes["id"] = $id;
        if ($class)
            $this->_attributes["class"] = $class;
        if ($attributes)
        {
            foreach ($attributes as $name => $value)
            {
                $this->_attributes[$name] = $value;
            }
        }
        if ($parent)
        {
            $parent->insertChild($this);
        }
    }

    function insertChild($child)
    {
        array_push($this->_children,$child);
    }

    function addAttribute($name,$value)
    {
        $this->_attributes[$name] = $value;
    }

    function toString()
    {
        $ret = "<{$this->_tag} ";
        foreach ($this->_attributes as $name => $value)
        {
            $ret .= "{$name}='{$value}'";
        }
        if (strtoupper($this->_tag) == "INPUT" || strtoupper($this->_tag) == "BR" )
            $ret .= "/>";
        else
        {
            $ret .= ">";
            foreach ($this->_children as $child)
            {
                $ret .=  $child->toString();
            }
            $ret .= "</{$this->_tag}>";
        }
        return $ret;
    }
}

class htmlDiv extends htmlElement
{
    function __construct($value=null,$parent=null,$id=null,$class=null,$attributes=null)
    {
        parent::__construct("div",$value,$parent,$id,$class,$attributes);
    }
}

class htmlForm extends htmlElement
{
    function __construct($method="POST",$action=null,$value=null,$parent=null,$id=null,$class=null,$attributes=null)
    {
        parent::__construct("form",$value,$parent,$id,$class,$attributes);
        if (! $action)
            $act = htmlspecialchars($_SERVER['PHP_SELF']);
        else
            $act = $action;

        $this->addAttribute("method",$method);
        $this->addAttribute("action",$act);
    }
}

class htmlInput extends htmlElement
{
    function __construct($type,$name,$value=null,$parent=null,$id=null,$class=null,$attributes=null)
    {
        parent::__construct("input",null,$parent,$id,$class,$attributes);
        if ($type)
            $this->addAttribute("type",$type);
        if ($name)
            $this->addAttribute("name",$name);
        if ($value)
            $this->addAttribute("value",$value);

    }
}

class htmlCell extends htmlElement
{
    function __construct($value=null,$parent=null,$id=null,$class=null,$attributes=null)
    {
        parent::__construct("td",$value,$parent,$id,$class,$attributes);
    }
}

class htmlRow extends htmlElement
{
    function __construct($value=null,$parent=null,$id=null,$class=null,$attributes=null)
    {
        parent::__construct("tr",$value,$parent,$id,$class,$attributes);
    }

    function addCell($value,$id=null,$class=null,$attributes=null)
    {
        $this->insertChild(new htmlCell($value,null,$id,$class,$attributes));
    }
}

class htmlTable extends htmlElement
{
    function __construct($value=null,$parent=null,$id=null,$class=null,$attributes=null)
    {
        parent::__construct("table",$value,$parent,$id,$class,$attributes);
    }
}
?>