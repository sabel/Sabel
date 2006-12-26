<?php

/**
 * Sabel_DB_Migration
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Migration
{
  protected $migration = null;

  public function __construct($env)
  {
    $params = get_db_params($env);
    $params = array_shift($params);

    switch ($params['driver']) {
      case 'mysql':
      case 'pdo-mysql':
        $db = 'Mysql';
        break;
      case 'pgsql':
      case 'pdo-pgsql':
        $db = 'Pgsql';
        break;
      case 'pdo-sqlite':
        $db = 'Sqlite';
        break;
      case 'firebird':
        $db = 'Firebird';
        break;
      case 'mssql':
        $db = 'Mssql';
        break;
    }

    $clsName = 'Sabel_DB_' . $db . '_Migration';
    $this->migration = Sabel::load($clsName);
  }

  public function add($type, $tblName, $arg2, $arg3 = null)
  {
    $this->migration->setModel($tblName);

    switch ($type) {
      case Migration::TABLE:
        $this->migration->addTable($tblName, $arg2);
        break;
      case Migration::VIEW:

        break;
      case Migration::COLUMN:
        $this->migration->addColumn($tblName, $arg2, $arg3);
        break;
    }
    Sabel_DB_SimpleCache::clear();
  }

  public function delete($type, $tblName, $arg2 = null)
  {
    $this->migration->setModel($tblName);

    switch ($type) {
      case Migration::TABLE:
        $this->migration->deleteTable($tblName);
        break;
      case Migration::VIEW:

        break;
      case Migration::COLUMN:
        $this->migration->deleteColumn($tblName, $arg2);
        break;
    }
    Sabel_DB_SimpleCache::clear();
  }

  public function change($type, $tblName, $arg2, $arg3 = null)
  {
    $this->migration->setModel($tblName);

    switch ($type) {
      case Migration::TABLE:
        break;
      case Migration::VIEW:
        break;
      case Migration::COLUMN:
        $this->migration->changeColumn($tblName, $arg2, $arg3);
        break;
    }
    Sabel_DB_SimpleCache::clear();
  }

  public function rename($type, $tblName, $arg2, $arg3 = null)
  {
    $this->migration->setModel($tblName);

    switch ($type) {
      case Migration::TABLE:
        $this->migration->renameTable($tblName, $arg2);
        break;
      case Migration::VIEW:

        break;
      case Migration::COLUMN:
        $this->migration->renameColumn($tblName, $arg2, $arg3);
        break;
    }
    Sabel_DB_SimpleCache::clear();
  }
}
