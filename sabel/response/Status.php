<?php

/**
 * Sabel_Response_Status
 *
 * @category   Response
 * @package    org.sabel.response
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Response_Status extends Sabel_Object
{
  const OK = 200;
  
  const MOVED_PERMANENTLY = 301;
  const FOUND             = 302;
  const NOT_MODIFIED      = 304;
  
  const BAD_REQUEST       = 400;
  const UNAUTHORIZED      = 401;
  const PAYMENT_REQUIRED  = 402;
  const FORBIDDEN         = 403;
  const NOT_FOUND         = 404;
  
  const INTERNAL_SERVER_ERROR = 500;
  const SERVICE_UNAVAILABLE   = 503;
  
  protected $statuses = array(
    self::OK                    => "OK",
    self::MOVED_PERMANENTLY     => "Moved Permanently",
    self::FOUND                 => "Found",
    self::NOT_MODIFIED          => "Not Modified",
    self::BAD_REQUEST           => "Bad Request",
    self::UNAUTHORIZED          => "Unauthorized",
    self::PAYMENT_REQUIRED      => "Payment Required",
    self::FORBIDDEN             => "Forbidden",
    self::NOT_FOUND             => "Not Found",
    self::INTERNAL_SERVER_ERROR => "Internal Server Error",
    self::SERVICE_UNAVAILABLE   => "Service Unavailable"
  );
  
  protected $statusCode = self::OK;
  
  public function __toString()
  {
    return $this->statusCode . " " . $this->getStatusAsString();
  }
  
  public function getCode()
  {
    return $this->statusCode;
  }
  
  public function setCode($statusCode)
  {
    $this->statusCode = $statusCode;
  }
  
  public function getStatusAsString()
  {
    if (isset($this->statuses[$this->statusCode])) {
      return $this->statuses[$this->statusCode];
    } else {
      return "";
    }
  }
  
  public function isFailure()
  {
    return ($this->statusCode >= 400);
  }
}
