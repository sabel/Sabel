<?php

/**
 * Sabel_Http_Requester_Stream
 *
 * @category   Http
 * @package    org.sabel.http
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2002-2006 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Http_Requester_Stream implements Sabel_Http_Requestable
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
    if ($timeout > 1 && $timeout <= 3600) $this->timeout = $timeout;
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
      $header = array();
      $socket = $this->socket; // alias for performance
      
      stream_socket_sendto($socket, $data);
      
      while (trim($h = stream_get_line($socket, 10240, "\n")) !== "") {
        $header[] = $h;
      }
      return array("header" => $header, "contents" => stream_get_contents($socket));
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
