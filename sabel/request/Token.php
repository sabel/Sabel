<?php

/**
 * Sabel_Request_Token
 *
 * @category   Request
 * @package    org.sabel.request
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Request_Token extends Sabel_Object
{
  protected $tokenValue = "";
  
  public function __toString()
  {
    return $this->tokenValue;
  }
  
  public function setValue($tokenValue)
  {
    $this->tokenValue = $tokenValue;
  }
  
  public function getValue()
  {
    return $this->__toString();
  }
  
  public function createValue($prefix = "")
  {
    if (is_string($prefix)) {
      $this->tokenValue = $prefix . md5(uniqid(mt_rand(), true));
      return $this->tokenValue;
    } else {
      throw new Sabel_Exception_InvalidArgument("prefix should be a string.");
    }
  }
}
