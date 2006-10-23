<?php

/**
 * Simple Error Contaienr
 *
 * @category   Validate
 * @package    org.sabel.validate
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Validate_Error
{
  const GRATHER_THEN =  5;
  const LOWER_THEN   = 10;
  const NOT_NULL     = 15;
  const CUSTOM       = 30;
  
  protected $name  = '';
  protected $msg   = '';
  protected $type  = '';
  protected $value = '';
  
  public function __construct($name, $msg, $value, $type)
  {
    $this->name  = $name;
    $this->msg   = $msg;
    $this->value = $value;
    $this->type  = $type;
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  public function getMessage()
  {
    return $this->msg;
  }
  
  public function getType()
  {
    return $this->type;
  }
  
  public function getValue()
  {
    return $this->value;
  }
}
