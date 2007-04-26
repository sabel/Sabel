<?php

/**
 * Sabel_DB_Migration_Common
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Migration_Common extends Sabel_DB_Migration_Base
{
  public function addColumn()
  {
    $cols = $this->createColumns();
    $tblName = convert_to_tablename($this->mdlName);

    if ($this->type === "upgrade") {
      foreach ($cols as $col) {
        $line = $this->createColumnAttributes($col);
        $this->executeQuery("ALTER TABLE $tblName ADD " . $line);
      }
    } else {
      foreach ($cols as $col) {
        $line = $this->createColumnAttributes($col);
        $this->executeQuery("ALTER TABLE $tblName DROP " . $col->name);
      }
    }
  }

  public function dropColumn()
  {
    $tblName = convert_to_tablename($this->mdlName);

    if ($this->type === "upgrade") {
      $cols    = $this->getDropColumns();
      $restore = $this->getRestoreFileName();

      if (!is_file($restore)) {
        $columns = array();
        $schema  = $this->getTableSchema();

        foreach ($schema->getColumns() as $column) {
          if (in_array($column->name, $cols)) $columns[] = $column;
        }

        $fp = fopen($restore, "w");
        $this->writeRestoreFile($fp, true, $columns);
      }

      foreach ($cols as $column) {
        $this->executeQuery("ALTER TABLE $tblName DROP $column");
      }
    } else {
      $cols = $this->createColumns($this->getRestoreFileName());
      foreach ($cols as $col) {
        $line = $this->createColumnAttributes($col);
        $this->executeQuery("ALTER TABLE $tblName ADD " . $line);
      }
    }
  }

  protected function getDropColumns()
  {
    $fp   = fopen($this->filePath, "r");
    $cols = array();

    while (!feof($fp)) {
      $line = trim(fgets($fp, 256));
      if ($line === "") continue;
      $cols[] = $line;
    }

    fclose($fp);
    return $cols;
  }

  public function changeColumn()
  {
    $tblName = convert_to_tablename($this->mdlName);
    $schema  = $this->getTableSchema();

    if ($this->type === "upgrade") {
      $cols = $this->createColumns();
      $columnNames = array();
      foreach ($cols as $col) $columnNames[] = $col->name;

      $restore = $this->getRestoreFileName();
      if (!is_file($restore)) {
        $columns = array();

        foreach ($schema->getColumns() as $column) {
          if (in_array($column->name, $columnNames)) $columns[] = $column;
        }

        $fp = fopen($restore, "w");
        $this->writeRestoreFile($fp, true, $columns);
      }

      $this->changeColumnUpgrade($cols, $schema, $tblName);
    } else {
      $cols = $this->createColumns($this->getRestoreFileName());
      $this->changeColumnDowngrade($cols, $schema, $tblName);
    }
  }
}
