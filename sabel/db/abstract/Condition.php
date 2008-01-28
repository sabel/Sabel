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
  /**
   * @var int
   */
  protected $type;
  
  /**
   * @var string
   */
  protected $column = "";
  
  /**
   * @var mixed
   */
  protected $value = null;
  
  /**
   * @var boolean
   */
  protected $isNot = false;
  
  public abstract function build(Sabel_DB_Abstract_Statement $stmt, &$counter);
  
  /**
   * @param string $column
   */
  public function __construct($column)
  {
    if (strpos($column, ".") === false) {
      $this->column = $column;
    } else {
      list($mdlName, $column) = explode(".", $column);
      $this->column = convert_to_tablename($mdlName) . "." . $column;
    }
  }
  
  /**
   * @return int
   */
  public function getType()
  {
    return $this->type;
  }
  
  /**
   * @return string
   */
  public function getColumn()
  {
    return $this->column;
  }
  
  /**
   * @return mixed
   */
  public function getValue()
  {
    return $this->value;
  }
  
  /**
   * @param mixed $value
   *
   * @return Sabel_DB_Abstract_Condition
   */
  public function setValue($value)
  {
    $this->value = $value;
    
    return $this;
  }
  
  /**
   * @param boolean $bool
   *
   * @return bool
   */
  public function isNot($bool = null)
  {
    if ($bool === null) {
      return $this->isNot;
    } elseif (is_bool($bool)) {
      $this->isNot = $bool;
    } else {
      throw new Sabel_DB_Exception("argument must be a string.");
    }
  }
  
  /**
   * @param Sabel_DB_Abstract_Statement $stmt
   *
   * @return string
   */
  protected function conditionColumn(Sabel_DB_Abstract_Statement $stmt)
  {
    if (strpos($this->column, ".") === false) {
      $column = $stmt->quoteIdentifier($this->column);
    } else {
      list ($tbl, $col) = explode(".", $this->column);
      $column = $stmt->quoteIdentifier($tbl) . "." . $stmt->quoteIdentifier($col);
    }
    
    return ($this->isNot) ? "NOT $column" : $column;
  }
}
