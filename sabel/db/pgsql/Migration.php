<?php

/**
 * Sabel_DB_Pgsql_Migration
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Pgsql_Migration extends Sabel_DB_Abstract_Migration
{
  protected $types = array(Sabel_DB_Type::INT      => "integer",
                           Sabel_DB_Type::BIGINT   => "bigint",
                           Sabel_DB_Type::SMALLINT => "smallint",
                           Sabel_DB_Type::FLOAT    => "real",
                           Sabel_DB_Type::DOUBLE   => "double precision",
                           Sabel_DB_Type::BOOL     => "boolean",
                           Sabel_DB_Type::STRING   => "varchar",
                           Sabel_DB_Type::TEXT     => "text",
                           Sabel_DB_Type::DATETIME => "timestamp",
                           Sabel_DB_Type::DATE     => "date");

  protected function createTable($filePath)
  {
    $query = $this->getCreateSql($this->getReader($filePath)->readCreate());
    $this->getDriver()->execute($query);
  }

  protected function changeColumnUpgrade($columns, $schema)
  {
    $this->alterChange($columns, $schema);
  }

  protected function changeColumnDowngrade($columns, $schema)
  {
    $this->alterChange($columns, $schema);
  }

  protected function createColumnAttributes($column)
  {
    $line   = array();
    $line[] = $column->name;
    $line[] = $this->getDataType($column);

    if ($column->nullable === false) $line[] = "NOT NULL";
    $line[] = $this->getDefaultValue($column);

    return implode(" ", $line);
  }

  private function alterChange($columns, $schema)
  {
    $tblName = $schema->getTableName();
    $driver = $this->getDriver();
    $driver->begin();

    foreach ($columns as $column) {
      $current = $schema->getColumnByName($column->name);
      if ($column->type !== null || ($current->isString() && $column->max !== null)) {
        $this->changeType($current, $column, $tblName, $driver);
      }

      if ($column->nullable !== null) {
        $this->changeNullable($current, $column, $tblName, $driver);
      }

      if ($column->default !== $current->default) {
        $this->changeDefault($current, $column, $tblName, $driver);
      }
    }

    $driver->commit();
  }

  private function changeType($current, $column, $tblName, $driver)
  {
    if ($current->type !== $column->type && $column->type !== null) {
      $type = $this->getDataType($column);
      $driver->execute("ALTER TABLE $tblName ALTER {$column->name} TYPE $type");
    } elseif ($current->isString() && $current->max !== $column->max) {
      $column->type = $current->type;
      if ($column->max === null) $column->max = 255;
      $type = $this->getDataType($column);
      $driver->execute("ALTER TABLE $tblName ALTER {$column->name} TYPE $type");
    }
  }

  private function changeNullable($current, $column, $tblName, $driver)
  {
    if ($current->nullable === $column->nullable) return;

    if ($column->nullable) {
      $driver->execute("ALTER TABLE $tblName ALTER {$column->name} DROP NOT NULL");
    } else {
      $driver->execute("ALTER TABLE $tblName ALTER {$column->name} SET NOT NULL");
    }
  }

  private function changeDefault($current, $column, $tblName, $driver)
  {
    if ($column->default === _NULL) {
      $driver->execute("ALTER TABLE $tblName ALTER {$column->name} DROP DEFAULT");
    } else {
      if ($column->isBool()) {
        $default = ($column->default) ? "true" : "false";
      } elseif ($column->isNumeric()) {
        $default = $column->default;
      } else {
        $default = "'{$column->default}'";
      }

      $driver->execute("ALTER TABLE $tblName ALTER {$column->name} SET DEFAULT $default");
    }
  }

  private function getDataType($col)
  {
    if ($col->increment) {
      if ($col->isInt()) {
        return "serial";
      } elseif ($col->isBigint()) {
        return "bigserial";
      } else {
        throw new Sabel_DB_Exception("invalid data type for sequence.");
      }
    } elseif ($col->isString()) {
      return $this->types[$col->type] . "({$col->max})";
    } else {
      return $this->types[$col->type];
    }
  }

  protected function getBooleanAttr($value)
  {
    $v = ($value === true) ? "true" : "false";
    return "DEFAULT " . $v;
  }
}
