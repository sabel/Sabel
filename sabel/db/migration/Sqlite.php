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

  protected $autoPrimary = false;

  public function createTable($cols)
  {
    $this->executeQuery($this->getCreateSql($cols));
  }

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
      $columns = $this->getTableSchema()->getColumns();
      foreach ($cols as $col) {
        if (isset($columns[$col->name])) unset($columns[$col->name]);
      }

      $this->dropColumnsAndRemakeTable($columns, $tblName);
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

      $columns = $this->getTableSchema()->getColumns();
      foreach ($cols as $col) {
        if (isset($columns[$col])) unset($columns[$col]);
      }

      $this->dropColumnsAndRemakeTable($columns, $tblName);
    } else {
      $cols = $this->createColumns($this->getRestoreFileName());
      foreach ($cols as $col) {
        $line = $this->createColumnAttributes($col);
        $this->executeQuery("ALTER TABLE $tblName ADD " . $line);
      }
    }
  }

  public function dropColumnsAndRemakeTable($columns, $tblName)
  {
    $driver = $this->driver;
    $driver->begin($driver->getConnectionName());

    $query = $this->getCreateSql($columns);
    $query = str_replace(" TABLE $tblName", " TABLE stmp_{$tblName}", $query);
    $this->executeQuery($query);

    $projection = array();
    foreach (array_keys($columns) as $key) $projection[] = $key;

    $projection = implode(", ", $projection);
    $query = "INSERT INTO stmp_{$tblName} SELECT $projection FROM $tblName";
    $this->executeQuery($query);

    $this->executeQuery("DROP TABLE $tblName");

    $query = "ALTER TABLE stmp_{$tblName} RENAME TO $tblName";
    $this->executeQuery($query);

    $driver->loadTransaction()->commit();
  }

  protected function changeColumnUpgrade($cols, $schema, $tblName)
  {
    $this->alterChange($cols, $schema, $tblName);
  }

  protected function changeColumnDowngrade($cols, $schema, $tblName)
  {
    $this->alterChange($cols, $schema, $tblName);
  }

  protected function alterChange($cols, $schema, $tblName)
  {
    $columns = $this->getTableSchema()->getColumns();
    foreach ($cols as $col) {
      if (isset($columns[$col->name])) $columns[$col->name] = $col;
    }

    $this->dropColumnsAndRemakeTable($columns, convert_to_tablename($this->mdlName));
  }

  protected function createColumnAttributes($col)
  {
    $line   = array();
    $line[] = $col->name;
    $line[] = $this->getDataType($col);

    if ($col->nullable === false) $line[] = "NOT NULL";

    if ($col->default !== "EMPTY" && $col->default !== null) {
      if (is_bool($col->default)) {
        $value  = ($col->default) ? "true" : "false";
        $line[] = "DEFAULT " . $value;
      } elseif ($col->isString()) {
        $d = $col->default;
        if ((substr($d, 0, 1) !== "'" || substr($d, -1, 1) !== "'") && $d !== "null") {
          $line[] = "DEFAULT '{$d}'";
        } else {
          $line[] = "DEFAULT $d";
        }
      } else {
        $line[] = "DEFAULT " . $col->default;
      }
    }

    return implode(" ", $line);
  }

  protected function getDataType($col)
  {
    if ($col->increment) {
      $this->sqlPrimary = true;
      return "integer PRIMARY KEY";
    } else {
      if ($col->isString()) {
        return $this->types[$col->type] . "({$col->max})";
      } else {
        return $this->types[$col->type];
      }
    }
  }
}
