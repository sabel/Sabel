<?php

/**
 * Sabel_DB_Abstract_Condition
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Abstract_Condition extends Sabel_Object
{
  protected
    $column = "",
    $value  = null,
    $isNot  = false;

  public abstract function build(Sabel_DB_Abstract_Sql $sql, &$counter);
  
  public function __construct($column)
  {
    if (strpos($column, ".") === false) {
      $this->column = $column;
    } else {
      list($mdlName, $column) = explode(".", $column);
      $this->column = convert_to_tablename($mdlName) . "." . $column;
    }
  }
  
  public function column()
  {
    return $this->column;
  }
  
  public function value()
  {
    return $this->value;
  }
  
  public function setValue($value)
  {
    $this->value = $value;
    
    return $this;
  }
  
  public function isNot($bool = null)
  {
    if ($bool === null) {
      return $this->isNot;
    } elseif (is_bool($bool)) {
      $this->isNot = $bool;
    } else {
      throw new Sabel_DB_Exception("argument must be a string.");
    }
    
    return $this;
  }
  
  protected function getColumnWithNot()
  {
    return ($this->isNot) ? "NOT " . $this->column : $this->column;
  }
  
  protected function toQueryPart($instance, $sql)
  {
    if ($instance instanceof Sabel_DB_Sql_Part_Interface) {
      return $instance->getValue($sql);
    } else {
      throw new Sabel_DB_Sql_Exception("cannot convert object to sql string");
    }
  }
}
