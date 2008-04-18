<?php

/**
 * Sabel_DB_Pgsql_Statement
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Pgsql_Statement extends Sabel_DB_Statement
{
  public function setDriver($driver)
  {
    if ($driver instanceof Sabel_DB_Pgsql_Driver) {
      $this->driver = $driver;
    } else {
      $message = __METHOD__ . '() $driver should be an instance of Sabel_DB_Pgsql_Driver';
      throw new Sabel_Exception_InvalidArgument($message);
    }
  }
  
  public function execute($bindValues = array())
  {
    $result = parent::execute($bindValues);
    if (!$this->isSelect() || empty($result)) return $result;
    
    $binaryColumns = array();
    foreach ($this->metadata->getColumns() as $column) {
      if ($column->isBinary()) $binaryColumns[] = $column->name;
    }
    
    if (!empty($binaryColumns)) {
      foreach ($result as &$row) {
        foreach ($binaryColumns as $colName) {
          if (isset($row[$colName])) {
            $row[$colName] = pg_unescape_bytea($row[$colName]);
          }
        }
      }
    }
    
    return $result;
  }
  
  public function escape(array $values)
  {
    $conn = $this->driver->getConnection();
    
    foreach ($values as &$val) {
      if (is_bool($val)) {
        $val = ($val) ? "'t'" : "'f'";
      } elseif (is_string($val)) {
        $val = "'" . pg_escape_string($conn, $val) . "'";
      }
    }
    
    return $values;
  }
  
  public function createBlob($binary)
  {
    $conn = $this->driver->getConnection();
    return new Sabel_DB_Pgsql_Blob($conn, $binary);
  }
}
