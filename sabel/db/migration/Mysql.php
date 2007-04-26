<?php

/**
 * Sabel_DB_Migration_Mysql
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Migration_Mysql extends Sabel_DB_Migration_Base
{
  protected $types = array(Sabel_DB_Type::INT      => "integer",
                           Sabel_DB_Type::BIGINT   => "bigint",
                           Sabel_DB_Type::SMALLINT => "smallint",
                           Sabel_DB_Type::FLOAT    => "float",
                           Sabel_DB_Type::DOUBLE   => "double",
                           Sabel_DB_Type::BOOL     => "tinyint",
                           Sabel_DB_Type::STRING   => "varchar",
                           Sabel_DB_Type::TEXT     => "text",
                           Sabel_DB_Type::DATETIME => "datetime");

  public function execute()
  {
    $command = $this->command;
    $this->$command();
  }

  public function setOptions($options)
  {
    foreach ($options as $opt) {
      if ($opt === "") continue;
      list ($name, $value) = array_map("trim", explode(":", $opt));
      $this->options[$name] = $value;
    }
  }

  public function create()
  {
    if ($this->type === "upgrade") {
      $cols = parent::create();
      $this->createTable($cols);
    } else {
      $query = "DROP TABLE " . convert_to_tablename($this->mdlName);
      $this->executeQuery($query);
    }
  }

  public function createTable($cols)
  {
    $pKey  = array();
    $query = array();

    foreach ($cols as $col) {
      if ($col->primary) $pKey[] = $col->name;
      $query[] = $this->createColumnAttributes($col);
    }

    if ($pKey) {
      $query[] = "PRIMARY KEY(" . implode(", ", $pKey) . ")";
    }

    $query   = implode(", ", $query);
    $tblName = convert_to_tablename($this->mdlName);
    $query   = "CREATE TABLE $tblName (" . $query . ")";

    if (isset($this->options["engine"])) {
      $query .= " ENGINE=InnoDB";
    }

    $this->executeQuery($query);
  }

  public function add()
  {
    $cols = parent::create();
    $tblName = convert_to_tablename($this->mdlName);

    if ($this->type === "upgrade") {
      foreach ($cols as $col) {
        $line = $this->createColumnAttributes($col);
        $this->executeQuery("ALTER TABLE $tblName ADD " . $line);
      }
    } else {
      foreach ($cols as $col) {
        $line = $this->createColumnAttributes($col);
        $this->executeQuery("ALTER TABLE $tblName DROP " . $col->name);
      }
    }
  }

  public function renameTable($from, $to)
  {
    $this->model->executeQuery("ALTER TABLE $from RENAME TO $to");
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

  protected function createColumnAttributes($col)
  {
    $line   = array();
    $line[] = $col->name;

    if ($col->type === Sabel_DB_Type::STRING) {
      $line[] = $this->types[$col->type] . "({$col->length})";
    } else {
      $line[] = $this->types[$col->type];
    }

    if (!$col->nullable) $line[] = "NOT NULL";

    if ($col->default !== null) {
      if ($col->type === Sabel_DB_Type::BOOL) {
        $line[] = "DEFAULT " . $this->getBooleanValue($col->default);
        $line[] = "COMMENT 'boolean'";
      } else {
        $line[] = "DEFAULT " . $col->default;
      }
    }

    if ($col->increment) $line[] = "AUTO_INCREMENT";

    return implode(" ", $line);
  }

  protected function getBooleanValue($value)
  {
    return (in_array($value, array("true", "TRUE", 1))) ? 1 : 0;
  }
}
