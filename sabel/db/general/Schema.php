<?php

Sabel::using('Sabel_DB_Base_Schema');

/**
 * Sabel_DB_General_Schema
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @subpackage general
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_General_Schema extends Sabel_DB_Base_Schema
{
  protected $schema = '';

  public function __construct($connectName, $schema)
  {
    $this->schema = $schema;
    $this->driver = Sabel_DB_Connection::getDriver($connectName);
  }

  public function getTableNames()
  {
    $tables = array();

    $sql = sprintf($this->tableList, $this->schema);
    $this->driver->execute($sql);

    foreach ($this->driver->getResultSet() as $row) {
      $row = array_change_key_case($row);
      $tables[] = $row['table_name'];
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
    Sabel::using('Sabel_DB_Type_Setter');

    $sql = sprintf($this->tableColumns, $this->schema, $table);
    $this->driver->execute($sql);

    $columns = array();
    foreach ($this->driver->getResultSet() as $row) {
      $row = array_change_key_case($row);
      $colName = $row['column_name'];
      $columns[$colName] = $this->makeColumnValueObject($row);
    }
    return $columns;
  }

  protected function makeColumnValueObject($row)
  {
    $co = new Sabel_ValueObject();
    $co->name     = $row['column_name'];
    $co->nullable = ($row['is_nullable'] !== 'NO');

    $type = $row['data_type'];

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
    $this->driver->execute($sql);
    return $this->driver->getResultSet();
  }
}
