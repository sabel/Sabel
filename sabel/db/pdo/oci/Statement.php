<?php

/**
 * Sabel_DB_Pdo_Oci_Statement
 *
 * @category   DB
 * @package    org.sabel.db.pdo
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Pdo_Oci_Statement extends Sabel_DB_Pdo_Statement
{
  public function escape(array $values)
  {
    foreach ($values as &$val) {
      if (is_bool($val)) {
        $val = ($val) ? 1 : 0;
      }
    }
    
    return $values;
  }
  
  public function escapeBinary($string)
  {
    return "'" . addcslashes(str_replace("'", "''", $string), "\000\032\\\r\n") . "'";
  }
  
  public function unescapeBinary($byte)
  {
    return stripcslashes(stream_get_contents($byte));
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
  
  protected function createSelectSql()
  {
    $tblName = $this->quoteIdentifier($this->table);
    $projection = $this->getProjection();
    
    $c = $this->constraints;
    $limit  = null;
    $offset = null;
    
    if (isset($c["offset"]) && isset($c["limit"])) {
      $limit  = $c["limit"];
      $offset = $c["offset"];
    } elseif (isset($c["offset"]) && !isset($c["limit"])) {
      $limit  = 100;
      $offset = $c["offset"];
    } elseif (isset($c["limit"]) && !isset($c["offset"])) {
      $limit  = $c["limit"];
      $offset = 0;
    }
    
    if ($limit !== null) {
      if (isset($c["order"])) {
        $order = $c["order"];
      } else {
        $order = convert_to_modelname($this->metadata->getTableName()) . "."
               . $this->metadata->getPrimaryKey() . " ASC";
      }
      
      $orderBy = " ORDER BY " . $this->quoteIdentifierForOrderString($order);
      $sql = "SELECT * FROM (SELECT ROW_NUMBER() OVER({$orderBy}) \"SDB_RN\", $projection "
           . "FROM $tblName" . $this->join . $this->where . $orderBy . ") "
           . "WHERE \"SDB_RN\" BETWEEN " . ($offset + 1) . " AND " . ($offset + $limit);
    } else {
      $sql = "SELECT $projection FROM $tblName" . $this->join . $this->where;
      
      if (isset($c["order"])) {
        $sql .= " ORDER BY " . $this->quoteIdentifierForOrderString($c["order"]);
      }
    }
    
    if ($this->forUpdate) {
      $sql .= " FOR UPDATE";
    }
    
    return $sql;
  }
}
