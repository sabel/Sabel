<?php

/**
 * Sabel_DB_Oci_Statement
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Oci_Statement extends Sabel_DB_Abstract_Statement
{
  public function __construct(Sabel_DB_Oci_Driver $driver)
  {
    $this->driver = $driver;
  }
  
  public function escape(array $values)
  {
    foreach ($values as &$val) {
      if (is_bool($val)) {
        $val = ($val) ? 1 : 0;
      } elseif (is_string($val)) {
        $val = "'" . addcslashes(str_replace("'", "''", $val), "\000\032\\\n\r") . "'";
      } elseif (is_object($val)) {
        $val = $val->getSqlValue($this);
      }
    }
    
    return $values;
  }
  
  public function createInsertSql()
  {
    if (($column = $this->seqColumn) !== null) {
      $seqName = strtoupper("{$this->table}_{$column}_seq");
      $rows = $this->driver->execute("SELECT {$seqName}.NEXTVAL AS id FROM DUAL");
      $id = $rows[0]["id"];
      $this->values(array_merge($this->values, array($column => $id)));
      $this->driver->setLastInsertId($id);
    }
    
    return parent::createInsertSql();
  }
  
  public function quoteIdentifier($arg)
  {
    if (is_array($arg)) {
      foreach ($arg as &$v) {
        $v = '"' . strtoupper($v) . '"';
      }
      return $arg;
    } elseif (is_string($arg)) {
      return '"' . strtoupper($arg) . '"';
    } else {
      $message = "argument must be a string or an array.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
  }
  
  protected function createConstraintSql()
  {
    $sql = "";
    $c = $this->constraints;
    
    if (isset($c["order"])) {
      $sql .= " ORDER BY " . $this->quoteIdentifierOfOrderBy($c["order"]);
    }
    
    $limit  = (isset($c["limit"]))  ? $c["limit"]  : null;
    $offset = (isset($c["offset"])) ? $c["offset"] : null;
    
    $this->driver->setLimit($limit);
    $this->driver->setOffset($offset);
    
    return $sql;
  }
}
