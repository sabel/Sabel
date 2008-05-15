<?php

/**
 * Sabel_Response_Object
 *
 * @category   Response
 * @package    org.sabel.response
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Response_Object extends Sabel_Object implements Sabel_Response
{
  protected $status      = null;
  protected $location    = "";
  protected $contentType = "";
  protected $headers   = array();
  protected $responses = array();
  
  public function __construct()
  {
    $this->status = new Sabel_Response_Status();
  }
  
  public function getStatus()
  {
    return $this->status;
  }
  
  public function setResponse($key, $value)
  {
    $this->responses[$key] = $value;
  }
  
  public function getResponse($key)
  {
    if (isset($this->responses[$key])) {
      return $this->responses[$key];
    } else {
      return null;
    }
  }
  
  public function setResponses(array $responses)
  {
    $this->responses = $responses;
  }
  
  public function getResponses()
  {
    return $this->responses;
  }
  
  public function setHeader($key, $value)
  {
    $this->headers[$key] = $value;
  }
  
  public function getHeader($key)
  {
    if (isset($this->headers[$key])) {
      return $this->headers[$key];
    } else {
      return null;
    }
  }
  
  public function getHeaders()
  {
    return $this->headers;
  }
  
  public function hasHeaders()
  {
    return (count($this->headers) !== 0);
  }
  
  public function outputHeader()
  {
    if (PHP_SAPI === "cli") {
      $header = new Sabel_Response_Header_Cli();
    } else {
      $header = new Sabel_Response_Header_Http();
    }
    
    return $header->output($this);
  }
  
  public function expiredCache($expire = 31536000)
  {
    $this->setHeader("Expires",       date(DATE_RFC822, time() + $expire) . " GMT");
    $this->setHeader("Last-Modified", date(DATE_RFC822, time() - $expire) . " GMT" );
    $this->setHeader("Cache-Control", "max-age={$expire}");
    $this->setHeader("Pragma", "");
  }
  
  public function success()
  {
    $this->status->setCode(Sabel_Response_Status::OK);
    
    return $this;
  }
  
  public function isSuccess()
  {
    return ($this->status->getCode() === Sabel_Response_Status::OK);
  }
  
  public function isFailure()
  {
    return $this->status->isFailure();
  }
  
  public function notFound()
  {
    $this->status->setCode(Sabel_Response_Status::NOT_FOUND);
    
    return $this;
  }
  
  public function isNotFound()
  {
    return ($this->status->getCode() === Sabel_Response_Status::NOT_FOUND);
  }
  
  public function serverError()
  {
    $this->status->setCode(Sabel_Response_Status::INTERNAL_SERVER_ERROR);
    
    return $this;
  }
  
  public function isServerError()
  {
    return ($this->status->getCode() === Sabel_Response_Status::INTERNAL_SERVER_ERROR);
  }
  
  public function forbidden()
  {
    $this->status->setCode(Sabel_Response_Status::FORBIDDEN);
    
    return $this;
  }
  
  public function isForbidden()
  {
    return ($this->status->getCode() === Sabel_Response_Status::FORBIDDEN);
  }
  
  public function badRequest()
  {
    $this->status->setCode(Sabel_Response_Status::BAD_REQUEST);
    
    return $this;
  }
  
  public function isBadRequest()
  {
    return ($this->status->getCode() === Sabel_Response_Status::BAD_REQUEST);
  }
  
  public function notModified()
  {
    $this->status->setCode(Sabel_Response_Status::NOT_MODIFIED);
  }
  
  public function isNotModified()
  {
    return ($this->status->getCode() === Sabel_Response_Status::NOT_MODIFIED);
  }
  
  public function setLocation($to, $host = null)
  {
    if ($host === null) {
      $this->location = $to;
    } else {
      $this->location = "http://" . $host . "/" . $to;
    }
    
    $this->status->setCode(Sabel_Response_Status::FOUND);
    
    return $this;
  }
  
  public function getLocation()
  {
    return $this->location;
  }
  
  public function isRedirected()
  {
    return ($this->status->getCode() === Sabel_Response_Status::FOUND);
  }
}
