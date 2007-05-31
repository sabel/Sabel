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
  private $status = 200;
  
  const SUCCESS    = 200;
  const REDIRECTED = 300;
  const NOT_FOUND  = 400;
  
  public function notFound()
  {
    $this->status = self::NOT_FOUND;
    return $this;
  }
  
  public function outputHeader()
  {
    if ($this->isNotFound()) {
      header("HTTP/1.0 404 Not Found");
    }
  }
  
  public function isNotFound()
  {
    return ($this->status === self::NOT_FOUND);
  }
  
  public function location($location)
  {
    $this->location = $location;
    $this->status = self::REDIRECTED;
    return $this;
  }
  
  public function getLocation()
  {
    return $this->location;
  }
  
  public function isRedirected()
  {
    return ($this->status === self::REDIRECTED);
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
}
