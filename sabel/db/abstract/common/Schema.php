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
    $tables = array();
    $sql    = sprintf($this->tableList, $this->schemaName);

    foreach ($this->execute($sql) as $row) {
      $row = array_change_key_case($row);
      $tables[] = $row["table_name"];
    }
    return $tables;
  }

  protected function createColumns($tblName)
  {
    $sql = sprintf($this->tableColumns, $this->schemaName, $tblName);

    $columns = array();
    foreach ($this->execute($sql) as $row) {
      $row = array_change_key_case($row);
      $colName = $row["column_name"];
      $columns[$colName] = $this->makeColumnValueObject($row);
    }

    return $columns;
  }

  protected function makeColumnValueObject($row)
  {
    $co           = new Sabel_DB_Schema_Column();
    $co->name     = $row["column_name"];
    $co->nullable = ($row["is_nullable"] !== "NO");

    $type = $row["data_type"];

    if ($this->isBoolean($type, $row)) {
      $co->type = Sabel_DB_Type::BOOL;
    } else {
      if ($this->isFloat($type)) $type = $this->getFloatType($type);
      Sabel_DB_Type_Setter::send($co, $type);
    }

    $this->setDefault($co, $row);
    $this->setIncrement($co, $row);
    $this->setPrimaryKey($co, $row);

    if ($co->primary) $co->nullable = false;
    if ($co->isString()) $this->setLength($co, $row);

    return $co;
  }
}
