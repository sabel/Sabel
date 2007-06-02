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

  public function createTable($cols)
  {
    $this->executeQuery($this->getCreateSql($cols));
  }

  protected function getCreateSql($cols)
  {
    $query = array();

    foreach ($cols as $col) {
      $query[] = $this->createColumnAttributes($col);
    }

    if (!empty($this->pkeys) && !$this->autoPrimary) {
      $query[] = "PRIMARY KEY(" . implode(", ", $this->pkeys) . ")";
    }

    if (!empty($this->uniques)) {
      foreach ($this->uniques as $column) {
        $query[] = "UNIQUE ({$column})";
      }
    }

    $tblName = convert_to_tablename($this->mdlName);
    return "CREATE TABLE $tblName (" . implode(", ", $query) . ")";
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
      $cols = $this->getDropColumns();
      $this->writeCurrentColumnsAttr($cols);
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

  protected function changeColumnUpgrade($cols, $schema, $tblName)
  {
    $columns = $this->getTableSchema()->getColumns();

    foreach ($cols as $col) {
      if (isset($columns[$col->name])) {
        $col = $this->setDifferenceAttr($col, $columns[$col->name]);
        $columns[$col->name] = $col;
      }
    }

    $this->dropColumnsAndRemakeTable($columns, $tblName);
  }

  protected function changeColumnDowngrade($cols, $schema, $tblName)
  {
    $columns = $this->getTableSchema()->getColumns();

    foreach ($cols as $col) {
      if (isset($columns[$col->name])) $columns[$col->name] = $col;
    }

    $this->dropColumnsAndRemakeTable($columns, $tblName);
  }

  protected function createColumnAttributes($col)
  {
    $line   = array();
    $line[] = $col->name;
    $line[] = $this->getDataType($col);

    if ($col->nullable === false) $line[] = "NOT NULL";

    $d = $col->default;
    if ($d === Sabel_DB_Migration_Tools_Parser::IS_EMPTY || $d === null) {
      return implode(" ", $line);
    }

    if ($col->isBool()) {
      $value  = ($d) ? "true" : "false";
      $line[] = "DEFAULT " . $value;
    } elseif ($col->isString()) {
      if ($d === "null") {
        $line[] = "DEFAULT NULL";
      } else {
        $line[] = "DEFAULT '{$d}'";
      }
    } else {
      $line[] = "DEFAULT " . $d;
    }

    return implode(" ", $line);
  }

  private function dropColumnsAndRemakeTable($columns, $tblName)
  {
    $driver = Sabel_DB_Migration_Manager::getDriver();
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

  private function setDifferenceAttr($col, $current)
  {
    if ($col->type === Sabel_DB_Migration_Tools_Parser::IS_EMPTY) {
      if ($current->isString()) $col->max  = $current->max;
      $col->type = $current->type;
    }

    if ($col->nullable === Sabel_DB_Migration_Tools_Parser::IS_EMPTY) {
      $col->nullable = $current->nullable;
    }

    if ($col->default === Sabel_DB_Migration_Tools_Parser::IS_EMPTY) {
      $col->default = $current->default;
    }

    return $col;
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
}
