<?php

/**
 * Sabel_DB_Oci_Migration
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Oci_Migration extends Sabel_DB_Abstract_Migration
{
  protected $types = array(Sabel_DB_Type::INT      => "number(10)",
                           Sabel_DB_Type::BIGINT   => "number(19)",
                           Sabel_DB_Type::SMALLINT => "number(5)",
                           Sabel_DB_Type::FLOAT    => "float(24)",
                           Sabel_DB_Type::DOUBLE   => "float(53)",
                           Sabel_DB_Type::BOOL     => "number(1)",
                           Sabel_DB_Type::STRING   => "varchar",
                           Sabel_DB_Type::TEXT     => "clob",
                           Sabel_DB_Type::DATETIME => "date",
                           Sabel_DB_Type::DATE     => "date");

  protected function create()
  {
    $tblName  = convert_to_tablename($this->mdlName);
    $accessor = $this->getAccessor();
    $tables   = $accessor->getTableList();

    if ($this->applyMode === "upgrade") {
      if (in_array($tblName, $tables)) {
        Sabel_Sakle_Task::warning("table '{$tblName}' already exists. (SKIP)");
      } else {
        $this->createTable($this->filePath);
      }
    } else {
      if (in_array($tblName, $tables)) {
        $this->dropSequence($accessor->get($tblName)->getSequenceColumn());
        $this->getDriver()->execute("DROP TABLE " . $tblName);
      } else {
        Sabel_Sakle_Task::warning("unknown table '{$tblName}'. (SKIP)");
      }
    }
  }

  protected function createTable($filePath)
  {
    $driver = $this->getDriver();
    $create = $this->getReader($filePath)->readCreate();
    $driver->execute($this->getCreateSql($create));

    foreach ($create->getColumns() as $column) {
      if ($column->increment) {
        $tblName = convert_to_tablename($this->mdlName);
        $seqName = strtoupper($tblName) . "_" . strtoupper($column->name) . "_SEQ";
        $driver->execute("CREATE SEQUENCE " . $seqName);
      } elseif ($column->isDate()) {
        $tblName = convert_to_tablename($this->mdlName);
        $driver->execute("COMMENT ON COLUMN {$tblName}.{$column->name} IS 'date'");
      }
    }
  }

  protected function getCreateSql($create)
  {
    $query   = array();
    $columns = $create->getColumns();
    $pkeys   = $create->getPrimaryKeys();
    $fkeys   = $create->getForeignKeys();
    $uniques = $create->getUniques();

    foreach ($columns as $column) {
      $line = $this->createColumnAttributes($column);
      if (isset($fkeys[$column->name])) {
        $fkey  = $fkeys[$column->name]->get();
        $line .= " REFERENCES {$fkey->refTable}({$fkey->refColumn})";
        if ($fkey->onDelete !== null && $fkey->onDelete !== "NO ACTION") {
          $line .= " ON DELETE " . $fkey->onDelete;
        }
      }

      $query[] = $line;
    }

    if ($pkeys) {
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

  protected function drop()
  {
    $restore = $this->getRestoreFileName();

    if ($this->applyMode === "upgrade") {
      if (is_file($restore)) unlink($restore);

      $schema = $this->getAccessor()->get(convert_to_tablename($this->mdlName));
      $writer = new Sabel_DB_Migration_Writer($restore);
      $writer->writeTable($schema);
      $this->getDriver()->execute("DROP TABLE " . $schema->getTableName());
      $this->dropSequence($schema->getSequenceColumn());
    } else {
      $this->createTable($restore);
    }
  }

  private function dropSequence($incCol)
  {
    if ($incCol !== null) {
      $tblName = convert_to_tablename($this->mdlName);
      $seqName = strtoupper($tblName) . "_" . strtoupper($incCol) . "_SEQ";
      Sabel_DB_Migration_Manager::getDriver()->execute("DROP SEQUENCE " . $seqName);
    }
  }

  protected function changeColumnUpgrade($columns, $schema)
  {
    $driver  = $this->getDriver();
    $tblName = $schema->getTableName();

    foreach ($columns as $column) {
      $current = $schema->getColumnByName($column->name);
      $line = $this->alterChange($column, $current);
      $driver->execute("ALTER TABLE $tblName MODIFY $line");
    }
  }

  protected function changeColumnDowngrade($columns, $schema)
  {
    $driver  = $this->getDriver();
    $tblName = $schema->getTableName();

    foreach ($columns as $column) {
      $current = $schema->getColumnByName($column->name);
      $line = $this->alterChange($column, $current);
      $driver->execute("ALTER TABLE $tblName MODIFY $line");
    }
  }

  protected function createColumnAttributes($col)
  {
    $line   = array();
    $line[] = $col->name;
    $line[] = $this->getTypeString($col);
    $line[] = $this->getDefaultValue($col);

    if (($nullable = $this->getNullableString($col)) !== "") {
      $line[] = $nullable;
    }

    return preg_replace("/[ ]{2,}/", " ", implode(" ", $line));
  }

  protected function alterChange($column, $current)
  {
    $line   = array();
    $line[] = $column->name;

    if ($current->isText() && $column->type !== null && !$column->isText()) {
      Sabel_Sakle_Task::warning("cannot modify lob column '{$current->name}'. (SKIP)");
    } elseif (!$current->isText()) {
      $col  = ($column->type === null) ? $current : $column;
      $type = $this->getTypeString($col, false);

      if ($col->isString()) {
        $max = ($column->max === null) ? $current->max : $column->max;
        $line[] = $type . "({$max})";
      } else {
        $line[] = $type;
      }
    }

    if (($d = $column->default) === _NULL) {
      $line[] = "DEFAULT NULL";
    } else {
      $cd = $current->default;

      if ($d === $cd) {
        $line[] = $this->getDefaultValue($current);
      } else {
        $this->valueCheck($column, $d);
        $line[] = $this->getDefaultValue($column);
      }
    }

    if ($current->nullable === true && $column->nullable === false) {
      $line[] = "NOT NULL";
    } elseif ($current->nullable === false && $column->nullable === true) {
      $line[] = "NULL";
    }

    return implode(" ", $line);
  }

  private function getTypeString($col, $withLength = true)
  {
    if ($col->isString() && $withLength) {
      return $this->types[$col->type] . "({$col->max})";
    } else {
      return $this->types[$col->type];
    }
  }

  private function getNullableString($column)
  {
    return ($column->nullable === false) ? "NOT NULL" : "";
  }

  private function valueCheck($column, $default)
  {
    if ($default === null) return true;

    if (($column->isBool() && !is_bool($default)) ||
        ($column->isNumeric() && !is_numeric($default))) {
      throw new Sabel_DB_Exception("invalid value for default.");
    } else {
      return true;
    }
  }

  protected function getBooleanAttr($value)
  {
    $v = ($value === true) ? "1" : "0";
    return "DEFAULT " . $v;
  }
}
