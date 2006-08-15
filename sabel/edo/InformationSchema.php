<?php

require_once('RecordObject.php');

class Edo_Type
{
  const INT       = 'INT';
  const STRING    = 'STRING';
  const BLOB      = 'BLOB';
  const DATE      = 'DATE';
  const TIMESTAMP = 'TIMESTAMP';  // pgsql timestamp || (mysql timestamp || datetime)
}

class Edo_InformationSchema_Table
{
  protected $tableName = '';
  protected $columns   = array();

  public function __construct($name, $columns)
  {
    $this->tableName = $name;
    $this->columns   = $columns;
  }

  public function getTableName()
  {
    return $this->tableName;
  }

  public function getColumns()
  {
    return $this->columns;
  }

  public function getColumnByName($name)
  {
    return $this->columns[$name];
  }
}

class Edo_InformationSchema
{
  protected $is = null;

  public function __construct($connectName, $schema)
  {
    $className = 'Edo_' . Sabel_Edo_DBConnection::getDB($connectName) . '_InformationSchema';
    $this->is  = new $className($connectName, $schema);
  }

  public function getTables()
  {
    return $this->is->getTables();
  }

  public function getTable($name)
  {
    return $this->is->getTable($name);
  }

  protected function createColumns($table)
  {
    return $this->is->createColumns($table);
  }

  protected function createColumn($table, $column = null)
  {
    return $this->is->createColumn($table, $column);
  }
}

class Edo_MysqlPgsql_InformationSchema
{
  protected $recordObj = null;
  protected $schema    = '';

  public function __construct($connectName, $schema)
  {
    $this->schema    = $schema;
    $this->recordObj = new Sabel_Edo_CommonRecord();
    $this->recordObj->setEDO($connectName);
  }
  
  public function getTables()
  {
    $sql = "SELECT * FROM information_schema.tables WHERE table_schema = '{$this->schema}'";

    $tables = array();
    foreach ($this->recordObj->execute($sql) as $val) {
      $data      = array_change_key_case($val->toArray());
      $tableName = $data['table_name'];
      $tables[]  = new Edo_InformationSchema_Table($tableName, $this->createColumns($tableName));
    }

    return $tables;
  }

  public function getTable($name)
  {
    return new Edo_InformationSchema_Table($name, $this->createColumns($name));
  }

  protected function createColumns($table)
  {
    $sql = "SELECT * FROM information_schema.columns WHERE table_name = '{$table}'";

    $columns = array();
    foreach ($this->recordObj->execute($sql) as $val) {
      $data = array_change_key_case($val->toArray());
      $columnName = $data['column_name']; 
      $columns[$columnName] = $this->createColumn($table, $columnName);
    }
    return $columns;
  }

  protected function createColumn($table, $column = null)
  {
    if (is_null($column)) return $this->createColumns($table);

    $sql  = "SELECT * FROM information_schema.columns ";
    $sql .= "WHERE table_name = '{$table}' AND column_name = '{$column}'";

    $res = $this->recordObj->execute($sql);
    return $this->makeColumnValueObject(array_change_key_case($res[0]->toArray()));
  }

  protected function makeColumnValueObject($columnRecord)
  {
    $co = new ColumnObject();
    $co->name    = $columnRecord['column_name'];
    $co->default = $columnRecord['column_default'];
    $co->notNull = ($columnRecord['is_nullable'] === 'NO');

    if (is_string($sql = $this->addIncrementInfo($co, $columnRecord))) {
      $co->increment = (count($this->recordObj->execute($sql)) > 0);
    }

    if (is_array($sqls = $this->addCommentInfo($co, $columnRecord))) {
      $oid = $this->recordObj->execute($sqls[0]);
      $pos = $this->recordObj->execute($sqls[1]);

      $comment = $this->recordObj->execute(sprintf($sqls[2], $oid[0]->relfilenode, $pos[0]->ordinal_position));
      $co->comment = $comment[0]->col_description;
    }

    $type = $columnRecord['data_type'];

    if (in_array($type, $this->getNumericTypes())) {
      $co->type = Edo_Type::INT;
      $co->convertToEdoInteger($columnRecord['data_type']);
    }

    if (in_array($type, $this->getStringTypes())) {
      $co->type = Edo_Type::STRING;
      $this->addStringLength($co, $columnRecord);
    }

    return $co;
  }
}

class Edo_Mysql_InformationSchema extends Edo_MysqlPgsql_InformationSchema
{
  public function getNumericTypes()
  {
    return array('tinyint', 'smallint', 'mediumint', 'int', 'bigint');
  }

  public function getStringTypes()
  {
    return array('text', 'mediumtext', 'varchar', 'char');
  }

  public function getBinaryTypes()
  {
    return array('blob', 'mediumblob', 'longblob');
  }

  public function addCommentInfo($co, $columnRecord)
  {
    $co->comment = $columnRecord['column_comment'];
  }

  public function addIncrementInfo($co, $columnRecord)
  {
    $co->increment = ($columnRecord['extra'] === 'auto_increment');
  }

  public function addStringLength($co, $columnRecord)
  {
    $co->maxLength = $columnRecord['character_maximum_length'];
  }
}

class Edo_Pgsql_InformationSchema extends Edo_MysqlPgsql_InformationSchema
{
  public function getNumericTypes()
  {
    return array('smallint', 'integer', 'bigint');
  }

  public function getStringTypes()
  {
    return array('text', 'character varying', 'varchar', 'character', 'char');
  }

  public function getBinaryTypes()
  {
    return array('bytea');
  }

  public function addCommentInfo($co, $columnRecord)
  {
    $sqls   = array();
    $sqls[] = "SELECT relfilenode FROM pg_class WHERE relname = '{$columnRecord['table_name']}'";

    $sql  = "SELECT ordinal_position FROM information_schema.columns ";
    $sql .= "WHERE table_name = '{$columnRecord['table_name']}' AND column_name = '{$co->name}'";
    $sqls[] = $sql;

    $sqls[] = "SELECT col_description FROM col_description(%s, %s)";

    return $sqls;
  }

  public function addIncrementInfo($co, $columnRecord)
  {
    $sql  = "SELECT * FROM pg_statio_user_sequences ";
    $sql .= "WHERE relname = '{$columnRecord['table_name']}_{$co->name}_seq'";

    return $sql;
  }

  public function addStringLength($co, $columnRecord)
  {
    $maxlen = $columnRecord['character_maximum_length'];
    $co->maxLength = (isset($maxlen)) ? $maxlen : 65535;
  }
}

class ColumnObject
{
  protected $data = array();

  public function __set($key, $val)
  {
    $this->data[$key] = $val;
  }

  public function __get($key)
  {
    return $this->data[$key];
  }

  public function convertToEdoInteger($type)
  {
    $data =& $this->data;

    switch($type) {
      case 'tinyint':
        $data['maxValue'] =  127;
        $data['minValue'] = -128;
        break;
      case 'smallint':
        $data['maxValue'] =  32767;
        $data['minValue'] = -32768;
        break;
      case 'mediumint':
        $data['maxValue'] =  8388607;
        $data['minValue'] = -8388608;
        break;
      case 'int':
      case 'integer':
        $data['maxValue'] =  2147483647;
        $data['minValue'] = -2147483648;
        break;
      case 'bigint':
        $data['maxValue'] =  9223372036854775807;
        $data['minValue'] = -9223372036854775808;
        break;
    }
  }
}
