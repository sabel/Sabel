<?php

Sabel::using("Sabel_DB_Type_Const");
Sabel::using("Sabel_DB_Executer");

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
  public function table($name)
  {
    return new Sabel_DB_Migration_Table($name);
  }
}

class Sabel_DB_Migration_Table
{
  protected $table = '';
  protected $columns = array();
  
  public function __construct($name)
  {
    $this->table = $name;
  }
  
  public function dropTable()
  {
    $this->query("DROP TABLE {$this->table}");
  }
  
  public function addColumn($name, $abstractType, $options = null)
  {
    $driverSpecificTypeAsSQL = '';
    
    $column = array();
    $column["name"] = $name;
    $column["type"] = $this->transrateType($abstractType);
    
    if (isset($options["length"])) {
      $column["length"] = $options["length"];
    } elseif (isset($options["precision"])) {
      $column["precision"] = $options["precision"];
    }
    
    if (isset($options["attributes"])) {
      $column["attributes"] = $options["attributes"];
    } else {
      $column["attributes"] = array();
    }
    
    $this->columns[] = $column;
  }
  
  protected function transrateType($abstractType)
  {
    switch ($abstractType) {
      case Sabel_DB_Type_Const::INT:
        $driverSpecificTypeAsSQL = "INT";
        break;
      case Sabel_DB_Type_Const::STRING:
        $driverSpecificTypeAsSQL = "VARCHAR";
        break;
      case Sabel_DB_Type_Const::TEXT:
        $driverSpecificTypeAsSQL = "TEXT";
        break;
      case Sabel_DB_Type_Const::DATETIME:
        $driverSpecificTypeAsSQL = "DATETIME";
        break;
    }
    
    return $driverSpecificTypeAsSQL;
  }
  
  public function alterDropColumn($name)
  {
    $this->query("ALTER TABLE {$this->table} DROP COLUMN {$name}");
  }
  
  public function alterAddColumn($name, $type, $options = null)
  {
    $type = $this->transrateType($type);
    
    if ($options !== null) {
      if (isset($options["attributes"])) {
        $attributes = join(" ", $options["attributes"]);
      } else {
        $attributes = "";
      }
      
      if (isset($options["length"])) {
        $length = $options["length"];
        $fmt = "ALTER TABLE %s ADD COLUMN %s %s(%s) %s";
        $sql = sprintf($fmt, $this->table, $name, $type, $length, $attributes);
      } elseif (isset($options["precision"])) {
        $fmt = "ALTER TABLE %s ADD COLUMN %s %s(%s, %s) %s";
        $ps = $options["presicion"];
        $sql = sprintf($fmt, $this->table, $name, $type, $ps[0], $ps[1], $attributes);
      }
    } else {
      $fmt = "ALTER TABLE %s ADD COLUMN %s %s %s";
      $sql = sprintf($fmt, $this->table, $name, $type, $attributes);
    }
    $this->query($sql);
  }
  
  public function changeColumnName($oldName, $newName)
  {
    
  }
  
  public function changeColumnType($name, $newType)
  {
    
  }
  
  public function create()
  {
    $sql = $this->makeCreateSql();
    $this->query($sql);
    return $sql;
  }
  
  public function query($sql)
  {
    $executer = new Sabel_DB_Executer(array("table" => $this->table));
    $executer->executeQuery($sql);
  }
  
  public function applyAlters()
  {
    
  }
  
  public function makeCreateSql()
  {
    $fmtHeader = "CREATE TABLE %s (";
    $fmtColumns = array(
                    "normal"    => "  %s %s %s",         // such as 'id INTEGER auto_increment'
                    "length"    => "  %s %s(%s) %s",     // such as 'id VARCHAR(64)'
                    "precision" => "  %s %s(%s,%s) %s"); // such as 'id NUMERIC(0, 12)'
                    
    $fmtFooter = ") %s";
    
    $sql = array();
    $sql[] = sprintf($fmtHeader, $this->table);
    
    $sqlColumns = array();
    foreach ($this->columns as $column) {
      $attributes = join(" ", $column["attributes"]);
      
      if (isset($column["length"])) {
        $sqlColumns[] = sprintf($fmtColumns["length"],
                                 $column["name"],
                                 $column["type"],
                                 $column["length"],
                                 $attributes);
      } elseif (isset($column["precision"])) {
        $sqlColumns[] = sprintf($fmtColumns["precision"],
                                 $column["name"],
                                 $column["type"],
                                 $column["precision"][0],
                                 $column["precision"][1],
                                 $attributes);
      } else {
        $sqlColumns[] = sprintf($fmtColumns["normal"],
                                 $column["name"],
                                 $column["type"],
                                 $attributes);
      }
    }
    
    $sql[] = join(",\n", $sqlColumns);
    $sql[] = ")";
    
    return join("\n", $sql);
  }
}
