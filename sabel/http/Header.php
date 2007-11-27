<?php

/**
 * HTTP Header
 *
 * @category   Http
 * @package    org.sabel.http
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Http_Header
{
  protected $headers       = array();
  protected $returnCode    = 0;
  protected $returnHttp    = '';
  protected $returnMessage = '';
  
  public function __construct($headers = null)
  {
    if (is_array($headers)) {
      foreach ($headers as $header) $this->add($header);
    }
  }
  
  public function __get($name)
  {
    return $this->get($name);
  }
  
  public function get($name)
  {
    $headers = $this->headers;
    return (isset($headers[$name])) ? $headers[$name] : false;
  }
  
  public function add($headerLine)
  {
    if (stripos($headerLine, ':') === false) {
      $parts = explode(' ', $headerLine);
      if (isset($parts[0])) $this->returnHttp = $parts[0];
      if (isset($parts[1])) $this->returnCode =(int) $parts[1];
      if (isset($parts[2])) $this->returnMessage = $parts[2];
    } else {
      $parts = explode(':', $headerLine);
      $this->headers[$parts[0]] = $parts[1];
    }
  }
  
  public function isInformation()
  {
    return ($this->returnCode >= 100 && $this->returnCode < 200);
  }
  
  public function isSuccess()
  {
    return ($this->returnCode >= 200 && $this->returnCode < 300);
  }
  
  public function isForward()
  {
    return ($this->returnCode >= 300 && $this->returnCode < 400);
  }
  
  public function isClientError()
  {
    return ($this->returnCode >= 400 && $this->returnCode < 500);
  }
  
  public function isServerError()
  {
    return ($this->returnCode >= 500 && $this->returnCode <= 599);
  }
  
  public function isOK()
  {
    return ($this->returnCode === 200);
  }
  
  /**
   * The request has been fulfilled and resulted in a new resource being created.
   *
   * @see RFC-2616 10.2.2
   */
  public function isCreated()
  {
    return ($this->returnCode === 201);
  }
  
  public function isAccepted()
  {
    return ($this->returnCode === 202);
  }
  
  public function isNonAuthoritativeInformation()
  {
    return ($this->returnCode === 203);
  }
  
  public function isNoContents()
  {
    return ($this->returnCode === 204);
  }
  
  public function isResetContent()
  {
    return ($this->returnCode === 205);
  }
  
  public function isPartialContent()
  {
    return ($this->returnCode === 206);
  }
  
  public function isMultiStatus()
  {
    return ($this->returnCode === 207);
  }
  
  public function isIMUsed()
  {
    return ($this->returnCode === 226);
  }
  
  public function getReturnCode()
  {
    return $this->returnCode;
  }
  
  public function getReturnMessage()
  {
    return trim($this->returnMessage);
  }
  
  public function toArray()
  {
    return $this->headers;
  }
}
