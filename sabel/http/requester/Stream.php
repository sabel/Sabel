<?php

Sabel::using("Sabel_Http_Requestable");

class Sabel_Http_Requester_Stream implements Sabel_Http_Requestable
{
  const NO_INIT   = 0;
  const CONNECTED = 5;
  const DISCONNECTED = 10;
  
  private $socket = null;
  private $state  = 0;
  
  protected $blockingMode = 1;
  protected $protocol     = 'tcp';
  protected $timeout      = 30;
  protected $flag         = STREAM_CLIENT_CONNECT;
  protected $bytesPerRead = 8192;
  
  public function __construct()
  {
  }
  
  public function setProtocol($protocol)
  {
    $this->protocol = $protocol;
  }
  
  public function setTimeout($timeout)
  {
    if ($timeout > 1 && $timeout <= 3600) $this->timeout = $timeout;
  }
  
  
  public function setBytesPerRead($value)
  {
    if ($value > 0) $this->bytesPerRead = $value;
  }
  
  /**
   * connect to a server
   *
   * @param string $host
   * @param int port
   * @throws Sabel_Runtime_Exception
   * @return void
   */
  public function connect($host, $port)
  {
    if (self::NO_INIT === $this->state || self::DISCONNECTED === $this->state) {
      $sock = stream_socket_client("{$this->protocol}://{$host}:{$port}",
                                   $errno, $errstr, $this->timeout, $this->flag);
                                   
      if (!$sock) throw new Sabel_Exception_Runtime("can't connect to server: err#{$errno}: {$errstr}");
      
      stream_set_blocking($sock, $this->blockingMode);
      
      $this->socket = $sock;
      $this->state  = self::CONNECTED;
    }
  }
  
  public function send($data)
  {
    if ($this->state === self::CONNECTED) {
      $result  = array();
      $socket  = $this->socket; // alias for performance
      
      stream_socket_sendto($socket, $data);
      
      $bytesPerRead = $this->bytesPerRead;
      for ($read = 0, $maxBytes = 1310720; !feof($socket) && $read <= $maxBytes;
           $read += $bytesPerRead) {
        $result[] = stream_get_line($socket, $bytesPerRead, "\n");
      }
      
      return $result;
    }
  }
  
  public function disconnect()
  {
    if (self::CONNECTED) fclose($this->socket);
  }
  
  public function setBlokingMode($mode)
  {
    if ($mode === 0 || $mode === 1) $this->blockingMode = $mode;
  }
}
