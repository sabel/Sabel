<?php

/**
 * Sabel_Db_Mysql_Statement
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Mysql_Statement extends Sabel_Db_Statement
{
  public function __construct(Sabel_Db_Mysql_Driver $driver)
  {
    $this->driver = $driver;
  }
  
  public function values(array $values)
  {
    if ($this->isInsert()) {
      foreach ($this->metadata->getColumns() as $colName => $column) {
        if (!isset($values[$colName]) && $this->isVarcharOfDefaultNull($column)) {
          $values[$colName] = null;
        }
      }
    }
    
    return parent::values($values);
  }
  
  public function escape(array $values)
  {
    $conn = $this->driver->getConnection();
    
    foreach ($values as &$val) {
      if (is_bool($val)) {
        $val = ($val) ? 1 : 0;
      } elseif (is_string($val)) {
        $val = "'" . mysql_real_escape_string($val, $conn) . "'";
      }
    }
    
    return $values;
  }
  
  public function createBlob($binary)
  {
    $conn = $this->driver->getConnection();
    return new Sabel_Db_Mysql_Blob($conn, $binary);
  }
  
  public function quoteIdentifier($arg)
  {
    if (is_array($arg)) {
      foreach ($arg as &$v) {
        $v = '`' . $v . '`';
      }
      return $arg;
    } elseif (is_string($arg)) {
      return '`' . $arg . '`';
    } else {
      $message = "argument must be a string or an array.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
  }
  
  private function isVarcharOfDefaultNull($column)
  {
    return ($column->isString() && $column->default === null);
  }
}
