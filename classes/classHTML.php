<?php
namespace devt\HTML;
class htmlElement
{
    private $_tag;
    private $_attributes = array();
    private $_children = array();

    function __construct($tag)
    {
        $this->_tag = $tag;
    }

    function insertChild($child)
    {
        array_push($this->_children,$child);
    }

    function addAttribute($name,$value)
    {
        $att = array();
        $att[$name] = $value;
        array_push($this->_attributes,$att);
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

class htmlForm extends htmlElement
{
    function __construct($method="POST")
    {
        parent::__construct("form");
        $this->addAttribute("method",$method);
    }
}
?>