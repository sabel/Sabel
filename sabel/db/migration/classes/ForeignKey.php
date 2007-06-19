<?php

/**
 * Sabel_DB_Migration_Classes_ForeignKey
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Migration_Classes_ForeignKey
{
  public $column    = null;
  public $refTable  = null;
  public $refColumn = null;
  public $onDelete  = null;
  public $onUpdate  = null;

  public function __construct($column)
  {
    $this->column = $column;
  }

  public function get()
  {
    if ($this->refTable === null && $this->refColumn === null) {
      $table  = substr($this->column, 0, -3);
      $column = str_replace($table, "", $this->column);
      if ($column === "_id") {
        $column = "id";
      } else {
        throw new Exception("invalid column name for foreign key.");
      }

      $this->refTable  = $table;
      $this->refColumn = $column;
    }

    return $this;
  }

  public function table($tblName)
  {
    $this->refTable = $tblName;

    return $this;
  }

  public function column($colName)
  {
    $this->refColumn = $colName;

    return $this;
  }

  public function onDelete($arg)
  {
    $this->onDelete = $arg;

    return $this;
  }

  public function onUpdate($arg)
  {
    $this->onUpdate = $arg;

    return $this;
  }
}
