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
  protected $migration   = null;
  protected $table       = '';
  protected $view        = '';
  protected $column      = '';
  protected $alterObject = '';
  protected $connectName = '';

  public function __construct($env)
  {
    if ($this->table === '')
      throw new Exception('Error: $table is empty. please set the table name.');

    if ($this->connectName === '')
      throw new Exception('Error: $connectName is empty.');

    switch ($env) {
      case 'production':
        $environment = PRODUCTION;
        break;
      case 'test':
        $environment = TEST;
        break;
      case 'development':
        $environment = DEVELOPMENT;
        break;
    }

    $params = get_db_params($environment);
    Sabel_DB_Connection::addConnection($env, $params);

    switch ($params[$this->connectName]['driver']) {
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
    $this->migration = Sabel::load($clsName, $this->connectName);
  }

  public function add($param)
  {
    if ($this->column !== '') {
      $this->migration->addColumn($this->table, $this->column, $param);
    } elseif ($this->view !== '') {

    } else {
      $this->migration->addTable($this->table, $param);
    }
  }

  public function delete($param = null)
  {
    if ($this->column !== '') {
      $this->migration->deleteColumn($this->table, $this->column);
    } elseif ($this->view !== '') {

    } else {
      $this->migration->deleteTable($this->table);
    }
  }

  public function change($param)
  {
    if ($this->column !== '') {
      $this->migration->changeColumn($this->table, $this->column, $param);
    } elseif ($this->view !== '') {

    } else {
      $this->migration->changeTable($this->table);
    }
  }

  public function renameTo($name)
  {
    if ($this->column !== '') {
      $this->migration->renameColumn($this->table, $this->column, $name);
    } elseif ($this->view !== '') {

    } else {
      $this->migration->renameTable($this->table, $name);
    }
  }

  public function renameFrom($name)
  {
    if ($this->column !== '') {
      $this->migration->renameColumn($this->table, $name, $this->column);
    } elseif ($this->view !== '') {

    } else {
      $this->migration->renameTable($name, $this->table);
    }
  }
}
