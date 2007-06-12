<?php

/**
 * Sabel_DB_Migration_Classes_Table
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Migration_Classes_Table
{
  private $mcolumns = array();

  private $columns  = array();
  private $pkeys    = array();
  private $uniques  = array();
  private $options  = array();

  public function column($name)
  {
    $mcolumn = new Sabel_DB_Migration_Classes_Column($name);
    return $this->mcolumns[$name] = $mcolumn;
  }

  public function setUp($migClass)
  {
    $columns = array();

    foreach ($this->mcolumns as $column) {
      $columns[] = $column->getColumn();
    }

    $pkeys = array();

    foreach ($columns as $column) {
      if ($column->primary === true) {
        $column->nullable = false;
      } elseif ($column->nullable === null) {
        $column->nullable = true;
      }

      if ($column->primary === null) {
        $column->primary = false;
      }

      if ($column->increment === null) {
        $column->increment = false;
      }

      if ($column->type === Sabel_DB_Type::STRING &&
          $column->max === null) $column->max = 255;

      if ($column->primary) $pkeys[] = $column->name;
    }

    $this->columns = $columns;

    foreach ($this->options as $key => $val) {
      $migClass->setOptions($key, $val);
    }

    $migClass->setPrimaryKeys($pkeys);

    return $this;
  }

  public function getColumns()
  {
    return $this->columns;
  }

  public function primary($columnNames)
  {
    $this->pkeys = $columnNames;
  }

  public function unique($columnNames)
  {
    $this->uniques[] = $columnNames;
  }

  public function options($key, $val)
  {
    $this->options[$key] = $val;
  }
}
