<?php

/**
 * Sabel_DB_Abstract_Common_Schema
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Abstract_Common_Schema extends Sabel_DB_Abstract_Schema
{
  public function getTableLists()
  {
    $sql  = sprintf($this->tablesSql, $this->schemaName);
    $rows = $this->execute($sql);
    if (empty($rows)) return array();

    $tables = array();
    foreach ($rows as $row) {
      $row = array_change_key_case($row);
      $tables[] = $row["table_name"];
    }

    return $tables;
  }

  protected function createColumns($tblName)
  {
    $sql  = sprintf($this->columnsSql, $this->schemaName, $tblName);
    $rows = $this->execute($sql);
    if (empty($rows)) return array();

    $columns = array();
    foreach ($rows as $row) {
      $row = array_change_key_case($row);
      $colName = $row["column_name"];
      $columns[$colName] = $this->makeColumnValueObject($row);
    }

    return $columns;
  }

  protected function makeColumnValueObject($row)
  {
    $column = new Sabel_DB_Schema_Column();
    $column->name = $row["column_name"];
    $column->nullable = ($row["is_nullable"] !== "NO");

    $type = $row["data_type"];

    if ($this->isBoolean($type, $row)) {
      $column->type = Sabel_DB_Type::BOOL;
    } else {
      Sabel_DB_Type_Setter::send($column, $type);
    }

    $this->setDefault($column, $row);
    $this->setIncrement($column, $row);
    $this->setPrimaryKey($column, $row);

    if ($column->primary) $column->nullable = false;
    if ($column->isString()) $this->setLength($column, $row);

    return $column;
  }
}
