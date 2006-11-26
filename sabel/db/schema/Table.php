<?php

/**
 * Sabel_DB_Schema_Table
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage schema
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Schema_Table
{
  protected $tableName = '';
  protected $columns   = array();

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

  public function getPrimaryKey()
  {
    $pKey = array();
    foreach ($this->columns as $column) {
      if ($column->primary) $pKey[] = $column->name;
    }

    if (empty($pKey)) return null;
    return (sizeof($pKey) === 1) ? $pKey[0] : $pKey;
  }

  public function getIncrementKey()
  {
    foreach ($this->columns as $column) {
      if ($column->increment) return $column->name;
    }
    return null;
  }
}
