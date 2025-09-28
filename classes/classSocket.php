<?php
/**
 * Class CSocket for wraping tcpip sockets
 * @author Tim Hogan
 * @version 1.0
 */

define("CSOCKET_CLIENT", 0);
define("CSOCKET_SERVER", 1);

class CSocket
{
	private $_type;
	private $_port;
	private $_address;
	private $_socket;
	private $_listen_socket;
	private $_connected;
	private $_bound;
	private $_readBuffer;

	function __construct($type,$port,$address = "0.0.0.0")
	{
		if ($type != CSOCKET_CLIENT && $type != CSOCKET_SERVER)
			throw new Exception("CSocket::__constuct invalid type of {$type}");

		$this->_type = $type;
		$this->_port = $port;
		$this->_address = $address;
		$this->_socket = null;
		$this->_listen_socket = null;
		$this->_connected = false;
		$this->_readBuffer = "";
		$this->_bound = false;

		switch ($this->_type)
		{
			case CSOCKET_CLIENT:
				$this->_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
				if (@socket_connect($this->_socket, $this->_address, $this->_port))
					$this->_connected = true;
				break;
			case CSOCKET_SERVER:
				$this->_listen_socket = socket_create(AF_INET, SOCK_STREAM, 0);
				if (!socket_bind($this->_listen_socket, $this->_address, $this->_port) )
				{
					@socket_close($this->_listen_socket);
					$this->_listen_socket = null;
					throw new Exception("CSocket::__constuct could not bind new server socket");
				}
				$this->_bound = true;
				socket_listen($this->_listen_socket);
				break;
			default:
				break;
		}
	}

	function __destruct()
	{
		if ($this->_socket)
			@socket_close($this->_socket);
		if ($this->_listen_socket)
			@socket_close($this->_listen_socket);
	}

	public function isConnected()
	{
		return $this->_connected;
	}

	public function isBound()
	{
		return $this->_bound;
	}

	public function accept()
	{
		if ($this->_type != CSOCKET_SERVER)
			throw new Exception("CSocket::accept - socket is not of type  CSOCKET_SERVER");
		if ($this->_listen_socket === null)
			throw new Exception("CSocket::accept - not a valid listening socket");

		if ($newsock = socket_accept($this->_listen_socket))
		{
			$this->_socket = $newsock;
			$this->_connected = true;
			return true;
		}
		return false;
	}

	public function select()
	{
		if ($this->_type != CSOCKET_SERVER)
			throw new Exception("CSocket::accept - socket is not of type  CSOCKET_SERVER");
		if ($this->_listen_socket === null)
			throw new Exception("CSocket::accept - not a valid listening socket");
		$read = [$this->_listen_socket];
		$w = null;
		$e = null;
		if (socket_select($read, $w, $e, 0))
			return true;
		return false;
	}

	public function select_and_accept()
	{
		if ($this->select())
		{
			return $this->accept();
		}
		return false;
	}

	public function write($data)
	{
		if ($this->_connected)
		{
			$length = strlen($data);
			while (true)
			{
				$sent = socket_write($this->_socket, $data, $length);
				if ($sent === false)
				{
					$sockerr = socket_last_error();
					throw new Exception("CSocket::write Socket error: " . socket_strerror($sockerr) . " [{$sockerr}]");
				}
				if ($sent < $length)
				{
					$data = substr($data, $sent);
					$length -= $sent;
				}
				else
				{
					break;
				}
			}

		}
		else
			throw new Exception("CSocket::write socket not connected");
		return true;
	}

	public function readBinary($length=0)
	{
		//Reads binary data from socket for length
		//If length thne keeps reading until EOF.
		if ($this->_connected)
		{
			$str = "";
			while ($d = socket_read($this->_socket, 1000))
			{
				$str .= $d;
				if ($length > 0 && strlen($str) >= $length)
					return $str;
			}
			return $str;
		}
		else
			throw new Exception("CSocket::readBinary socket not connected");
	}

	public function readLine()
	{
		if ($this->_connected)
		{
			$str = "";
			if (strlen($this->_readBuffer) > 0)
			{
				$offset = strpos($this->_readBuffer, "\n");
				if ($offset !== false)
				{
					$str = substr($this->_readBuffer, 0,$offset);
					$this->_readBuffer = substr($this->_readBuffer, $offset + 1);
					return $str;
				}
			}

			while ($d = socket_read($this->_socket, 1000))
			{
				$this->_readBuffer .= $d;
				$offset = strpos($this->_readBuffer, "\n");
				if ($offset !== false)
				{
					$str = substr($this->_readBuffer, 0,$offset);
					$this->_readBuffer = substr($this->_readBuffer, $offset + 1);
					break;
				}
			}
			return $str;
		}
		else
			throw new Exception("CSocket::readMine socket not connected");
	}

	public function close()
	{
		if ($this->_socket)
		{
			@socket_close($this->_socket);
			$this->_socket = null;
			$this->_connected = false;
		}
	}

}
?>