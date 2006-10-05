<?php

/**
 * Sabel Container of multiple Errors
 *
 * @category   Validate
 * @package    org.sabel.validate
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Validate_Errors
{
  protected $errors = array();
  
  public function add($name, $msg, $type = null)
  {
    $this->errors[$name] = new Sabel_Validate_Error($name, $msg, $type);
  }
  
  public function get($name)
  {
    return $this->errors[$name];
  }
  
  public function getErrors()
  {
    return $this->errors;
  }
  
  public function hasError()
  {
    return (count($this->errors) > 0);
  }
}