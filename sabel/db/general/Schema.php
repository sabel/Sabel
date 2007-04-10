<?php

/**
 * Sabel_DB_General_Schema
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_General_Schema extends Sabel_DB_Base_Schema
{
  protected $schemaName = '';

  public function __construct($connectionName, $schemaName)
  {
    $this->driver = load_driver($connectionName);
    $this->schemaName = $schemaName;
  }

  public function getTableNames()
  {
    $tables = array();

    $sql = sprintf($this->tableList, $this->schemaName);
    $this->execute($sql);

    foreach ($this->driver->getResult() as $row) {
      $row = array_change_key_case($row);
      $tables[] = $row["table_name"];
    }
    return $tables;
  }

  public function getTables()
  {
    $tables = array();
    foreach ($this->getTableNames() as $tblName) {
      $tables[$tblName] = $this->getTable($tblName);
    }
    return $tables;
  }

  protected function createColumns($table)
  {
    $sql = sprintf($this->tableColumns, $this->schemaName, $table);
    $this->execute($sql);

    $columns = array();
    foreach ($this->driver->getResult() as $row) {
      $row = array_change_key_case($row);
      $colName = $row["column_name"];
      $columns[$colName] = $this->makeColumnValueObject($row);
    }
    return $columns;
  }

  protected function makeColumnValueObject($row)
  {
    $co           = new StdClass();
    $co->name     = $row["column_name"];
    $co->nullable = ($row["is_nullable"] !== "NO");

    $type = $row["data_type"];

    if ($this->isBoolean($type, $row)) {
      $co->type = Sabel_DB_Type_Const::BOOL;
    } else {
      if ($this->isFloat($type)) $type = $this->getFloatType($type);
      Sabel_DB_Type_Setter::send($co, $type);
    }

    $this->setDefault($co, $row);
    $this->setIncrement($co, $row);
    $this->setPrimaryKey($co, $row);

    if ($co->type === Sabel_DB_Type_Const::STRING) $this->setLength($co, $row);
    return $co;
  }

  protected function execute($sql)
  {
    $driver = $this->driver;

    $driver->setSql($sql)->execute();
    return $driver->getResult();
  }
}
