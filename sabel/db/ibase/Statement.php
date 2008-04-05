<?php

/**
 * Sabel_DB_Ibase_Statement
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Ibase_Statement extends Sabel_DB_Statement
{
  public function setDriver($driver)
  {
    if ($driver instanceof Sabel_DB_Ibase_Driver) {
      $this->driver = $driver;
    } else {
      $message = "driver should be an instance of Sabel_DB_Ibase_Driver";
      throw new Sabel_Exception_InvalidArgument($message);
    }
  }
  
  public function escape(array $values)
  {
    foreach ($values as &$val) {
      if (is_bool($val)) {
        $val = ($val) ? 1 : 0;
      } elseif (is_string($val)) {
        $val = "'" . ibase_escape_string($val) . "'";
      }
    }
    
    return $values;
  }
  
  public function escapeBinary($string)
  {
    $escaped = $this->escape(array($string));
    return addcslashes($escaped[0], "\000\032\\\r\n");
  }
  
  public function unescapeBinary($byte)
  {
    return stripcslashes($byte);
  }
  
  protected function createSelectSql()
  {
    $sql = "SELECT ";
    $c = $this->constraints;
    
    if (isset($c["limit"])) {
      $query  = "FIRST {$c["limit"]} ";
      $query .= (isset($c["offset"])) ? "SKIP " . $c["offset"] : "SKIP 0";
      $sql   .= $query . " ";
    } elseif (isset($c["offset"])) {
      $sql   .= "SKIP " . $c["offset"] . " ";
    }
    
    $projection = $this->getProjection();
    $sql .= "$projection FROM "
          . $this->quoteIdentifier($this->table)
          . $this->join . $this->where
          . $this->createConstraintSql();
    
    if ($this->forUpdate) {
      $sql .= " FOR UPDATE";
    }
    
    return $sql;
  }
  
  public function createInsertSql()
  {
    if (($column = $this->seqColumn) !== null) {
      $seqName = strtoupper("{$this->table}_{$column}_seq");
      $rows = $this->driver->execute("SELECT GEN_ID({$seqName}, 1) AS id FROM RDB\$DATABASE");
      $id = $rows[0]["id"];
      $values = array_merge($this->values, array($column => $id));
      $this->values($values);
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
      $sql .= " ORDER BY " . $this->quoteIdentifierForOrderString($c["order"]);
    }
    
    return $sql;
  }
}

if (ini_get("magic_quotes_sybase") === "1") {
  function ibase_escape_string($val)
  {
    return $val;
  }
}
else {
  function ibase_escape_string($val)
  {
    return str_replace("'", "''", $val);
  }
}
