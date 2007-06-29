<?php

/**
 * Sabel_DB_Migration_Sqlite
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Migration_Sqlite extends Sabel_DB_Migration_Base
{
  protected $types = array(Sabel_DB_Type::INT      => "int",
                           Sabel_DB_Type::BIGINT   => "bigint",
                           Sabel_DB_Type::SMALLINT => "smallint",
                           Sabel_DB_Type::FLOAT    => "float",
                           Sabel_DB_Type::DOUBLE   => "double",
                           Sabel_DB_Type::BOOL     => "boolean",
                           Sabel_DB_Type::STRING   => "varchar",
                           Sabel_DB_Type::TEXT     => "text",
                           Sabel_DB_Type::DATETIME => "datetime");

  private $autoPrimary = false;

  protected function createTable($columns)
  {
    executeQuery($this->getCreateSql($columns, !$this->autoPrimary, false));
  }

  protected function getCreateSql($columns)
  {
    $query = array();

    foreach ($columns as $column) {
      $query[] = $this->createColumnAttributes($column);
    }

    if ($this->pkeys && !$this->autoPrimary) {
      $query[] = "PRIMARY KEY(" . implode(", ", $this->pkeys) . ")";
    }

    if ($this->uniques) {
      foreach ($this->uniques as $unique) {
        $query[] = "UNIQUE (" . implode(", ", $unique) . ")";
      }
    }

    $tblName = convert_to_tablename($this->mdlName);
    return "CREATE TABLE $tblName (" . implode(", ", $query) . ")";
  }

  public function addColumn()
  {
    $columns = getAddColumns($this->filePath);
    $tblName = convert_to_tablename($this->mdlName);

    if ($this->type === "upgrade") {
      $this->execAddColumn($columns);
    } else {
      $currentCols = getSchema($this->mdlName)->getColumns();
      $columnNames = array();
      foreach ($columns as $column) $columnNames[] = $column->name;

      foreach ($columnNames as $name) {
        if (isset($currentCols[$name])) unset($currentCols[$name]);
      }

      $this->dropColumnsAndRemakeTable($currentCols, $tblName);
    }
  }

  public function dropColumn()
  {
    if ($this->type === "upgrade") {
      $restore = $this->getRestoreFileName();
      if (is_file($restore)) unlink($restore);

      $columns = getDropColumns($this->filePath);
      $tblName = convert_to_tablename($this->mdlName);

      $schema = getSchema($this->mdlName);
      writeColumns($schema, $restore, $columns);
      $sColumns = $schema->getColumns();

      foreach ($sColumns as $name => $column) {
        if (in_array($column->name, $columns)) unset($sColumns[$name]);
      }

      foreach ($columns as $column) {
        if (!isset($sColumns[$column])) {
          $warning = "column '{$column}' of $tblName does not exist. (SKIP)";
          Sabel_Sakle_Task::warning($warning);
        }
      }

      $this->dropColumnsAndRemakeTable($sColumns, $tblName);
    } else {
      $this->restoreDropColumn();
    }
  }

  protected function changeColumnUpgrade($columns, $schema, $tblName)
  {
    $sColumns = $schema->getColumns();

    foreach ($columns as $column) {
      if (isset($sColumns[$column->name])) {
        $column = $this->alterChange($column, $sColumns[$column->name]);
        $sColumns[$column->name] = $column;
      }
    }

    $this->dropColumnsAndRemakeTable($sColumns, $tblName);
  }

  protected function changeColumnDowngrade($columns, $schema, $tblName)
  {
    $sColumns = $schema->getColumns();

    foreach ($columns as $column) {
      if (isset($sColumns[$column->name])) $sColumns[$column->name] = $column;
    }

    $this->dropColumnsAndRemakeTable($sColumns, $tblName);
  }

  protected function createColumnAttributes($col)
  {
    $line   = array();
    $line[] = $col->name;
    $line[] = $this->getDataType($col);

    if ($col->nullable === false) $line[] = "NOT NULL";
    $line[] = $this->getDefaultValue($col);

    return implode(" ", $line);
  }

  private function dropColumnsAndRemakeTable($columns, $tblName)
  {
    $driver = Sabel_DB_Migration_Manager::getDriver();
    $driver->begin();

    $query = $this->getCreateSql($columns);
    $query = str_replace(" TABLE $tblName", " TABLE stmp_{$tblName}", $query);
    executeQuery($query);

    $projection = array();
    foreach (array_keys($columns) as $key) $projection[] = $key;

    $projection = implode(", ", $projection);
    $query = "INSERT INTO stmp_{$tblName} SELECT $projection FROM $tblName";

    executeQuery($query);
    executeQuery("DROP TABLE $tblName");
    executeQuery("ALTER TABLE stmp_{$tblName} RENAME TO $tblName");

    $driver->loadTransaction()->commit();
  }

  private function alterChange($column, $current)
  {
    if ($column->type === null) {
      $column->type = $current->type;
    }

    if ($column->isString() && $column->max === null) {
      $column->max = $current->max;
    }

    if ($column->nullable === null) {
      $column->nullable = $current->nullable;
    }

    if ($column->default === _NULL) {
      $column->default = null;
    } elseif ($column->default === null) {
      $column->default = $current->default;
    }

    return $column;
  }

  private function getDataType($col)
  {
    if ($col->increment) {
      $this->autoPrimary = true;
      return "integer PRIMARY KEY";
    } else {
      if ($col->isString()) {
        return $this->types[$col->type] . "({$col->max})";
      } else {
        return $this->types[$col->type];
      }
    }
  }

  protected function getBooleanAttr($value)
  {
    $v = ($value === true) ? "true" : "false";
    return "DEFAULT " . $v;
  }
}
