<?php

/**
 * Sabel_DB_Schema_Base
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Schema_Base
{
  protected $driver = null;
  protected $schemaName = "";

  public function __construct($connectionName, $schemaName)
  {
    $this->driver = load_driver($connectionName);
    $this->schemaName = $schemaName;
  }

  public function getAll()
  {
    $tables = array();
    foreach ($this->getTableNames() as $tblName) {
      $tables[$tblName] = $this->getTable($tblName);
    }

    return $tables;
  }

  public function getTable($tblName)
  {
    $columns = $this->createColumns($tblName);
    return new Sabel_DB_Schema_Table($tblName, $columns);
  }

  protected function execute($sql)
  {
    $driver = $this->driver;

    $driver->setSql($sql)->execute();
    return $driver->getResult();
  }
}
