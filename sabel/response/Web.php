<?php

/**
 * Sabel_Response_Web
 *
 * @category   Controller
 * @package    org.sabel.controller
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Response_Web extends Sabel_Response_Abstract implements Sabel_Response
{
  private $location = "";
  private $locationUri = "";
  
  private $status = 200;
  
  const SUCCESS      = 200;
  const REDIRECTED   = 300;
  const NOT_MODIFIED = 304;
  const NOT_FOUND    = 400;
  const FORBIDDEN    = 403;
  const SERVER_ERROR = 500;
  
  private $contentType = "";
  
  private $headers = array();
  private $responses = array();
  
  public function setResponses($array)
  {
    $this->responses = $array;
  }
  
  public function toArray()
  {
    return $this->responses;
  }
  
  public function getResponses()
  {
    return $this->responses;
  }
  
  public function getResponse($key)
  {
    if (isset($this->responses[$key])) {
      return $this->responses[$key];
    } else {
      return null;
    }
  }
    
  public function setContentType($type)
  {
    $this->contentType = $type;
  }
    
  public function notFound()
  {
    $this->status = self::NOT_FOUND;
    return $this;
  }
  
  public function outputHeader()
  {
    if ($this->contentType !== "") {
      header("Content-Type: " . $this->contentType);
    }
    
    if ($this->headers) {
      foreach ($this->headers as $message => $value) {
        header(ucfirst($message) . ": " . $value);
      }
    }
    
    if ($this->location) {
      l("[Core] Header location: " . var_export($this->location, 1));
    }
    
    if ($this->isForbidden()) {
      header("HTTP/1.0 403 Forbidden");
    } elseif ($this->isNotFound()) {
      header("HTTP/1.0 404 Not Found");
    } elseif ($this->isServerError()) {
      header("HTTP/1.0 500 Internal Server Error");
    } elseif ($this->isRedirected()) {
      header("Location: " . $this->location);
    } elseif ($this->isNotModified()) {
      header("HTTP/1.0 304 Not Modified");
      exit;
    }
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
  }
  
  public function isForbidden()
  {
    return ($this->status === self::FORBIDDEN);
  }
}
