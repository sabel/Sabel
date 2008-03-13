<?php

/**
 * HTTP Header
 *
 * @category   Http
 * @package    org.sabel.http
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Http_Header
{
  protected $headers       = array();
  protected $returnCode    = 0;
  protected $returnHttp    = '';
  protected $returnMessage = '';
  
  public function __construct($headers = array())
  {
    foreach ($headers as $header) $this->add($header);
  }
  
  public function get($name)
  {
    if (isset($this->headers[$name])) {
      return $this->headers[$name];
    } else {
      return null;
    }
  }
  
  public function add($headerLine)
  {
    if (substr($headerLine, 0, 5) === "HTTP/") {
      $parts = explode(" ", $headerLine);
      $this->returnHttp    = $parts[0];
      $this->returnCode    = (int)$parts[1];
      $this->returnMessage = $parts[2];
    } else {
      $parts = array_map("trim", explode(":", $headerLine));
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
