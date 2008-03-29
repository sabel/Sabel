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
  protected
    $location    = "",
    $locationUri = "";
  
  protected
    $status      = Sabel_Response::SUCCESS,
    $contentType = "";
  
  protected
    $headers   = array(),
    $responses = array();
  
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
    $this->status = Sabel_Response::SUCCESS;
    
    return $this;
  }
  
  public function isSuccess()
  {
    return ($this->status === Sabel_Response::SUCCESS);
  }
  
  public function isFailure()
  {
    $status = $this->status;
    
    return ($status === Sabel_Response::NOT_FOUND   ||
            $status === Sabel_Response::FORBIDDEN   ||
            $status === Sabel_Response::BAD_REQUEST ||
            $status === Sabel_Response::SERVER_ERROR);
  }
  
  public function notFound()
  {
    $this->status = Sabel_Response::NOT_FOUND;
    
    return $this;
  }
  
  public function isNotFound()
  {
    return ($this->status === Sabel_Response::NOT_FOUND);
  }
  
  public function serverError()
  {
    $this->status = Sabel_Response::SERVER_ERROR;
    
    return $this;
  }
  
  public function isServerError()
  {
    return ($this->status === Sabel_Response::SERVER_ERROR);
  }
  
  public function forbidden()
  {
    $this->status = Sabel_Response::FORBIDDEN;
    
    return $this;
  }
  
  public function isForbidden()
  {
    return ($this->status === Sabel_Response::FORBIDDEN);
  }
  
  public function badRequest()
  {
    $this->status = Sabel_Response::BAD_REQUEST;
    
    return $this;
  }
  
  public function isBadRequest()
  {
    return ($this->status === Sabel_Response::BAD_REQUEST);
  }
  
  public function notModified()
  {
    $this->status = Sabel_Response::NOT_MODIFIED;
  }
  
  public function isNotModified()
  {
    return $this->status === Sabel_Response::NOT_MODIFIED;
  }
  
  public function getLocation()
  {
    return $this->location;
  }
  
  public function getLocationUri()
  {
    return $this->locationUri;
  }
  
  public function location($host, $to)
  {
    $this->location    = "http://" . $host . "/" . $to;
    $this->locationUri = $to;
    $this->status      = Sabel_Response::REDIRECTED;
    
    return $this;
  }
  
  public function isRedirected()
  {
    return ($this->status === Sabel_Response::REDIRECTED);
  }
}
