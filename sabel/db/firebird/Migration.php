<?php

/**
 * Sabel_DB_Firebird_Migration
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage firebird
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Firebird_Migration extends Sabel_DB_Base_Migration
{
  protected $search  = array('TYPE::INT',
                             'TYPE::SINT',
                             'TYPE::BINT',
                             'TYPE::STRING',
                             'TYPE::TEXT',
                             'TYPE::DATETIME',
                             'TYPE::FLOAT',
                             'TYPE::DOUBLE',
                             'TYPE::BOOL',
                             '__TRUE__',
                             '__FALSE__');

  protected $replace = array('integer',
                             'smallint',
                             'bigint',
                             'varchar',
                             'blob sub_type 1',
                             'timestamp',
                             'float',
                             'double',
                             'char(1)',
                             "1",
                             "0");

  public function addTable($tblName, $cmdQuery)
  {
    $cmdQuery = preg_replace("/[\n\r\f][ \t]*/", '', $cmdQuery);

    $exeQuery = array();
    foreach (explode(',', $cmdQuery) as $line) {
      list ($colName) = explode(' ', $line);
      $attr = trim(str_replace($colName, '', $line));

      if (strpos($attr, 'TYPE::BOOL') !== false) {
        $attr = $this->createBooleanAttr($attr);
      }

      $line = $colName . ' ' . $this->checkDefaultPosition($attr);
      if (strpos($line, '(INCREMENT)') !== false) {
        $this->createGenerator($tblName, $colName);
        $line = str_replace('(INCREMENT)', '', $line);
      }
      $exeQuery[] = $line;
    }

    $sch   = $this->search;
    $rep   = $this->replace;
    $query = str_replace($sch, $rep, implode(',', $exeQuery));
    $this->model->execute("CREATE TABLE $tblName ( " . $query . " )");
  }

  protected function checkDefaultPosition($attr)
  {
    list ($type) = explode(' ', $attr);
    $tmpAttr = trim(str_replace($type, '', $attr));
    if (strpos($tmpAttr, 'DEFAULT') !== false) {
      if (substr($tmpAttr, 0, 7) !== 'DEFAULT') {
        $defValue = trim(str_replace('DEFAULT', '', strstr($tmpAttr, 'DEFAULT')));
        $tmpAttr  = trim(str_replace(array('DEFAULT', $defValue), '', $tmpAttr));
        $attr = $type . ' DEFAULT ' . $defValue . ' ' . $tmpAttr;
      }
    }

    return $attr;
  }

  protected function createGenerator($tblName, $colName)
  {
    $query = "CREATE GENERATOR " . strtoupper($tblName . '_' . $colName . '_GEN');
    $this->model->execute($query);
  }

  public function deleteTable($tblName)
  {
    $model = $this->model;
    $key   = $model->getTableSchema()->getIncrementKey();

    if ($key !== null) {
      $model->execute("DROP GENERATOR {$tblName}_{$key}_GEN");
    }
    $model->execute("DROP TABLE $tblName");
  }

  public function renameTable($from, $to)
  {
    $schema = $this->model->getTableSchema();
    $cols   = $schema->getColumns();
    $pKey   = $schema->getPrimaryKey();
    $query  = array();

    foreach ($cols as $col) {
      $query[] = $this->createColumnAttribute($col);
    }

    if ($pKey !== null) {
      $key = (is_array($pKey)) ? implode(',', $pKey) : $pKey;
      $query[] = "PRIMARY KEY ( $key )";
    }

    $model = $this->model;
    $query = "CREATE TABLE $to ( " . implode(',', $query) . " )";
    $model->execute($query);

    $query = "INSERT INTO $to SELECT * FROM $from";
    $model->execute($query);

    $model->execute("DROP TABLE $from");
  }

  public function addColumn($tblName, $colName, $param)
  {
    if (strpos($param, 'TYPE::BOOL') !== false) {
      $param = $this->createBooleanAttr($param);
    }

    $sch  = $this->search;
    $rep  = $this->replace;
    $attr = str_replace($sch, $rep, $this->checkDefaultPosition($param));

    $this->model->execute("ALTER TABLE $tblName ADD $colName $attr");
  }

  public function deleteColumn($tblName, $colName)
  {
    $this->model->execute("ALTER TABLE $tblName DROP $colName");
  }

  public function changeColumn($tblName, $colName, $param)
  {
    if (strpos($param, 'TYPE::BOOL') !== false) {
      $param = $this->createBooleanAttr($param);
    }

    $sch  = $this->search;
    $rep  = $this->replace;
    $attr = str_replace($sch, $rep, $this->checkDefaultPosition($param));

    $schema = $this->model->getTableSchema();
    $cols   = $schema->getColumns();
    $pKey   = $schema->getPrimaryKey();
    $query  = array();

    foreach ($cols as $col) {
      if ($col->name === $colName) {
        $query[] = $colName . ' ' . $attr;
      } else {
        $query[] = $this->createColumnAttribute($col);
      }
    }

    if ($pKey !== null) {
      $key = (is_array($pKey)) ? implode(',', $pKey) : $pKey;
      $query[] = "PRIMARY KEY ( $key )";
    }

    $this->inout($tblName, implode(',', $query), '*');
  }

  public function renameColumn($tblName, $from, $to)
  {
    $this->model->execute("ALTER TABLE $tblName ALTER $from TO $to");
  }

  protected function createColumnAttribute($col)
  {
    $tmp   = array();
    $tmp[] = $col->name;

    switch ($col->type) {
      case Sabel_DB_Type_Const::INT:
        if ($col->max > 9E+18) {
          $tmp[] = 'bigint';
        } elseif ($col->max < 32768) {
          $tmp[] = 'smallint';
        } else {
          $tmp[] = 'integer';
        }
        break;
      case Sabel_DB_Type_Const::STRING:
        $tmp[] = "varchar({$col->max})";
        break;
      case Sabel_DB_Type_Const::BOOL:
        $tmp[] = "char(1)";
        break;
      default:
        $types = array(Sabel_DB_Type_Const::TEXT     => 'blob sub_type',
                       Sabel_DB_Type_Const::DATETIME => 'datetime',
                       Sabel_DB_Type_Const::FLOAT    => 'float',
                       Sabel_DB_Type_Const::DOUBLE   => 'double');

        $tmp[] = $types[$col->type];
        break;
    }

    if ($col->type !== Sabel_DB_Type_Const::BOOL && !$col->nullable) {
      $tmp[] = 'not null';
    }

    if ($col->default !== null) {
      switch ($col->type) {
        case Sabel_DB_Type_Const::INT:
        case Sabel_DB_Type_Const::FLOAT:
        case Sabel_DB_Type_Const::DOUBLE:
          $tmp[] = "default {$col->default}";
          break;
        case Sabel_DB_Type_Const::STRING:
        case Sabel_DB_Type_Const::TEXT:
        case Sabel_DB_Type_Const::DATETIME:
          $tmp[] = "default '{$col->default}'";
          break;
        case Sabel_DB_Type_Const::BOOL:
          $tmp[] = "default {$col->default}";
          break;
      }
    }

    return implode(' ', $tmp);
  }

  protected function inout($tblName, $createSQL, $selectCols)
  {
    $model = $this->model;

    // checking sql for create.
    $query = "CREATE TABLE sabel_firebird_creatable ( $createSQL )";
    $model->execute($query);
    $drop  = "DROP TABLE sabel_firebird_creatable";

    // check whether insert is possible.
    $query = "INSERT INTO sabel_firebird_creatable SELECT $selectCols FROM {$tblName}";
    try {
      $model->execute($query);
    } catch (Exception $e) {
      $model->execute($drop);
      throw $e;
    }

    $model->execute($drop);

    $tmpTable = $tblName . '_alter_tmp';
    $query    = "CREATE TABLE $tmpTable ( $createSQL )";
    $model->execute($query);

    $query = "INSERT INTO $tmpTable SELECT $selectCols FROM {$tblName}";

    try {
      $model->execute($query);
    } catch (Exception $e) {
      $model->execute("DROP TABLE $tmpTable");
      throw $e;
    }

    $model->execute("DROP TABLE $tblName");

    $query = "CREATE TABLE $tblName ( $createSQL )";
    $model->execute($query);

    $query = "INSERT INTO $tblName SELECT * FROM $tmpTable";
    $model->execute($query);
    $model->execute("DROP TABLE $tmpTable");
  }

  protected function createBooleanAttr($attr)
  {
    $attr = str_replace('NOT NULL', '', $attr);
    if (strpos($attr, 'DEFAULT') === false) {
      $attr .= ' DEFAULT __FALSE__';
    }
    return $attr;
  }
}
