<?php

/**
 * Sabel_DB_Schema_Table
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Schema_Table
{
  protected $tableName       = "";
  protected $columns         = array();
  protected $primaryKey      = null;
  protected $incrementColumn = null;
  protected $tableEngine     = null;

  public function __construct($name, $columns)
  {
    $this->tableName = $name;
    $this->columns   = $columns;
  }

  public function __get($key)
  {
    return (isset($this->columns[$key])) ? $this->columns[$key] : null;
  }

  public function getTableName()
  {
    return $this->tableName;
  }

  public function getColumns()
  {
    return $this->columns;
  }

  public function getColumnByName($name)
  {
    return $this->columns[$name];
  }

  public function getColumnNames()
  {
    return array_keys($this->columns);
  }

  public function setPrimaryKey($key)
  {
    $this->primaryKey = $key;
  }

  public function getPrimaryKey()
  {
    return $this->primaryKey;
  }

  public function setIncrementColumn($colName)
  {
    $this->incrementColumn = $colName;
  }

  public function getIncrementColumn()
  {
    return $this->incrementColumn;
  }

  public function setTableEngine($engine)
  {
    $this->tableEngine = $engine;
  }

  public function getTableEngine()
  {
    return $this->tableEngine;
  }
}
