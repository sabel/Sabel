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
class Sabel_DB_Mysql_Migration
{
  protected $search  = array('TYPE::INT(INCREMENT)',
                             'TYPE::SINT(INCREMENT)',
                             'TYPE::BINT(INCREMENT)',
                             'TYPE::INT',
                             'TYPE::SINT',
                             'TYPE::BINT',
                             'TYPE::STRING',
                             'TYPE::TEXT',
                             'TYPE::DATETIME',
                             'TYPE::FLOAT',
                             'TYPE::DOUBLE');

  protected $replace = array('integer auto_increment',
                             'smallint auto_increment',
                             'bigint auto_increment',
                             'integer',
                             'smallint',
                             'bigint',
                             'varchar',
                             'text',
                             'datetime',
                             'float',
                             'double');

  protected $driver = null;
  protected $connectName = '';

  public function __construct($connectName)
  {
    $this->driver = Sabel_DB_Connection::getDriver($connectName);
    $this->connectName = $connectName;
  }

  public function addTable($tblName, $cmdQuery)
  {
    $cmdQuery = preg_replace("/[\n\r\f]/", '', $cmdQuery);

    $exeQuery = array();
    foreach (explode(',', $cmdQuery) as $line) {
      $line = trim($line);
      if (strpos($line, 'TYPE::BOOL') === false) {
        $exeQuery[] = $line;
      } else {
        list ($colName) = explode(' ', $line);
        $attr = str_replace('TYPE::BOOL', 'tinyint', strpbrk($line, 'TYPE::BOOL'));
        $exeQuery[] = $colName . ' ' . $attr . " comment 'boolean'";
      }
    }

    $sch   = $this->search;
    $rep   = $this->replace;
    $query = str_replace($sch, $rep, implode(',', $exeQuery));
    $this->driver->driverExecute("CREATE TABLE $tblName ( " . $query . " )");
  }

  public function deleteTable($tblName)
  {
    $this->driver->driverExecute("DROP TABLE $tblName");
  }

  public function renameTable($from, $to)
  {
    $this->driver->driverExecute("ALTER TABLE $from RENAME TO $to");
  }

  public function addColumn($tblName, $colName, $param)
  {
    $sch = $this->search;
    $rep = $this->replace;
    $this->driver->driverExecute("ALTER TABLE $tblName ADD $colName " . str_replace($sch, $rep, $param));
  }

  public function deleteColumn($tblName, $colName)
  {
    $this->driver->driverExecute("ALTER TABLE $tblName DROP $colName");
  }

  public function changeColumn($tblName, $colName, $param)
  {
    $sch   = $this->search;
    $rep   = $this->replace;
    $query = "ALTER TABLE $tblName MODIFY $colName " . str_replace($sch, $rep, $param);
    $this->driver->driverExecute($query);
  }

  public function renameColumn($tblName, $from, $to)
  {
    $schema = Sabel_DB_Connection::getSchema($this->connectName);
    $query  = "SELECT column_type FROM information_schema.columns "
            . "WHERE table_schema = '{$schema}' AND table_name = '{$tblName}'";

    $this->driver->driverExecute($query);
    $row   = $this->driver->getResultSet()->fetch();
    $query = "ALTER TABLE $tblName CHANGE $from $to " . $row['column_type'];
    $this->driver->driverExecute($query);
  }
}
