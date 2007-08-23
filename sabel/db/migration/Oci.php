<?php

/**
 * Sabel_DB_Migration_Oci
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Migration_Oci extends Sabel_DB_Migration_Base
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

  public function create()
  {
    $tblName = convert_to_tablename($this->mdlName);

    if ($this->applyMode === "upgrade") {
      $this->createUpgrade($tblName);
    } else {
      if (is_table_exists($tblName)) {
        $schema = getSchema($this->mdlName);
        executeQuery("DROP TABLE $tblName");
        $this->dropSequence($schema->getIncrementColumn());
      } else {
        Sabel_Sakle_Task::warning("unknown table '{$tblName}'. (SKIP)");
      }
    }
  }

  protected function createTable($cols)
  {
    executeQuery($this->getCreateSql($cols));

    foreach ($cols as $col) {
      if ($col->increment) {
        $tblName = convert_to_tablename($this->mdlName);
        $seqName = strtoupper($tblName) . "_" . strtoupper($col->name) . "_SEQ";
        executeQuery("CREATE SEQUENCE " . $seqName);
      } elseif ($col->isDate()) {
        $tblName = convert_to_tablename($this->mdlName);
        executeQuery("COMMENT ON COLUMN {$tblName}.{$col->name} IS 'date'");
      }
    }
  }

  protected function getCreateSql($columns)
  {
    $query = array();
    $fkeys = $this->fkeys;

    foreach ($columns as $column) {
      $line = $this->createColumnAttributes($column);
      if (isset($fkeys[$column->name])) {
        $fkey  = $fkeys[$column->name]->get();
        $line .= " REFERENCES {$fkey->refTable}({$fkey->refColumn})";
        if ($fkey->onDelete !== null) $line .= " ON DELETE " . $fkey->onDelete;
      }

      $query[] = $line;
    }

    if ($this->pkeys) {
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

  public function drop()
  {
    if ($this->applyMode === "upgrade") {
      $restore = $this->getRestoreFileName();
      if (is_file($restore)) unlink($restore);

      $schema  = getSchema($this->mdlName);
      $tblName = convert_to_tablename($this->mdlName);

      writeTable($schema, $restore);
      executeQuery("DROP TABLE $tblName");
      $this->dropSequence($schema->getIncrementColumn());
    } else {
      $path = $this->getRestoreFileName();
      $this->createTable(getCreate($path, $this));
    }
  }

  private function dropSequence($incCol)
  {
    if ($incCol !== null) {
      $tblName = convert_to_tablename($this->mdlName);
      $seqName = strtoupper($tblName) . "_" . strtoupper($incCol) . "_SEQ";
      executeQuery("DROP SEQUENCE " . $seqName);
    }
  }

  protected function changeColumnUpgrade($columns, $schema, $tblName)
  {
    foreach ($columns as $column) {
      $current = $schema->getColumnByName($column->name);
      $line = $this->alterChange($column, $current);
      executeQuery("ALTER TABLE $tblName MODIFY $line");
    }
  }

  protected function changeColumnDowngrade($columns, $schema, $tblName)
  {
    foreach ($columns as $column) {
      $current = $schema->getColumnByName($column->name);
      $line = $this->alterChange($column, $current);
      executeQuery("ALTER TABLE $tblName MODIFY $line");
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
      throw new Exception("invalid value for default.");
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
