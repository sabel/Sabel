<?php

/**
 * Sabel_DB_Schema_Sqlite
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage schema
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Schema_SQLite extends Sabel_DB_Schema_Common
{
  protected
    $tableList    = "SELECT name FROM sqlite_master WHERE type = 'table'",
    $tableColumns = "SELECT * FROM sqlite_master WHERE name = '%s'";

  public function __construct($connectName, $schema = null)
  {
    $this->driver = Sabel_DB_Connection::getDriver($connectName);
  }

  public function getTableNames()
  {
    $tables = array();

    $this->driver->execute($this->tableList);
    foreach ($this->driver->getResultSet() as $row) $tables[] = $row['name'];
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

  protected function createColumns($table)
  {
    $this->driver->execute(sprintf($this->tableColumns, $table));
    $assocRow = $this->driver->getResultSet()->fetch();
    $creator  = new Sabel_DB_Schema_Util_Creator();
    return $creator->create($assocRow['sql']);
  }
}
