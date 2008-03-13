<?php

/**
 * Sabel_Http_Requester_Stream
 *
 * @category   Http
 * @package    org.sabel.http
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Http_Requester_Stream implements Sabel_Http_Requester_Interface
{
  const NO_INIT      = 0;
  const CONNECTED    = 5;
  const DISCONNECTED = 10;
  
  private $socket = null;
  private $state  = 0;
  
  protected $blockingMode = 1;
  protected $protocol     = "tcp";
  protected $timeout      = 30;
  protected $flag         = STREAM_CLIENT_CONNECT;
  
  public function setProtocol($protocol)
  {
    $this->protocol = $protocol;
  }
  
  public function setTimeout($timeout)
  {
    if ($timeout > 1 && $timeout <= 3600) {
      $this->timeout = $timeout;
    }
  }
  
  /**
   * connect to a server
   *
   * @param string $host
   * @param int    $port
   *
   * @throws Sabel_Runtime_Exception
   * @return void
   */
  public function connect($host, $port)
  {
    if (self::NO_INIT === $this->state || self::DISCONNECTED === $this->state) {
      $sock = stream_socket_client("{$this->protocol}://{$host}:{$port}",
                                   $errno, $errstr, $this->timeout, $this->flag);
      
      if ($sock) {
        stream_set_blocking($sock, $this->blockingMode);
        $this->socket = $sock;
        $this->state  = self::CONNECTED;
      } else {
        $message = "can't connect to server: err#{$errno}: $errstr";
        throw new Sabel_Exception_Runtime($message);
      }
    }
  }
  
  public function send($data)
  {
    if ($this->state === self::CONNECTED) {
      $headers = array();
      $socket  = $this->socket;
      
      stream_socket_sendto($socket, $data);
      
      while ($header = stream_get_line($socket, 10240, "\n")) {
        $line = trim($header);
        if ($line === "") break;
        $headers[] = $line;
      }
      
      return array("header" => $headers, "contents" => stream_get_contents($socket));
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
