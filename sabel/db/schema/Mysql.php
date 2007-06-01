<?php

/**
 * Sabel_DB_Schema_Mysql
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Schema_Mysql extends Sabel_DB_Schema_Common
{
  protected
    $tableList    = "SELECT table_name FROM information_schema.tables WHERE table_schema = '%s'",
    $tableColumns = "SELECT * FROM information_schema.columns WHERE table_schema = '%s' AND table_name = '%s'";

  public function isBoolean($type, $row)
  {
    return ($type === "tinyint" && $row["column_comment"] === "boolean");
  }

  public function isFloat($type)
  {
    return ($type === "float" || $type === "double");
  }

  public function getFloatType($type)
  {
    return ($type === "float") ? "float" : "double";
  }

  public function setDefault($co, $row)
  {
    $default = $row["column_default"];

    if ($default === null) {
      $co->default = null;
    } else {
      $this->setDefaultValue($co, $default);
    }
  }

  public function setIncrement($co, $row)
  {
    $co->increment = ($row["extra"] === "auto_increment");
  }

  public function setPrimaryKey($co, $row)
  {
    $co->primary = ($row["column_key"] === "PRI");
  }

  public function setLength($co, $row)
  {
    //$co->max = (int)$row["character_octet_length"];
    $co->max = (int)$row["character_maximum_length"];
  }

  public function getForeignKey($tblName)
  {
    $instance = Sabel_DB_Schema_Mysql_Factory::create($this->getMysqlVersion());
    return $instance->getForeignKeys($tblName, $this->driver);
  }

  public function getUniques($tblName)
  {
    $instance = Sabel_DB_Schema_Mysql_Factory::create($this->getMysqlVersion());
    return $instance->getUniques($tblName, $this->driver);
  }

  public function getTableEngine($tblName)
  {
    $row = $this->execute("SHOW TABLE STATUS WHERE Name='{$tblName}'");
    return $row[0]["Engine"];
  }

  private function getMysqlVersion()
  {
    static $version = null;

    if ($version === null) {
      $result  = $this->execute("SELECT VERSION() AS version");
      $version = $result[0]["version"];
    }

    return $version;
  }
}
