<?php

/**
 * Sabel_Request_Token
 *
 * @category   Request
 * @package    org.sabel.request
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Request_Token extends Sabel_Object
{
  /**
   * @var string
   */
  protected $tokenValue = "";
  
  /**
   * @return string
   */
  public function __toString()
  {
    return $this->tokenValue;
  }
  
  /**
   * @param string $tokenValue
   *
   * @return void
   */
  public function setValue($tokenValue)
  {
    $this->tokenValue = $tokenValue;
  }
  
  /**
   * @return string
   */
  public function getValue()
  {
    return $this->__toString();
  }
  
  /**
   * @param  string $prefix
   *
   * @throws Sabel_Exception_InvalidArgument
   * @return string
   */
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
