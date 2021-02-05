<?php
namespace devt\HTML;
class htmlElement
{
    private $_tag;
    private $_attributes = array();
    private $_children = array();

    function __construct($tag,$id=null,$class=null,$attributes=null)
    {
        $this->_tag = $tag;
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
        $ret .= ">";
        foreach ($this->_children as $child)
        {
            $ret .=  $child->toString();
        }
        $ret .= "</{$this->_tag}>";
        return $ret;
    }
}

class htmlDiv extends htmlElement
{
    function __construct($id=null,$class=null,$attributes=null)
    {
        parent::__construct("div",$id,$class,$attributes);
    }
}

class htmlForm extends htmlElement
{
    function __construct($method="POST",$id=null,$class=null,$attributes=null)
    {
        parent::__construct("form",$id,$class,$attributes);
        $this->addAttribute("method",$method);
    }
}
?>