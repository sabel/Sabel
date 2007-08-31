<?php

/**
 * Sabel_DB_Abstract_Schema
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Abstract_Schema
{
  protected $driver = null;
  protected $schemaName = "";

  abstract public function getTableList();
  abstract public function getForeignKeys($tblName);
  abstract public function getUniques($tblName);

  public function __construct($connectionName, $schemaName)
  {
    $this->driver = Sabel_DB_Config::loadDriver($connectionName);
    $this->schemaName = $schemaName;
  }

  public function getAll()
  {
    $tables = array();
    foreach ($this->getTableList() as $tblName) {
      $tables[$tblName] = $this->getTable($tblName);
    }

    return $tables;
  }

  public function getTable($tblName)
  {
    $columns = $this->createColumns($tblName);
    $schema  = new Sabel_DB_Schema_Table($tblName, $columns);
    $schema->setForeignKeys($this->getForeignKeys($tblName));
    $schema->setUniques($this->getUniques($tblName));

    return $schema;
  }

  protected function execute($sql)
  {
    return $this->driver->execute($sql);
  }

  protected function setDefaultValue($column, $default)
  {
    if ($default === null) {
      $column->default = null;
    } else {
      switch ($column->type) {
        case Sabel_DB_Type::INT:
        case Sabel_DB_Type::SMALLINT:
          $column->default = (int)$default;
          break;

        case Sabel_DB_Type::FLOAT:
        case Sabel_DB_Type::DOUBLE:
          $column->default = (float)$default;
          break;

        case Sabel_DB_Type::BOOL:
          if (is_bool($default)) {
            $column->default = $default;
          } else {
            $column->default = in_array($default, array("1", "t", "true"));
          }
          break;

        case Sabel_DB_Type::BIGINT:
          $column->default = (string)$default;
          break;

        default:
          $column->default = $default;
      }
    }
  }

  public function getTableEngine($tblName)
  {
    return null;
  }
}
