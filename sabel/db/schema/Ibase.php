<?php

/**
 * Sabel_DB_Schema_Ibase
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Schema_Ibase extends Sabel_DB_Schema_Base
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
                   "40"  => "cstring",
                   "261" => "blob");

  protected
    $genList      = 'SELECT RDB$GENERATOR_NAME FROM RDB$GENERATORS WHERE RDB$SYSTEM_FLAG = 0',
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

  public static function convertToIbaseType($schemaType)
  {
    switch ($schemaType) {
      case Sabel_DB_Type::INT:
        return 8;
      case Sabel_DB_Type::FLOAT:
        return 10;
      case Sabel_DB_Type::DOUBLE:
        return 27;
      case Sabel_DB_Type::STRING:
        return 37;
      case Sabel_DB_Type::DATETIME:
        return 35;
      case Sabel_DB_Type::DATE:
        return 12;
      case Sabel_DB_Type::TIME:
        return 13;
      case Sabel_DB_Type::BOOL:
        return 14;
      case Sabel_DB_Type::TEXT:
      case Sabel_DB_Type::BYTE:
        return 261;
    }
  }

  public function getTableLists()
  {
    $tables = array();

    $rows = $this->execute($this->tableList);
    foreach ($rows as $row) {
      $tables[] = strtolower($row['rdb$relation_name']);
    }

    return $tables;
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
    $co->name     = strtolower($fieldName);
    $co->nullable = ($row['rdb$null_flag'] === null);

    if ($this->isText($row)) {
      $type = "text";
    } else {
      $typeNum = $row['rdb$field_type'];
      $type    = $this->types[$typeNum];
    }

    if (($default = $row['rdb$default_source']) !== null) {
      $tmp = substr($default, 8);
      $default = (is_numeric($tmp)) ? $tmp : substr($tmp, 1, -1);
    }

    if ($this->isBool($co, $type, $row, $default)) {
    } else {
      if ($this->isFloat($type)) $type = $this->getFloatType($type);
      Sabel_DB_Type_Setter::send($co, $type);
    }

    $this->setIncrement($co, $fieldName, $tblName);
    $this->setPrimaryKey($co, $fieldName);
    $this->setDefaultValue($co, $default);

    if ($co->isString()) $this->setLength($co, $row);

    return $co;
  }

  protected function isText($row)
  {
    return ($row['rdb$field_type'] === 261 && $row['rdb$field_sub_type'] === 1);
  }

  protected function isBool($co, $type, $row, $default)
  {
    if ($type === "char" && $row['rdb$character_length'] === 1) {
      if ($default === "0" || $default === "1") {
        $co->type = Sabel_DB_Type::BOOL;
        return true;
      }
    }

    return false;
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
  }

  protected function setLength($co, $row)
  {
    $co->max = $row['rdb$character_length'];
  }
}
