<?php
namespace devt\GPIO;
use RuntimeException;

interface FileSystemInterface
{
    /**
     * Checks whether a file or directory exists
     *
     * @param string $path Path to the file or directory
     *
     * @return bool true if file exists, false otherwise
     */
    public function exists($path);

    /**
     * Tells whether the filename is a directory
     *
     * @param string $path Path to the file/directory
     *
     * @return bool true if the filename exists and is a directory, false otherwise
     */
    public function isDir($path);

    /**
     * Open a file.
     *
     * @param string $path The path of the file to open
     * @param string $mode The mode to open the file in (see fopen())
     *
     * @return resource A stream resource.
     */
    public function open($path, $mode);

    /**
     * Read the contents of a file.
     *
     * @param string $path The path of the file to read
     *
     * @return string The file contents
     */
    public function getContents($path);

    /**
     * Write a buffer to a file.
     *
     * @param string $path   The path of the file to write to
     * @param string $buffer The buffer to write
     *
     * @return int The number of bytes written
     */
    public function putContents($path, $buffer);
}

final class FileSystem implements FileSystemInterface
{
    /**
     * {@inheritdoc}
     */
    public function open($path, $mode)
    {
        $stream = @fopen($path, $mode);

        $this->exceptionIfFalse($stream);

        return $stream;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents($path)
    {
        $stream = $this->open($path, 'r');

        $contents = @stream_get_contents($stream);
        fclose($stream);

        $this->exceptionIfFalse($contents);

        return $contents;
    }

    /**
     * {@inheritdoc}
     */
    public function putContents($path, $buffer)
    {
        $stream = $this->open($path, 'w');

        $bytesWritten = @fwrite($stream, $buffer);
        fclose($stream);

        $this->exceptionIfFalse($bytesWritten);

        return $bytesWritten;
    }

    private function exceptionIfFalse($result)
    {
        if (false === $result) {
            $errorDetails = error_get_last();
            throw new RuntimeException($errorDetails['message']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function exists($path)
    {
        return file_exists($path);
    }

    /**
     * {@inheritdoc}
     */
    public function isDir($path)
    {
        return is_dir($path);
    }
}

//namespace GPIO\Pin;

interface PinInterface
{
    public const VALUE_LOW = 0;
    public const VALUE_HIGH = 1;

    /**
     * Get the pin number.
     *
     * @return int
     */
    public function getNumber();

    /**
     * Export the pin.
     */
    public function export();

    /**
     * Unexport the pin.
     */
    public function unexport();

    /**
     * Get the pin value.
     *
     * @return int
     */
    public function getValue();
}

//use GPIO\FileSystem\FileSystemInterface;

abstract class Pin implements PinInterface
{
    // Paths
    public const GPIO_PATH = '/sys/class/gpio/';
    public const GPIO_PREFIX = 'gpio';

    // Files
    public const GPIO_FILE_EXPORT = 'export';
    public const GPIO_FILE_UNEXPORT = 'unexport';

    // Pin files
    public const GPIO_PIN_FILE_DIRECTION = 'direction';
    public const GPIO_PIN_FILE_VALUE = 'value';

    // Directions
    public const DIRECTION_IN = 'in';
    public const DIRECTION_OUT = 'out';
    public const DIRECTION_LOW = 'low';
    public const DIRECTION_HIGH = 'high';

    protected $fileSystem;
    protected $number;

    protected $exported = false;

    /**
     * Constructor.
     *
     * @param FileSystemInterface $fileSystem An object that provides file system access
     * @param int                 $number     The number of the pin
     */
    public function __construct(FileSystemInterface $fileSystem, $number)
    {
        $this->fileSystem = $fileSystem;
        $this->number = $number;

        $this->export();
    }

    /**
     * {@inheritdoc}
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        if (!$this->isExported()) {
            $this->exported = true;

            $this->writePinNumberToFile($this->getFile(self::GPIO_FILE_EXPORT));

            // After export, we need to wait some time for kernel to report changes.
            usleep(200 * 1000);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function unexport()
    {
        if ($this->isExported()) {
            $this->writePinNumberToFile($this->getFile(self::GPIO_FILE_UNEXPORT));
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function isExported()
    {
        $directory = $this->getPinDirectory();

        return $this->fileSystem->exists($directory) && $this->fileSystem->isDir($directory);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDirection()
    {
        $directionFile = $this->getPinFile(self::GPIO_PIN_FILE_DIRECTION);

        if (!$this->fileSystem->exists($directionFile)) {
            return null;
        }

        return trim($this->fileSystem->getContents($directionFile));
    }

    /**
     * {@inheritdoc}
     */
    protected function setDirection($direction)
    {
        if ($this->getDirection() !== $direction) {
            $directionFile = $this->getPinFile(self::GPIO_PIN_FILE_DIRECTION);
            $this->fileSystem->putContents($directionFile, $direction);
            usleep(100 * 1000);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        $valueFile = $this->getPinFile(self::GPIO_PIN_FILE_VALUE);
        return (int) trim($this->fileSystem->getContents($valueFile));
    }

    /**
     * Get the path of the import or export file.
     *
     * @param string $file The type of file (import/export)
     *
     * @return string The file path
     */
    private function getFile($file)
    {
        return self::GPIO_PATH . $file;
    }

    /**
     * Get the path of a pin directory.
     *
     * @return string
     */
    protected function getPinDirectory()
    {
        return self::GPIO_PATH . self::GPIO_PREFIX . $this->getNumber();
    }

    /**
     * Get the path of a pin access file.
     *
     * @param string $file The type of pin file (edge/value/direction)
     *
     * @return string
     */
    protected function getPinFile($file)
    {
        return $this->getPinDirectory() . '/' . $file;
    }

    /**
     * Write the pin number to a file.
     *
     * @param string $file The file to write to
     */
    private function writePinNumberToFile($file)
    {
        $this->fileSystem->putContents($file, $this->getNumber());
    }
}


interface InputPinInterface extends PinInterface
{
    public const EDGE_NONE = 'none';
    public const EDGE_BOTH = 'both';
    public const EDGE_RISING = 'rising';
    public const EDGE_FALLING = 'falling';

    /**
     * Get the pin edge.
     *
     * @return string The pin edge value
     */
    public function getEdge();

    /**
     * Set the pin edge.
     *
     * @param string $edge The pin edge value to set
     */
    public function setEdge($edge);
}


final class InputPin extends Pin implements InputPinInterface
{
    public const GPIO_PIN_FILE_EDGE = 'edge';

    /**
     * Constructor.
     *
     * @param FileSystemInterface $fileSystem An object that provides file system access
     * @param int                 $number     The number of the pin
     */
    public function __construct(FileSystemInterface $fileSystem, $number)
    {
        parent::__construct($fileSystem, $number);

        $this->setDirection(self::DIRECTION_IN);
    }

    /**
     * {@inheritdoc}
     */
    public function getEdge()
    {
        $edgeFile = $this->getPinFile(self::GPIO_PIN_FILE_EDGE);
        return trim($this->fileSystem->getContents($edgeFile));
    }

    /**
     * {@inheritdoc}
     */
    public function setEdge($edge)
    {
        $edgeFile = $this->getPinFile(self::GPIO_PIN_FILE_EDGE);
        $this->fileSystem->putContents($edgeFile, $edge);
    }
}

interface OutputPinInterface extends PinInterface
{
    /**
     * Set the pin value.
     *
     * @param int $value The value to set
     */
    public function setValue($value);
}

final class OutputPin extends Pin implements OutputPinInterface
{
    /**
     * Constructor.
     *
     * @param FileSystemInterface $fileSystem An object that provides file system access
     * @param int                 $number     The number of the pin
     */
    public function __construct(FileSystemInterface $fileSystem, $number, $exportDirection = self::DIRECTION_OUT)
    {
        parent::__construct($fileSystem, $number);

        $direction = self::DIRECTION_OUT;

        if ($this->exported) {
            $direction = $exportDirection;
        }

        $this->setDirection($direction);
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        $valueFile = $this->getPinFile(self::GPIO_PIN_FILE_VALUE);
        $this->fileSystem->putContents($valueFile, $value);
    }
}

//namespace GPIO;

//use GPIO\Interrupt\InterruptWatcherInterface;
//use GPIO\Pin\InputPinInterface;
//use GPIO\Pin\OutputPinInterface;

interface GPIOInterface
{
    /**
     * Get an input pin.
     *
     * @param int $number The pin number
     *
     * @return InputPinInterface
     */
    public function getInputPin($number);

    /**
     * Get an output pin.
     *
     * @param int $number The pin number
     *
     * @return OutputPinInterface
     */
    public function getOutputPin($number);

    /**
     * Create an interrupt watcher.
     *
     * @return InterruptWatcherInterface
     */
    public function createWatcher();
}


//use GPIO\FileSystem\FileSystem;
//use GPIO\FileSystem\FileSystemInterface;
//use GPIO\Interrupt\InterruptWatcher;
//use GPIO\Pin\Pin;
//use GPIO\Pin\InputPin;
//use GPIO\Pin\OutputPin;

final class GPIO implements GPIOInterface
{
    private $fileSystem;
    private $streamSelect;

    /**
     * Constructor.
     *
     * @param FileSystemInterface|null $fileSystem Optional file system object to use
     * @param callable|null $streamSelect Optional stream select callable
     */
    public function __construct(FileSystemInterface $fileSystem = null, callable $streamSelect = null)
    {
        $this->fileSystem = $fileSystem ?: new FileSystem();
        $this->streamSelect = $streamSelect ?: 'stream_select';
    }

    /**
     * {@inheritdoc}
     */
    public function getInputPin($number)
    {
        return new InputPin($this->fileSystem, $number);
    }

    /**
     * {@inheritdoc}
     */
    public function getOutputPin($number, $exportDirection = Pin::DIRECTION_OUT)
    {
        if ($exportDirection !== Pin::DIRECTION_OUT && $exportDirection !== Pin::DIRECTION_LOW && $exportDirection !== Pin::DIRECTION_HIGH) {
            throw new \InvalidArgumentException('exportDirection has to be an OUT type (OUT/LOW/HIGH).');
        }

        return new OutputPin($this->fileSystem, $number, $exportDirection);
    }

    /**
     * {@inheritdoc}
     */
    public function createWatcher()
    {
        return new InterruptWatcher($this->fileSystem, $this->streamSelect);
    }
}


//namespace GPIO\Interrupt;

//use GPIO\Pin\InputPinInterface;

interface InterruptWatcherInterface
{
    /**
     * Register a callback to fire on pin interrupts. Only one callback can be registered per pin, this method will overwrite.
     *
     * @param InputPinInterface $pin
     * @param callable $callback
     */
    public function register(InputPinInterface $pin, callable $callback);

    /**
     * Unregister a pin callback.
     *
     * @param InputPinInterface $pin
     */
    public function unregister(InputPinInterface $pin);

    /**
     * Watch for pin interrupts.
     *
     * @param int $timeout The maximum time to watch for in milliseconds.
     */
    public function watch($timeout);
}

class InterruptWatcher implements InterruptWatcherInterface
{
    private $fileSystem;
    private $streamSelect;
    private $streams;
    private $pins;
    private $callbacks;

    /**
     * Constructor.
     *
     * @param FileSystemInterface $fileSystem An object that provides file system access
     * @param callable $streamSelect The stream select implementation
     */
    public function __construct(FileSystemInterface $fileSystem, callable $streamSelect)
    {
        $this->fileSystem = $fileSystem;
        $this->streamSelect = $streamSelect;

        $this->streams = $this->pins = $this->callbacks = [];
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        foreach ($this->streams as $stream) {
            fclose($stream);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function register(InputPinInterface $pin, callable $callback)
    {
        $pinNumber = $pin->getNumber();

        if (!isset($this->streams[$pinNumber])) {
            $file = '/sys/class/gpio/gpio' . $pinNumber . '/value';
            $this->streams[$pinNumber] = $this->fileSystem->open($file, 'r');
            stream_set_blocking($this->streams[$pinNumber], false);
            fread($this->streams[$pinNumber], 1);
            @rewind($this->streams[$pinNumber]);
        }

        $this->pins[$pinNumber] = $pin;
        $this->callbacks[$pinNumber] = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function unregister(InputPinInterface $pin)
    {
        $pinNumber = $pin->getNumber();

        if (isset($this->streams[$pinNumber])) {
            fclose($this->streams[$pinNumber]);

            unset($this->streams[$pinNumber]);
            unset($this->callbacks[$pinNumber]);
            unset($this->pins[$pinNumber]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function watch($timeout)
    {
        $seconds = floor($timeout / 1000);
        $carry = $timeout - ($seconds * 1000);
        $micro = $carry * 1000;

        $read = $write = [];
        $except = $this->streams;

        $streamSelect = $this->streamSelect;
        $result = @$streamSelect($read, $write, $except, $seconds, $micro);

        if (false === $result) {
            return false;
        }

        $triggers = [];

        foreach ($except as $pinNumber => $stream) {
            $value = fread($stream, 1);
            @rewind($stream);

            if ($value !== false) {
                $triggers[$pinNumber] = (int) $value;
            }
        }

        foreach ($triggers as $pinNumber => $value) {
            if (false === call_user_func($this->callbacks[$pinNumber], $this->pins[$pinNumber], $value)) {
                return false;
            }
        }

        return true;
    }
}

?>