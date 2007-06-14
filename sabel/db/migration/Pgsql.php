<?php

/**
 * Sabel_DB_Migration_Pgsql
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Migration_Pgsql extends Sabel_DB_Migration_Base
{
  protected $types = array(Sabel_DB_Type::INT      => "integer",
                           Sabel_DB_Type::BIGINT   => "bigint",
                           Sabel_DB_Type::SMALLINT => "smallint",
                           Sabel_DB_Type::FLOAT    => "real",
                           Sabel_DB_Type::DOUBLE   => "double precision",
                           Sabel_DB_Type::BOOL     => "boolean",
                           Sabel_DB_Type::STRING   => "varchar",
                           Sabel_DB_Type::TEXT     => "text",
                           Sabel_DB_Type::DATETIME => "timestamp");

  protected function createTable($cols)
  {
    executeQuery($this->getCreateSql($cols));
  }

  protected function changeColumnUpgrade($columns, $schema, $tblName)
  {
    foreach ($columns as $column) {
      $current = $schema->getColumnByName($column->name);
      $this->alterChange($current, $column, $tblName);
    }
  }

  protected function changeColumnDowngrade($columns, $schema, $tblName)
  {
    foreach ($columns as $column) {
      $current = $schema->getColumnByName($column->name);
      $this->alterChange($current, $column, $tblName);
    }
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

  private function alterChange($current, $column, $tblName)
  {
    if ($column->type !== null || ($current->isString() && $column->max !== null)) {
      $this->changeType($current, $column, $tblName);
    }

    if ($column->nullable !== null) {
      $this->changeNullable($current, $column, $tblName);
    }

    if ($column->default !== $current->default) {
      $this->changeDefault($current, $column, $tblName);
    }
  }

  private function changeType($current, $column, $tblName)
  {
    if ($current->type !== $column->type && $column->type !== null) {
      $type = $this->getDataType($column);
      executeQuery("ALTER TABLE $tblName ALTER {$column->name} TYPE $type");
    } elseif ($current->isString() && $current->max !== $column->max) {
      $column->type = $current->type;
      if ($column->max === null) $column->max = 255;
      $type = $this->getDataType($column);
      executeQuery("ALTER TABLE $tblName ALTER {$column->name} TYPE $type");
    }
  }

  private function changeNullable($current, $column, $tblName)
  {
    if ($current->nullable === $column->nullable) return;

    if ($column->nullable) {
      executeQuery("ALTER TABLE $tblName ALTER {$column->name} DROP NOT NULL");
    } else {
      executeQuery("ALTER TABLE $tblName ALTER {$column->name} SET NOT NULL");
    }
  }

  private function changeDefault($current, $column, $tblName)
  {
    if ($column->default === _NULL) {
      executeQuery("ALTER TABLE $tblName ALTER {$column->name} DROP DEFAULT");
    } else {
      if ($column->isBool()) {
        $default = ($column->default) ? "true" : "false";
      } elseif ($column->isNumeric()) {
        $default = $column->default;
      } else {
        $default = "'{$column->default}'";
      }

      executeQuery("ALTER TABLE $tblName ALTER {$column->name} SET DEFAULT $default");
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
        throw new Exception("invalid data type for sequence.");
      }
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
