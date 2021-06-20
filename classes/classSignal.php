<?php
class Signal
{
    private $_socksend = null;
    private $_sockrecv = null;
    private $_queue = null;
    private $_port;
    private $_ip = "255.255.255.255";

    function __construct($port)
    {
        $this->_port = $port;
    }

    public function listen()
    {
        if (! $this->_sockrecv)
        {
            $this->_sockrecv = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            socket_set_option($this->_sockrecv, SOL_SOCKET, SO_BROADCAST, 1);
            socket_set_option($this->_sockrecv, SOL_SOCKET, SO_REUSEADDR, 1);
            socket_bind($this->_sockrecv, '0.0.0.0', $this->_port);
        }
        $from = '0.0.0.0';
        $data = '';
        $port = $this->_port;

        $rslt = socket_recvfrom($this->_sockrecv,$data,4096,0,$from,$port);
        if ($rslt !== false)
        {
            return $data;
        }
        return null;
    }

    public function trigger($data)
    {
        if ( ! $this->_socksend)
        {
            $this->_socksend = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            socket_set_option($this->_socksend, SOL_SOCKET, SO_BROADCAST, 1);
        }
        socket_sendto($this->_socksend, $data, strlen($data), 0, $this->_ip, $this->_port);
    }

    public function queue($data)
    {
        if (! $this->_queue)
            $this->_queue = msg_get_queue($this->_port);
        msg_send($this->_queue, 1, $data);
    }

    public function receive()
    {
        $data = null;
        if (! $this->_queue)
            $this->_queue = msg_get_queue($this->_port);
        $stat = msg_stat_queue($this->_queue);
        if ($stat['msg_qnum'] > 0)
        {
            msg_receive($this->_queue, 1, $msgtype, 1024, $data);
            return $data;
        }
        return null;
    }
}
?>