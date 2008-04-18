<?php

/**
 * Sabel_DB_Mssql_Statement
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Mssql_Statement extends Sabel_DB_Statement
{
  public function setDriver($driver)
  {
    if ($driver instanceof Sabel_DB_Mssql_Driver) {
      $this->driver = $driver;
    } else {
      $message = __METHOD__ . '() $driver should be an instance of Sabel_DB_Mssql_Driver';
      throw new Sabel_Exception_InvalidArgument($message);
    }
  }
  
  public function execute($bindValues = array(), $additionalParameters = array())
  {
    $result = parent::execute($bindValues);
    
    if (!$this->isSelect() || empty($result) || !extension_loaded("mbstring")) {
      return $result;
    }
    
    $fromEnc = (defined("SDB_MSSQL_ENCODING")) ? SDB_MSSQL_ENCODING : "SJIS";
    $toEnc   = mb_internal_encoding();
    $columns = $this->metadata->getColumns();
    
    foreach ($result as &$row) {
      foreach ($columns as $name => $column) {
        if (isset($row[$name]) && ($column->isString() || $column->isText())) {
          $row[$name] = mb_convert_encoding($row[$name], $toEnc, $fromEnc);
        }
      }
    }
    
    return $result;
  }
  
  public function escape(array $values)
  {
    if (extension_loaded("mbstring")) {
      $toEnc = (defined("SDB_MSSQL_ENCODING")) ? SDB_MSSQL_ENCODING : "SJIS";
      $fromEnc = mb_internal_encoding();
      
      $currentRegexEnc = mb_regex_encoding();
      mb_regex_encoding($fromEnc);
      
      foreach ($values as $k => &$val) {
        if (is_bool($val)) {
          $val = ($val) ? "1" : "0";
        } elseif (is_string($val)) {
          $val = "'" . mb_convert_encoding(mb_ereg_replace("'", "''", $val), $toEnc, $fromEnc) . "'";
        }
      }
      
      mb_regex_encoding($currentRegexEnc);
    } else {
      foreach ($values as &$val) {
        if (is_bool($val)) {
          $val = ($val) ? "1" : "0";
        } elseif (is_string($val)) {
          $val = "'" . str_replace("'", "''", $val) . "'";
        }
      }
    }
    
    return $values;
  }
  
  public function createBlob($binary)
  {
    return new Sabel_DB_Mssql_Blob($binary);
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
      $sql = "SELECT * FROM (SELECT ROW_NUMBER() OVER({$orderBy}) AS [SDB_RN], $projection "
           . "FROM $tblName" . $this->join . $this->where . $orderBy . ") "
           . "WHERE [SDB_RN] BETWEEN " . ($offset + 1) . " AND " . ($offset + $limit);
    } else {
      $sql = "SELECT $projection FROM $tblName" . $this->join . $this->where;
      
      if (isset($c["order"])) {
        $sql .= " ORDER BY " . $this->quoteIdentifierForOrderString($c["order"]);
      }
    }
    
    return $sql;
  }
  
  public function quoteIdentifier($arg)
  {
    if (is_array($arg)) {
      foreach ($arg as &$v) {
        $v = '[' . $v . ']';
      }
      return $arg;
    } elseif (is_string($arg)) {
      return '[' . $arg . ']';
    } else {
      $message = __METHOD__ . "() argument must be a string or an array.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
  }
}
