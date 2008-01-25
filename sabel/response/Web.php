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
  protected
    $location    = "",
    $locationUri = "";
    
  protected
    $status      = 200,
    $contentType = "";
    
  protected
    $headers   = array(),
    $responses = array();
    
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
  
  public function setHeader($message, $value)
  {
    $this->headers[$message] = $value;
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
    
    return ($status === Sabel_Response::NOT_FOUND ||
            $status === Sabel_Response::FORBIDDEN ||
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
