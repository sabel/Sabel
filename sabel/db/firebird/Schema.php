<?php

//Sabel::using('Sabel_DB_Base_Schema');

/**
 * Sabel_DB_Firebird_Schema
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage firebird
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Firebird_Schema extends Sabel_DB_Base_Schema
{
  protected
    $types = array('7'   => 'smallint',
                   '8'   => 'integer',
                   '10'  => 'float',
                   '12'  => 'date',
                   '13'  => 'time',
                   '14'  => 'char',
                   '16'  => 'bigint',
                   '27'  => 'double',
                   '35'  => 'timestamp',
                   '37'  => 'varchar',
                   '40'  => 'cstring',
                   '261' => 'blob');

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

  private
    $generators  = array(),
    $primaryKeys = array();

  public function __construct($connectName, $schema = null)
  {
    $this->driver = Sabel_DB_Connection::getDriver($connectName);
  }

  public static function convertToFirebirdType($schemaType)
  {
    switch ($schemaType) {
      case Sabel_DB_Type_Const::INT:
        return 8;
      case Sabel_DB_Type_Const::FLOAT:
        return 10;
      case Sabel_DB_Type_Const::DOUBLE:
        return 27;
      case Sabel_DB_Type_Const::STRING:
        return 37;
      case Sabel_DB_Type_Const::DATETIME:
        return 35;
      case Sabel_DB_Type_Const::DATE:
        return 12;
      case Sabel_DB_Type_Const::TIME:
        return 13;
      case Sabel_DB_Type_Const::BOOL:
        return 14;
      case Sabel_DB_Type_Const::TEXT:
      case Sabel_DB_Type_Const::BYTE:
        return 261;
    }
  }

  public function getTableNames()
  {
    $tables = array();

    $this->driver->execute($this->tableList);
    foreach ($this->driver->getResultSet() as $row) {
      $tables[] = strtolower($row['rdb$relation_name']);
    }
    return $tables;
  }

  public function getTables()
  {
    $tables = array();
    foreach ($this->getTableNames() as $tblName) {
      $tables[$tblName] = $this->getTable($tblName);
    }
    return $tables;
  }

  protected function createGenerators()
  {
    if (!empty($this->generators)) return null;

    $gens =& $this->generators;
    $this->driver->execute($this->genList);
    foreach ($this->driver->getResultSet() as $row) {
      $gens[] = trim($row['rdb$generator_name']);
    }
  }

  protected function createPrimaryKeys($tblName)
  {
    if (!empty($this->primaryKeys)) return null;

    $keys =& $this->primaryKeys;
    $this->driver->execute(sprintf($this->priKeys, $tblName));
    foreach ($this->driver->getResultSet() as $row) {
      $keys[] = trim($row['rdb$field_name']);
    }
  }

  protected function createColumns($table)
  {
    $columns = array();
    $tblName = strtoupper($table);

    $this->createGenerators();
    $this->createPrimaryKeys($tblName);

    $this->driver->execute(sprintf($this->tableColumns, $tblName));
    foreach ($this->driver->getResultSet() as $row) {
      $colName = strtolower(trim($row['rdb$field_name']));
      $columns[$colName] = $this->makeColumnValueObject($row, $tblName);
    }
    return $columns;
  }

  protected function makeColumnValueObject($row, $tblName)
  {
    $fieldName = trim($row['rdb$field_name']);

    $co = new Sabel_ValueObject();
    $co->name     = strtolower($fieldName);
    $co->nullable = ($row['rdb$null_flag'] === null);

    if ($this->isText($row)) {
      $type = 'text';
    } else {
      $typeNum = $row['rdb$field_type'];
      $type    = $this->types[$typeNum];
    }

    $conName = get_db_tables(strtolower($tblName));
    $default = $row['rdb$default_source'];
    $co->default = ($default === null) ? null : $this->getDefaultValue($default, $conName);

    if (!$this->isBool($co, $type, $row)) {
      if ($this->isFloat($type)) $type = $this->getFloatType($type);
      Sabel_DB_Type_Setter::send($co, $type);
      $this->setIncrement($co, $fieldName, $tblName);
      $this->setPrimaryKey($co, $fieldName);
    }

    if ($co->type === Sabel_DB_Type_Const::STRING) $this->setLength($co, $row);
    return $co;
  }

  protected function isText($row)
  {
    return ($row['rdb$field_type'] === 261 && $row['rdb$field_sub_type'] === 1);
  }

  protected function isBool($co, $type, $row)
  {
    if ($type === 'char' && $row['rdb$character_length'] === 1) {
      if ($co->default === 0 || $co->default === 1) {
        $co->type = Sabel_DB_Type_Const::BOOL;
        return true;
      }
    }
    return false;
  }

  protected function isFloat($type)
  {
    return ($type === 'float' || $type === 'double');
  }

  protected function getFloatType($type)
  {
    return ($type === 'float') ? 'float' : 'double';
  }

  protected function setIncrement($co, $fieldName, $tblName)
  {
    $genName = $tblName . '_' . $fieldName . '_GEN';
    $co->increment = (in_array($genName, $this->generators));
  }

  protected function setPrimaryKey($co, $fieldName)
  {
    $co->primary = (in_array($fieldName, $this->primaryKeys));
  }

  protected function setDefault($co, $default)
  {
    $co->default = ($default === null) ? null : $this->getDefault($default);
  }

  protected function getDefaultValue($default, $conName)
  {
    $con  = Sabel_DB_Connection::getConnection($conName);
    $info = ibase_blob_info($con, $default);
    $blob = ibase_blob_open($con, $default);
    $val  = substr(ibase_blob_get($blob, $info[0]), 8);

    return (is_numeric($val)) ? (int)$val : substr($val, 1, -1);
  }

  protected function setLength($co, $row)
  {
    $co->max = $row['rdb$character_length'];
  }
}
