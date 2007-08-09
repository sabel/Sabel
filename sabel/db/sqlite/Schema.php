<?php

/**
 * Sabel_DB_Sqlite_Schema
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Sqlite_Schema extends Sabel_DB_Abstract_Schema
{
  protected
    $tableList    = "SELECT name FROM sqlite_master WHERE type = 'table'",
    $tableColumns = "SELECT sql FROM sqlite_master WHERE name = '%s'";

  private
    $difinition  = "",
    $floatTypes  = array("float", "float4", "real"),
    $doubleTypes = array("float8", "double");

  public function getTableLists()
  {
    $tables = array();

    $rows = $this->execute($this->tableList);
    foreach ($rows as $row) $tables[] = $row["name"];
    return $tables;
  }

  public function getForeignKeys($tblName)
  {
    return null;
  }

  public function getUniques($tblName)
  {
    $createSql = $this->getCreateSql($tblName);
    preg_match_all("/UNIQUE ?(\([^)]+\))/i", $createSql, $matches);
    if (empty($matches[1])) return null;

    $uniques = array();
    foreach ($matches[1] as $unique) {
      $unique = trim(str_replace(array("(", ")"), "", $unique));
      $exp = array_map("trim", explode(",", $unique));
      $uniques[] = $exp;
    }

    return $uniques;
  }

  protected function createColumns($tblName)
  {
    return $this->create($this->getCreateSql($tblName));
  }

  private function create($createSql)
  {
    $columns = array();
    $lines   = $this->splitCreateSQL($createSql);

    foreach ($lines as $key => $line) {
      $co    = new Sabel_DB_Schema_Column();
      $split = explode(" ", $line);
      $name  = $split[0];
      $attr  = trim(substr($line, strlen($name)));
      $co->name = $name;

      if (preg_match("/PRIMARY KEY ?\(/i", $line) ||
          preg_match("/UNIQUE ?\(/i", $line)) break;

      $this->setIncrement($co, $attr);

      if ($this->setDataType($co, $attr)) {
        $this->setNotNull($co);
        $this->setPrimary($co);
        $this->setDefault($co);
      }

      $columns[$name] = $co;
    }

    $this->setConstraintPrimaryKey($columns, $createSql);
    return $columns;
  }

  private function splitCreateSQL($sql)
  {
    $sql   = substr(strpbrk($sql, "("), 0);
    $lines = explode(",", substr($sql, 1, -1));
    return array_map("strtolower", array_map("trim", $lines));
  }

  private function setDataType($co, $attributes)
  {
    $tmp     = substr($attributes, 0, strpos($attributes, " "));
    $type    = ($tmp === "") ? $attributes : $tmp;
    $colLine = substr($attributes, strlen($type));

    if ($this->isBoolean($type)) {
      $co->type = Sabel_DB_Type::BOOL;
    } elseif (!$this->isString($co, $type)) {
      if ($this->isFloat($type)) $type = $this->getFloatType($type);
      Sabel_DB_Type_Setter::send($co, $type);
    }

    if ($colLine === "") {
      $co->nullable = true;
      $co->primary  = false;
      $co->default  = null;
      return false;
    } else {
      $this->difinition = $colLine;
      return true;
    }
  }

  protected function isBoolean($type)
  {
    return ($type === "boolean" || $type === "bool");
  }

  protected function isFloat($type)
  {
    return (in_array($type, $this->floatTypes) || in_array($type, $this->doubleTypes));
  }

  protected function getFloatType($type)
  {
    return (in_array($type, $this->floatTypes)) ? "float" : "double";
  }

  protected function isString($co, $type)
  {
    $types = array("varchar", "char", "character");

    foreach ($types as $sType) {
      if (strpos($type, $sType) !== false) {
        $length   = strpbrk($type, "(");
        $co->type = Sabel_DB_Type::STRING;
        $co->max  = ($length === false) ? 255 : (int)substr($length, 1, -1);
        return true;
      }
    }

    return false;
  }

  private function setNotNull($co)
  {
    $co->nullable  = (strpos($this->difinition, "not null") === false);
    $this->difinition = str_replace("not null", "", $this->difinition);
  }

  private function setPrimary($co)
  {
    $co->primary   = (strpos($this->difinition, "primary key") !== false);
    $this->difinition = str_replace("primary key", "", $this->difinition);
  }

  private function setDefault($co)
  {
    if (strpos($this->difinition, "default") === false) {
      $co->default = null;
    } else {
      $d = substr(trim($this->difinition), 8);
      if ($d === "null" || $d === "NULL") {
        $co->default = null;
      } else {
        if (!$co->isBool()) {
          if (!is_numeric($d)) $d = substr($d, 1, -1);
        }
        $this->setDefaultValue($co, $d);
      }
    }
  }

  private function setIncrement($co, $attributes)
  {
    $pri  = (strpos($attributes, "integer primary key") !== false);
    $pri2 = (strpos($attributes, "integer not null primary key") !== false);

    $co->increment = ($pri || $pri2);
  }

  private function setConstraintPrimaryKey($columns, $createSql)
  {
    preg_match_all("/PRIMARY KEY ?(\([^)]+\))/i", $createSql, $matches);
    if (empty($matches[1])) return;

    $pkey = str_replace(array("(", ")"), "", $matches[1][0]);
    $exp  = array_map("trim", explode(",", $pkey));

    foreach ($exp as $colName) {
      if (isset($columns[$colName])) {
        $columns[$colName]->primary  = true;
        $columns[$colName]->nullable = false;
      }
    }
  }

  private function getCreateSql($tblName)
  {
    $rows = $this->execute(sprintf($this->tableColumns, $tblName));
    return $rows[0]["sql"];
  }
}
