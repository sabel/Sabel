<?php

/**
 * Sabel Validator
 *
 * @category   Validate
 * @package    org.sabel.validate
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Validate_Validator
{
  abstract public function isError($column, $value);
  
  public function hasCustom($column)
  {
  }
  
  // @todo value???
  public function custom($name, $value)
  {
    $method = 'validate' . ucfirst($name);
    $this->$method();
  }
}
