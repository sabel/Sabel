<?php

/**
 * Sabel_DB_Migration_Classes_Create
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Migration_Classes_Create
{
  private $mcolumns = array();
  private $columns  = array();
  private $pkeys    = array();
  private $fkeys    = array();
  private $uniques  = array();
  private $options  = array();

  public function column($name)
  {
    $mcolumn = new Sabel_DB_Migration_Classes_Column($name);
    return $this->mcolumns[$name] = $mcolumn;
  }

  public function getColumns($migClass)
  {
    $columns = array();

    foreach ($this->mcolumns as $column) {
      $columns[] = $column->getColumn();
    }

    $pkeys =& $this->pkeys;
    foreach (arrange($columns) as $column) {
      if ($column->primary) $pkeys[] = $column->name;
    }

    foreach ($this->options as $key => $val) {
      $migClass->setOptions($key, $val);
    }

    $migClass->setPrimaryKeys(array_unique($pkeys));
    $migClass->setUniques($this->uniques);
    $migClass->setForeignKeys($this->fkeys);

    return $columns;
  }

  public function primary($columnNames)
  {
    if (is_string($columnNames)) {
      $this->pkeys = (array)$columnNames;
    } elseif (is_array($columnNames)) {
      $this->pkeys = $columnNames;
    } else {
      throw new Exception("argument must be a string or array.");
    }
  }

  public function unique($columnNames)
  {
    if (is_string($columnNames)) {
      $this->uniques[] = (array)$columnNames;
    } elseif (is_array($columnNames)) {
      $this->uniques[] = $columnNames;
    } else {
      throw new Exception("argument must be a string or array.");
    }
  }

  public function fkey($colName)
  {
    $fKey = new Sabel_DB_Migration_Classes_ForeignKey($colName);
    return $this->fkeys[$colName] = $fKey;
  }

  public function options($key, $val)
  {
    $this->options[$key] = $val;
  }
}
