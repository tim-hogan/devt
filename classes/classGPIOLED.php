<?php
namespace devt\GPIOLed;
use  devt\GPIO\GPIO;
use  devt\GPIO\GPIO\getOutputPin;
use  devt\GPIO\PinInterface;

require dirname(__FILE__) . "/classGPIO.php";

interface LedInterface
{
    public const LED_TYPE_SINGLE = 1;
    public const LED_TYPE_TRICOLOUR = 2;

    public function singleled($state);
    public function multiled($colour,bool $state);
    public function on($colour);
    public function off();
    public function blink($colour,$rate,$duration);
}

class Led implements LedInterface
{
    private $_gpio = null;

    private $_type = LedInterface::LED_TYPE_SINGLE;

    private $_PIN_RED = 25;
    private $_PIN_GREEN = 23;
    private $_PIN_BLUE = 24;
    private $_PIN_SINGLE = 23;

    private $_RedPin = null;
    private $_GreenPin = null;
    private $_BluePin = null;
    private $_singlePin = null;

    private $threads = array();

    public function __construct($type = LedInterface::LED_TYPE_SINGLE, $pins = null)
    {
        $this->_type = $type;

        if ($pins)
        {

            if (isset($pins["red"]))
                $this->_PIN_RED = $pins["red"];

            if (isset($pins["green"]))
                $this->_PIN_GREEN = $pins["green"];

            if (isset($pins["blue"]))
                $this->_PIN_BLUE = $pins["blue"];

            if (isset($pins["single"]))
                $this->_PIN_SINGLE = $pins["single"];
        }

        $this->_gpio= new GPIO();

    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
    }

    public function exportPins()
    {
        $pins = array();
        $pins["red"] = $this->_RedPin;
        $pins["green"] = $this->_GreenPin;
        $pins["blue"] = $this->_BluePin;
        $pins["single"] = $this->_singlePin;
    }

    public function singleled($state)
    {
        if (! $this->_singlePin )
            $this->_singlePin = new $this->_gpio->getOutputPin($this->_PIN_SINGLE);
        $this->_singlePin->setvalue($state ? PinInterface::VALUE_HIGH : PinInterface::VALUE_LOW);
    }

    /**
     * Summary of multiled
     * @param mixed $colour
     *    If colour is an array then values are [0] red, [1] green [2] blue from 0 - 255;
     *    If colour is a string then vali values are:
     *          red, green, blue, yellow, magenta, cyan and white
     * @param bool $state
     */
    public function multiled($colour=null,bool $state)
    {
        if (! $this->_RedPin )
            $this->_RedPin = $this->_gpio->getOutputPin($this->_PIN_RED);
        if (! $this->_GreenPin )
            $this->_GreenPin = $this->_gpio->getOutputPin($this->_PIN_GREEN);
        if (! $this->_BluePin )
            $this->_BluePin = $this->_gpio->getOutputPin($this->_PIN_BLUE);

        if (!$state)
        {
            $this->_RedPin->setvalue(PinInterface::VALUE_LOW);
            $this->_GreenPin->setvalue(PinInterface::VALUE_LOW);
            $this->_BluePin->setvalue(PinInterface::VALUE_LOW);
        }
        else
        {
            switch (gettype($colour))
            {
                case 'array':
                    if ($colour[0] >= 128)
                         $this->_RedPin->setvalue(PinInterface::VALUE_HIGH);
                    if ($colour[1] >= 128)
                         $this->_GreenPin->setvalue(PinInterface::VALUE_HIGH);
                    if ($colour[2] >= 128)
                         $this->_BluePin->setvalue(PinInterface::VALUE_HIGH);
                    break;
                case 'string':
                    switch (strtolower($colour))
                    {
                        case "red":
                            $this->_RedPin->setvalue(PinInterface::VALUE_HIGH);
                            $this->_GreenPin->setvalue(PinInterface::VALUE_LOW);
                            $this->_BluePin->setvalue(PinInterface::VALUE_LOW);
                            break;
                        case "green":
                            $this->_RedPin->setvalue(PinInterface::VALUE_LOW);
                            $this->_GreenPin->setvalue(PinInterface::VALUE_HIGH);
                            $this->_BluePin->setvalue(PinInterface::VALUE_LOW);
                            break;
                        case "blue":
                            $this->_RedPin->setvalue(PinInterface::VALUE_LOW);
                            $this->_GreenPin->setvalue(PinInterface::VALUE_LOW);
                            $this->_BluePin->setvalue(PinInterface::VALUE_HIGH);
                            break;
                        case "yellow":
                            $this->_RedPin->setvalue(PinInterface::VALUE_HIGH);
                            $this->_GreenPin->setvalue(PinInterface::VALUE_HIGH);
                            $this->_BluePin->setvalue(PinInterface::VALUE_LOW);
                            break;
                        case "magenta":
                            $this->_RedPin->setvalue(PinInterface::VALUE_HIGH);
                            $this->_GreenPin->setvalue(PinInterface::VALUE_LOW);
                            $this->_BluePin->setvalue(PinInterface::VALUE_HIGH);
                            break;
                        case "cyan":
                            $this->_RedPin->setvalue(PinInterface::VALUE_LOW);
                            $this->_GreenPin->setvalue(PinInterface::VALUE_HIGH);
                            $this->_BluePin->setvalue(PinInterface::VALUE_HIGH);
                            break;
                        case "white":
                            $this->_RedPin->setvalue(PinInterface::VALUE_HIGH);
                            $this->_GreenPin->setvalue(PinInterface::VALUE_HIGH);
                            $this->_BluePin->setvalue(PinInterface::VALUE_HIGH);
                            break;
                    }
                    break;
            }
        }
    }

    public function on($colour=null)
    {
        switch ($this->_type)
        {
            case LedInterface::LED_TYPE_SINGLE:
                $this->singleled(true);
                break;
            case LedInterface::LED_TYPE_TRICOLOUR:
                $this->multiled($colour,true);
                break;
        }
    }

    public function off()
    {
        switch ($this->_type)
        {
            case LedInterface::LED_TYPE_SINGLE:
                $this->singleled(false);
                break;
            case LedInterface::LED_TYPE_TRICOLOUR:
                $this->multiled(null,false);
                break;
        }
    }

    /**
     * Summary of blink
     * @param mixed $colour
     * @param mixed $rate in blinks per second
     * @param mixed $duration in milliceconds
     */
    public function blink($colour,$rate,$duration)
    {
        $sleeptime = ((1.0/$rate) / 2.0) * 1000000;
        $time_start = microtime(true);
        $time_end = $time_start;
        $duration_seconds = floatval($duration/1000);
        $go = true;

        while ($go)
        {
            $this->multiled($colour,true);
            usleep($sleeptime);
            $this->multiled($colour,false);
            usleep($sleeptime);
            $time_end = microtime(true);
            $go = $duration_seconds > 0 ? ($time_end-$time_start) < $duration_seconds : true;
        }
    }

    public function unexport()
    {
        if ($this->_RedPin)
            $this->_RedPin->unexport();
        if ($this->_GreenPin)
            $this->_GreenPin->unexport();
        if ($this->_BluePin)
            $this->_BluePin->unexport();
        if ($this->_singlePin)
            $this->_singlePin->unexport();
    }
}

?>