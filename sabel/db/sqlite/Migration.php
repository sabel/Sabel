<?php

/**
 * Sabel_DB_Sqlite_Migration
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Sqlite_Migration extends Sabel_DB_Abstract_Migration
{
  protected $types = array(Sabel_DB_Type::INT      => "int",
                           Sabel_DB_Type::BIGINT   => "bigint",
                           Sabel_DB_Type::SMALLINT => "smallint",
                           Sabel_DB_Type::FLOAT    => "float",
                           Sabel_DB_Type::DOUBLE   => "double",
                           Sabel_DB_Type::BOOL     => "boolean",
                           Sabel_DB_Type::STRING   => "varchar",
                           Sabel_DB_Type::TEXT     => "text",
                           Sabel_DB_Type::DATETIME => "datetime",
                           Sabel_DB_Type::DATE     => "date");

  protected function createTable($filePath)
  {
    $create  = $this->getReader($filePath)->readCreate();
    $columns = $create->getColumns();
    $pkeys   = $create->getPrimaryKeys();
    $uniques = $create->getUniques();
    $query   = $this->makeCreateSql($columns, $pkeys, $uniques);

    $this->getDriver()->execute($query);
  }

  protected function addColumn()
  {
    $columns = $this->getReader()->readAddColumn()->getColumns();

    if ($this->applyMode === "upgrade") {
      $this->execAddColumn($columns);
    } else {
      $schema   = $this->getAccessor()->get(convert_to_tablename($this->mdlName));
      $tblName  = $schema->getTableName();
      $currents = $schema->getColumns();

      foreach ($columns as $column) {
        $name = $column->name;
        if (isset($currents[$name])) unset($currents[$name]);
      }

      $this->dropColumnsAndRemakeTable($currents, $schema);
    }
  }

  protected function dropColumn()
  {
    $restore = $this->getRestoreFileName();

    if ($this->applyMode === "upgrade") {
      if (is_file($restore)) unlink($restore);

      $columns  = $this->getReader()->readDropColumn()->getColumns();
      $schema   = $this->getAccessor()->get(convert_to_tablename($this->mdlName));
      $tblName  = $schema->getTableName();
      $sColumns = $schema->getColumns();
      $colNames = $schema->getColumnNames();

      $writer = new Sabel_DB_Migration_Writer($restore);
      $writer->writeColumns($schema, $columns);
      $writer->close();

      foreach ($columns as $column) {
        if (isset($sColumns[$column])) {
          unset($sColumns[$column]);
        } else {
          $warning = "column '{$column}' does not exist. (SKIP)";
          Sabel_Sakle_Task::warning($warning);
        }
      }

      $this->dropColumnsAndRemakeTable($sColumns, $schema);
    } else {
      $columns = $this->getReader($restore)->readAddColumn()->getColumns();
      $this->execAddColumn($columns);
    }
  }

  protected function changeColumnUpgrade($columns, $schema)
  {
    $sColumns = $schema->getColumns();

    foreach ($columns as $column) {
      if (isset($sColumns[$column->name])) {
        $column = $this->alterChange($column, $sColumns[$column->name]);
        $sColumns[$column->name] = $column;
      }
    }

    $this->dropColumnsAndRemakeTable($sColumns, $schema);
  }

  protected function changeColumnDowngrade($columns, $schema)
  {
    $sColumns = $schema->getColumns();

    foreach ($columns as $column) {
      if (isset($sColumns[$column->name])) $sColumns[$column->name] = $column;
    }

    $this->dropColumnsAndRemakeTable($sColumns, $schema);
  }

  protected function createColumnAttributes($col)
  {
    $line = array($col->name);

    if ($col->increment) {
      $line[] = "integer PRIMARY KEY";
    } elseif ($col->isString()) {
      $line[] = $this->types[$col->type] . "({$col->max})";
    } else {
      $line[] = $this->types[$col->type];
    }

    if ($col->nullable === false) $line[] = "NOT NULL";
    $line[] = $this->getDefaultValue($col);

    return implode(" ", $line);
  }

  private function dropColumnsAndRemakeTable($columns, $schema)
  {
    $driver  = $this->getDriver();
    $tblName = $schema->getTableName();
    $pkeys   = $schema->getPrimaryKey();
    $uniques = $schema->getUniques();
    $query   = $this->makeCreateSql($columns, $pkeys, $uniques);
    $query   = str_replace(" TABLE $tblName", " TABLE stmp_{$tblName}", $query);

    $driver->begin();
    $driver->execute($query);

    $projection = array();
    foreach (array_keys($columns) as $key) $projection[] = $key;

    $projection = implode(", ", $projection);
    $query = "INSERT INTO stmp_{$tblName} SELECT $projection FROM $tblName";

    $driver->execute($query);
    $driver->execute("DROP TABLE $tblName");
    $driver->execute("ALTER TABLE stmp_{$tblName} RENAME TO $tblName");
    $driver->commit();
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

  private function makeCreateSql($columns, $pkeys, $uniques)
  {
    $query  = array();
    $hasSeq = false;

    foreach ($columns as $column) {
      if ($column->increment) $hasSeq = true;
      $query[] = $this->createColumnAttributes($column);
    }

    if ($pkeys && !$hasSeq) {
      $query[] = "PRIMARY KEY(" . implode(", ", $pkeys) . ")";
    }

    if ($uniques) {
      foreach ($uniques as $unique) {
        $query[] = "UNIQUE (" . implode(", ", $unique) . ")";
      }
    }

    $tblName = convert_to_tablename($this->mdlName);
    return "CREATE TABLE $tblName (" . implode(", ", $query) . ")";
  }

  protected function getBooleanAttr($value)
  {
    $v = ($value === true) ? "true" : "false";
    return "DEFAULT " . $v;
  }
}
