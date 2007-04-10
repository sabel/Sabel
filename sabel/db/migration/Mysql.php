<?php

/**
 * Sabel_DB_Mysql_Migration
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage mysql
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Mysql_Migration extends Sabel_DB_Base_Migration
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
                             '__TRUE__',
                             '__FALSE__');

  protected $replace = array('integer auto_increment',
                             'bigint auto_increment',
                             'integer',
                             'smallint',
                             'bigint',
                             'varchar',
                             'text',
                             'datetime',
                             'float',
                             'double',
                             '1',
                             '0');

  public function addTable($tblName, $cmdQuery, $engine = null)
  {
    $cmdQuery = preg_replace("/[\n\r\f][ \t]*/", '', $cmdQuery);

    $exeQuery = array();
    foreach (explode(',', $cmdQuery) as $line) {
      if (substr($line, 0, 4) === 'FKEY') {
        $exeQuery[] = $this->parseForForeignKey($line);
      } elseif (strpos($line, 'TYPE::BOOL') === false) {
        $exeQuery[] = $line;
      } else {
        list ($colName) = explode(' ', $line);
        $line = str_replace(array($colName, 'TYPE::BOOL'), '', $line);
        $exeQuery[] = $colName . ' ' . $this->createBooleanAttr($line);
      }
    }

    $sch   = $this->search;
    $rep   = $this->replace;
    $query = str_replace($sch, $rep, implode(',', $exeQuery));
    $query = "CREATE TABLE $tblName ( $query )";
    if ($engine !== null) $query .= " ENGINE={$engine}";
    $this->model->executeQuery($query);
  }

  public function deleteTable($tblName)
  {
    $this->model->executeQuery("DROP TABLE $tblName");
  }

  public function renameTable($from, $to)
  {
    $this->model->executeQuery("ALTER TABLE $from RENAME TO $to");
  }

  public function addColumn($tblName, $colName, $param)
  {
    $sch = $this->search;
    $rep = $this->replace;

    if (strpos($param, 'TYPE::BOOL') !== false) {
      $param = $this->createBooleanAttr(str_replace('TYPE::BOOL', '', $param));
    }

    $attr = str_replace($sch, $rep, $param);
    $this->model->executeQuery("ALTER TABLE $tblName ADD $colName $attr");
  }

  public function deleteColumn($tblName, $colName)
  {
    $this->model->executeQuery("ALTER TABLE $tblName DROP $colName");
  }

  public function changeColumn($tblName, $colName, $param)
  {
    $sch   = $this->search;
    $rep   = $this->replace;
    $query = "ALTER TABLE $tblName MODIFY $colName " . str_replace($sch, $rep, $param);
    $this->model->executeQuery($query);
  }

  public function renameColumn($tblName, $from, $to)
  {
    $conName = $this->model->getConnectName();
    $driver  = $this->model->getDriver();
    $schema  = Sabel_DB_Connection::getSchema($conName);
    $query   = "SELECT column_type FROM information_schema.columns "
             . "WHERE table_schema = '{$schema}' AND table_name = '{$tblName}'";

    $driver->driverExecute($query);
    $row   = $driver->getResultSet()->fetch();
    $query = "ALTER TABLE $tblName CHANGE $from $to " . $row['column_type'];
    $driver->execute($query);
  }

  protected function createBooleanAttr($attr)
  {
    return "tinyint " . trim($attr) . " comment 'boolean'";
  }
}
