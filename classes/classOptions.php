<?php
class option
{
    private $_o;

    function __construct()
    {
        $this->_o = 0;
    }

    public function set(int $what)
    {
        $this->_o = $this->_o | $what;
    }

    public function reset(int $what)
    {
        $this->_o = $this->_o & ~ $what;
    }

    public function isset(int $what)
    {
        return boolval($this->_o & $what);
    }

    public function setstate(int $what,bool $state)
    {
        if ($state)   
            $this->set($what);
        else
            $this->reset($what);
    }

    public function same(int $what,bool $value)
    {
        return boolval(($this->isset($what) == $value));
    }
}
?>