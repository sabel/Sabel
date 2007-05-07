<?php

/**
 * error handling
 *
 * @category   Error
 * @package    org.sabel.errors
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Errors
{
  protected $errors = array();
  
  public function add($name, $msg)
  {
    $this->errors[$name] = $msg;
    return $this;
  }
  
  public function get($name)
  {
    if (isset($this->errors[$name])) return $this->errors[$name];
  }
  
  public function hasError()
  {
    return (count($this->errors) > 0);
  }
  
  public function count()
  {
    return count($this->errors);
  }
  
  public function toArray()
  {
    return $this->errors;
  }
}
