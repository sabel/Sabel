<?php

/**
 * Sabel_DB_Ibase_Schema
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Ibase_Schema extends Sabel_DB_Abstract_Schema
{
  protected
    $types = array("7"   => "smallint",
                   "8"   => "integer",
                   "10"  => "float",
                   "12"  => "date",
                   "13"  => "time",
                   "14"  => "char",
                   "16"  => "bigint",
                   "27"  => "double",
                   "35"  => "timestamp",
                   "37"  => "varchar",
                   "261" => "blob");

  protected
    $genList      = 'SELECT RDB$GENERATOR_NAME FROM RDB$GENERATORS WHERE RDB$SYSTEM_FLAG = 0 OR RDB$SYSTEM_FLAG IS NULL',
    $priKeys      = 'SELECT RDB$FIELD_NAME FROM RDB$RELATION_CONSTRAINTS rel INNER JOIN RDB$INDEX_SEGMENTS seg
                     ON rel.RDB$INDEX_NAME = seg.RDB$INDEX_NAME WHERE rel.RDB$RELATION_NAME = \'%s\' AND
                     rel.RDB$CONSTRAINT_TYPE = \'PRIMARY KEY\'',
    $tableList    = 'SELECT RDB$RELATION_NAME FROM RDB$RELATIONS WHERE RDB$SYSTEM_FLAG = 0',
    $tableColumns = 'SELECT rf.RDB$FIELD_NAME, f.RDB$FIELD_TYPE, f.RDB$FIELD_SUB_TYPE, rf.RDB$NULL_FLAG,
                     f.RDB$CHARACTER_LENGTH, rf.RDB$DEFAULT_SOURCE FROM RDB$FIELDS f, RDB$RELATION_FIELDS rf
                     WHERE f.RDB$FIELD_NAME = rf.RDB$FIELD_SOURCE AND rf.RDB$RELATION_NAME = \'%s\'
                     ORDER BY rf.RDB$FIELD_POSITION ASC';

  protected
    $generators  = array(),
    $primaryKeys = array();

  public function getTableLists()
  {
    $tables = array();

    $rows = $this->execute($this->tableList);
    foreach ($rows as $row) {
      $tables[] = trim(strtolower($row['rdb$relation_name']));
    }

    return $tables;
  }

  public function getForeignKeys($tblName)
  {
    $tn  = strtoupper($tblName);

    $sql = 'SELECT seg.RDB$FIELD_NAME AS column_name, rc2.RDB$RELATION_NAME AS ref_table, '
         . 'seg2.RDB$FIELD_NAME AS ref_column, refc.RDB$DELETE_RULE, refc.RDB$UPDATE_RULE '
         . 'FROM RDB$RELATION_CONSTRAINTS rc '
         . 'INNER JOIN RDB$INDEX_SEGMENTS seg ON rc.RDB$INDEX_NAME = seg.RDB$INDEX_NAME '
         . 'INNER JOIN RDB$INDICES ind ON rc.RDB$INDEX_NAME = ind.RDB$INDEX_NAME '
         . 'INNER JOIN RDB$RELATION_CONSTRAINTS rc2 ON ind.RDB$FOREIGN_KEY = rc2.RDB$INDEX_NAME '
         . 'INNER JOIN RDB$INDEX_SEGMENTS seg2 ON ind.RDB$FOREIGN_KEY = seg2.RDB$INDEX_NAME '
         . 'INNER JOIN RDB$REF_CONSTRAINTS refc ON rc2.rdb$constraint_name = refc.RDB$CONST_NAME_UQ '
         . 'WHERE rc.RDB$CONSTRAINT_TYPE = \'FOREIGN KEY\' AND rc.RDB$RELATION_NAME = \'' . $tn . '\'';

    $rows = $this->execute($sql);
    if (empty($rows)) return null;

    $columns = array();
    foreach ($rows as $row) {
      $row = array_map("trim", $row);
      $column = strtolower($row["column_name"]);
      $columns[$column]["referenced_table"]  = strtolower($row["ref_table"]);
      $columns[$column]["referenced_column"] = strtolower($row["ref_column"]);
      $columns[$column]["on_delete"]         = $row['rdb$delete_rule'];
      $columns[$column]["on_update"]         = $row['rdb$update_rule'];
    }

    return $columns;
  }

  public function getUniques($tblName)
  {
    $tn  = strtoupper($tblName);

    $sql = 'SELECT seg.RDB$INDEX_NAME, seg.RDB$FIELD_NAME '
         . 'FROM RDB$RELATION_CONSTRAINTS rc '
         . 'INNER JOIN RDB$INDEX_SEGMENTS seg ON seg.RDB$INDEX_NAME = rc.RDB$INDEX_NAME '
         . 'WHERE rc.RDB$RELATION_NAME = \'' . $tn . '\' AND '
         . 'rc.RDB$CONSTRAINT_TYPE = \'UNIQUE\'';

    $rows = $this->execute($sql);
    if (empty($rows)) return null;

    $uniques = array();
    foreach ($rows as $row) {
      $key = trim($row['rdb$index_name']);
      $uniques[$key][] = trim(strtolower($row['rdb$field_name']));
    }

    return array_values($uniques);
  }

  protected function createGenerators()
  {
    if (!empty($this->generators)) return;

    $gens =& $this->generators;
    $rows = $this->execute($this->genList);

    foreach ($rows as $row) {
      $gens[] = trim($row['rdb$generator_name']);
    }
  }

  protected function createPrimaryKeys($tblName)
  {
    if (!empty($this->primaryKeys)) return;

    $keys =& $this->primaryKeys;
    $rows = $this->execute(sprintf($this->priKeys, $tblName));

    foreach ($rows as $row) {
      $keys[] = trim($row['rdb$field_name']);
    }
  }

  protected function createColumns($tblName)
  {
    $columns = array();
    $tblName = strtoupper($tblName);

    $this->createGenerators();
    $this->createPrimaryKeys($tblName);
    $rows = $this->execute(sprintf($this->tableColumns, $tblName));

    foreach ($rows as $row) {
      $colName = strtolower(trim($row['rdb$field_name']));
      $columns[$colName] = $this->makeColumnValueObject($row, $tblName);
    }

    return $columns;
  }

  protected function makeColumnValueObject($row, $tblName)
  {
    $fieldName = trim($row['rdb$field_name']);

    $co = new Sabel_DB_Schema_Column();
    $co->name = strtolower($fieldName);
    $co->nullable = ($row['rdb$null_flag'] === null);

    if ($this->isText($row)) {
      $type = "text";
    } else {
      $typeNum = $row['rdb$field_type'];
      $type    = $this->types[$typeNum];
    }

    if (($default = $row['rdb$default_source']) !== null) {
      $default = substr($default, 8);
    }

    if (!$this->isBoolean($co, $type, $row)) {
      if ($this->isFloat($type)) $type = $this->getFloatType($type);
      Sabel_DB_Type_Setter::send($co, $type);
    }

    $this->setIncrement($co, $fieldName, $tblName);
    $this->setPrimaryKey($co, $fieldName);

    $this->setDefaultValue($co, $this->cleaningValue($co, $default));
    if ($co->isString()) $this->setLength($co, $row);

    return $co;
  }

  protected function isText($row)
  {
    return ($row['rdb$field_type'] === 261 && $row['rdb$field_sub_type'] === 1);
  }

  protected function isBoolean($co, $type, $row)
  {
    if ($type === "char" && $row['rdb$character_length'] === 1) {
      $co->type = Sabel_DB_Type::BOOL;
      return true;
    } else {
      return false;
    }
  }

  protected function isFloat($type)
  {
    return ($type === "float" || $type === "double");
  }

  protected function getFloatType($type)
  {
    return ($type === "float") ? "float" : "double";
  }

  protected function setIncrement($co, $fieldName, $tblName)
  {
    $genName = "{$tblName}_{$fieldName}_GEN";
    $co->increment = (in_array($genName, $this->generators));
  }

  protected function setPrimaryKey($co, $fieldName)
  {
    $co->primary = (in_array($fieldName, $this->primaryKeys));
    if ($co->primary) $co->nullable = false;
  }

  protected function setLength($co, $row)
  {
    $co->max = $row['rdb$character_length'];
  }

  private function cleaningValue($co, $default)
  {
    if ($default === null) return null;

    if (!$co->isNumeric() && !$co->isBool()) {
      $default = substr($default, 1, -1);
    }

    return ($default === "NULL") ? null : $default;
  }
}
