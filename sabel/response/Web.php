<?php

/**
 * Sabel_Response_Web
 *
 * @category   Response
 * @package    org.sabel.response
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Response_Web extends Sabel_Object implements Sabel_Response
{
  private $location = "";
  private $locationUri = "";
  
  private $status = 200;
  
  const SUCCESS      = 200;
  const REDIRECTED   = 300;
  const NOT_MODIFIED = 304;
  const BAD_REQUEST  = 400;
  const NOT_FOUND    = 404;
  const FORBIDDEN    = 403;
  const SERVER_ERROR = 500;
  
  private $contentType = "";
  
  private $headers = array();
  private $responses = array();
  
  protected $parameters = array();
  
  public function __get($key)
  {
    return $this->parameters[$key];
  }
  
  public function __set($key, $value)
  {
    $this->parameters[$key] = $value;
  }
  
  public function getResponse($key)
  {
    if (isset($this->responses[$key])) {
      return $this->responses[$key];
    } else {
      return null;
    }
  }
  
  public function setResponse($key, $value)
  {
    $this->responses[$key] = $value;
  }
  
  public function getResponses()
  {
    return $this->responses;
  }
  
  public function setResponses(array $responses)
  {
    $this->responses = $responses;
  }
  
  public function toArray()
  {
    return $this->responses;
  }
  
  public function setContentType($type)
  {
    $this->contentType = $type;
  }
  
  public function getContentType()
  {
    return $this->contentType;
  }
  
  public function hasContentType()
  {
    return ($this->contentType !== "");
  }
   
  public function notFound()
  {
    $this->status = self::NOT_FOUND;
    return $this;
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
    
    $header->output($this);
  }
  
  public function outputHeaderIfRedirected()
  {
    if ($this->isRedirected()) {
      $this->outputHeader();
      return true;
    } else {
      return false;
    }
  }
  
  public function expiredCache($expire = 31536000)
  {
    $this->setHeader("Expires",       date(DATE_RFC822, time() + $expire) . " GMT");
    $this->setHeader("Last-Modified", date(DATE_RFC822, time() - $expire) . " GMT" );
           
    $this->setHeader("Cache-Control", "max-age={$expire}");
    $this->setHeader("Pragma", "");
  }
  
  public function etag($value)
  {
    $this->setHeader("Etag", '"' . $value . '"');
  }
  
  public function outputHeaderIfRedirectedThenExit()
  {
    if ($this->outputHeaderIfRedirected()) exit;
  }
  
  public function isNotFound()
  {
    return ($this->status === self::NOT_FOUND);
  }
  
  public function location($host, $to)
  {
    $this->location = "http://" . $host . "/" . $to;
    $this->locationUri = $to;
    $this->status = self::REDIRECTED;
    return $this;
  }
  
  public function getLocation()
  {
    return $this->location;
  }
  
  public function getLocationUri()
  {
    return $this->locationUri;
  }
  
  public function isRedirected()
  {
    return ($this->status === self::REDIRECTED);
  }
  
  public function notModified()
  {
    $this->status = self::NOT_MODIFIED;
  }
  
  public function isNotModified()
  {
    return $this->status === self::NOT_MODIFIED;
  }
  
  public function success()
  {
    $this->status = self::SUCCESS;
    return $this;
  }
  
  public function isSuccess()
  {
    return ($this->status === self::SUCCESS);
  }
  
  public function serverError()
  {
    $this->status = self::SERVER_ERROR;
    return $this;
  }
  
  public function isServerError()
  {
    return ($this->status === self::SERVER_ERROR);
  }
  
  public function setHeader($message, $value)
  {
    $this->headers[$message] = $value;
  }
  
  public function forbidden()
  {
    $this->status = self::FORBIDDEN;
    return $this;
  }
  
  public function isForbidden()
  {
    return ($this->status === self::FORBIDDEN);
  }
  
  public function isFailure()
  {
    $status = $this->status;
    
    return ($status === self::NOT_FOUND ||
            $status === self::FORBIDDEN ||
            $status === self::SERVER_ERROR);
  }
}
