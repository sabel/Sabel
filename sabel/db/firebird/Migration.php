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
class Sabel_DB_Firebird_Migration
{
  protected $search  = array('TYPE::INT(INCREMENT)',
                             'TYPE::BINT(INCREMENT)',
                             'TYPE::INT',
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

  protected $replace = array('integer primary key',
                             'integer primary key',
                             'int',
                             'smallint',
                             'bigint',
                             'varchar',
                             'blob sub_type 1',
                             'timestamp',
                             'float',
                             'double',
                             'boolean',
                             "1",
                             "0");

  protected $model = null;

  public function setModel($tblName)
  {
    $mdlName     = convert_to_modelname($tblName);
    $this->model = @MODEL($mdlName);
  }

  public function addTable($tblName, $cmdQuery)
  {
    $cmdQuery = preg_replace("/[\n\r\f]/", '', $cmdQuery);

    $exeQuery = array();
    foreach (explode(',', $cmdQuery) as $line) {
      $line = trim($line);
      list ($colName) = explode(' ', $line);
      $attr = str_replace($colName, '', $line);
      $exeQuery[] = $colName . ' ' . $this->incrementFilter($attr);
    }

    $sch   = $this->search;
    $rep   = $this->replace;
    $query = str_replace($sch, $rep, implode(',', $exeQuery));
    $this->model->execute("CREATE TABLE $tblName ( " . $query . " )");
  }

  public function deleteTable($tblName)
  {
    $this->model->execute("DROP TABLE $tblName");
  }

  public function renameTable($from, $to)
  {
    $this->model->execute("ALTER TABLE $from RENAME TO $to");
  }

  public function addColumn($tblName, $colName, $param)
  {
    $sch  = $this->search;
    $rep  = $this->replace;
    $attr = str_replace($sch, $rep, $param);

    $this->model->execute("ALTER TABLE $tblName ADD $colName $attr");
  }

  public function deleteColumn($tblName, $colName)
  {
    $cols = $this->model->getTableSchema()->getColumns();
    unset($cols[$colName]);

    $query = array();
    foreach ($cols as $col) {
      $query[] = $this->createColumnAttribute($col);
    }

    $this->inout($tblName, implode(',', $query), implode(',', array_keys($cols)));
  }

  public function changeColumn($tblName, $colName, $param)
  {
    $attr = $this->incrementFilter($param);
    $sch  = $this->search;
    $rep  = $this->replace;
    $attr = $colName . ' ' .str_replace($sch, $rep, $param);

    $cols  = $this->model->getTableSchema()->getColumns();
    $query = array();

    foreach ($cols as $col) {
      if ($col->name === $colName) {
        $query[] = $attr;
        continue;
      }

      $query[] = $this->createColumnAttribute($col);
    }

    $this->inout($tblName, implode(',', $query), '*');
  }

  public function renameColumn($tblName, $from, $to)
  {
    $cols  = $this->model->getTableSchema()->getColumns();
    $query = array();

    foreach ($cols as $col) {
      if ($col->name === $from) $col->name = $to;
      $query[] = $this->createColumnAttribute($col);
    }

    $this->inout($tblName, implode(',', $query), '*');
  }

  protected function incrementFilter($attr)
  {
    if (strpos($attr,  'TYPE::INT(INCREMENT)') !== false ||
        strpos($attr, 'TYPE::BINT(INCREMENT)') !== false) {
      $attr = "INTEGER PRIMARY KEY";
    }
    return $attr;
  }

  protected function createColumnAttribute($col)
  {
    $tmp   = array();
    $tmp[] = $col->name;

    switch ($col->type) {
      case Sabel_DB_Type_Const::INT:
        if ($col->increment) {
          $tmp[] = 'integer primary key';
        } elseif ($col->max > 9E+18) {
          $tmp[] = 'bigint';
        } elseif ($col->max < 32768) {
          $tmp[] = 'smallint';
        } else {
          $tmp[] = 'int';
        }
        break;
      case Sabel_DB_Type_Const::STRING:
        $tmp[] = "varchar({$col->max})";
        break;
      default:
        $types = array(Sabel_DB_Type_Const::BOOL     => 'boolean',
                       Sabel_DB_Type_Const::TEXT     => 'text',
                       Sabel_DB_Type_Const::DATETIME => 'datetime',
                       Sabel_DB_Type_Const::FLOAT    => 'float',
                       Sabel_DB_Type_Const::DOUBLE   => 'double');

        $tmp[] = $types[$col->type];
        break;
    }

    if (!$col->nullable) $tmp[] = 'not null';

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
          $val   = ($col->default) ? 'true' : 'false';
          $tmp[] = "default '{$val}'";
          break;
      }
    }

    return implode(' ', $tmp);
  }

  protected function inout($tblName, $createSQL, $selectCols)
  {
    $model = $this->model;
    $model->begin();

    $tmpTable = $tblName . '_alter_tmp';
    $query    = "CREATE TEMPORARY TABLE $tmpTable ( $createSQL )";
    $model->execute($query);

    $query = "INSERT INTO $tmpTable SELECT $selectCols FROM {$tblName}";
    $model->execute($query);
    $model->execute("DROP TABLE $tblName");

    $query = "CREATE TABLE $tblName ( $createSQL )";
    $model->execute($query);

    $query = "INSERT INTO $tblName SELECT * FROM $tmpTable";
    $model->execute($query);
    $model->execute("DROP TABLE $tmpTable");

    $model->commit();
  }
}
