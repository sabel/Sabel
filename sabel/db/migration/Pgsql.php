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
class Sabel_DB_Migration_Pgsql extends Sabel_DB_Migration_Common
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

  public function createTable($cols)
  {
    $this->executeQuery($this->getCreateSql($cols));
  }

  protected function changeColumnUpgrade($cols, $schema, $tblName)
  {
    foreach ($cols as $col) {
      $current = $schema->getColumnByName($col->name);
      $this->alterChange($current, $col, $tblName);
    }
  }

  protected function changeColumnDowngrade($cols, $schema, $tblName)
  {
    foreach ($cols as $col) {
      $current = $schema->getColumnByName($col->name);
      $this->alterChange($current, $col, $tblName);
    }
  }

  protected function alterChange($current, $col, $tblName)
  {
    if ($col->type !== "EMPTY") {
      if ($current->type !== $col->type) {
        $type = $this->getDataType($col);
        $this->executeQuery("ALTER TABLE $tblName ALTER {$col->name} TYPE $type");
      } else {
        if ($current->isString() && $current->max !== $col->length) {
          $type = $this->getDataType($col);
          $this->executeQuery("ALTER TABLE $tblName ALTER {$col->name} TYPE $type");
        }
      }
    }

    if ($current->nullable !== $col->nullable) {
      if ($col->nullable === true) {
        $this->executeQuery("ALTER TABLE $tblName ALTER {$col->name} DROP NOT NULL");
      } elseif ($col->nullable === false) {
        $this->executeQuery("ALTER TABLE $tblName ALTER {$col->name} SET NOT NULL");
      }
    }

    if ($current->default !== $col->default) {
      if ($col->default === null) {
        $this->executeQuery("ALTER TABLE $tblName ALTER {$col->name} DROP DEFAULT");
      } elseif ($col->default !== "EMPTY") {
        if ($col->isBool()) {
          $default = ($col->default) ? "true" : "false";
        } elseif ($col->isString()) {
          $default = "'{$col->default}'";
        } else {
          $default = $col->default;
        }

        $this->executeQuery("ALTER TABLE $tblName ALTER {$col->name} SET DEFAULT $default");
      }
    }
  }

  protected function createColumnAttributes($col)
  {
    $line   = array();
    $line[] = $col->name;
    $line[] = $this->getDataType($col);

    if ($col->nullable === false) $line[] = "NOT NULL";

    $d = $col->default;

    if ($d !== "EMPTY") {
      if ($d === null) {
        $line[] = "DEFAULT NULL";
      } elseif ($col->isString()) {
        $line[] = "DEFAULT '{$d}'";
      } else {
        if ($col->isBool()) $d = ($d) ? "true" : "false";
        $line[] = "DEFAULT $d";
      }
    }

    return implode(" ", $line);
  }

  protected function getDataType($col)
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
}
