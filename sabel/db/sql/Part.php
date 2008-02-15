<?php

/**
 * Sabel_DB_Sql_Part
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Sql_Part extends Sabel_Object
{
  protected
    $fmt    = null,
    $values = array(),
    $quote  = false,
    $escape = false;
    
  public static function create($fmt)
  {
    $instance = new self();
    
    $args = func_get_args();
    if (count($args) > 1) {
      unset($args[0]);
      $instance->values = $args;
    }
    
    $instance->fmt = $fmt;
    
    return $instance;
  }
  
  public function quote($bool)
  {
    if (is_bool($bool)) {
      $this->quote = $bool;
    } else {
      $message = "argument must be a boolean.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
    
    return $this;
  }
  
  public function escape($bool)
  {
    if (is_bool($bool)) {
      $this->escape = $bool;
    } else {
      $message = "argument must be a boolean.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
    
    return $this;
  }
  
  public function getSqlValue(Sabel_DB_Abstract_Statement $stmt)
  {
    if (empty($this->values)) {
      return $this->fmt;
    } else {
      $values = $this->values;
      
      if ($this->quote) {
        $values = $stmt->quoteIdentifier($values);
      }
      
      if ($this->escape) {
        $values = $stmt->escape($values);
      }
      
      return vsprintf($this->fmt, $values);
    }
  }
}
