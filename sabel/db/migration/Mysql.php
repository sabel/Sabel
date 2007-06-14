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

  public function setOptions($key, $val)
  {
    $this->options[$key] = $val;
  }

  protected function createTable($cols)
  {
    $query = $this->getCreateSql($cols);

    if (isset($this->options["engine"])) {
      $query .= " ENGINE=" . $this->options["engine"];
    }

    executeQuery($query);
  }

  public function drop()
  {
    if ($this->type === "upgrade") {
      $restore = $this->getRestoreFileName();
      if (is_file($restore)) unlink($restore);

      $fp = fopen($restore, "w");

      $schema = getSchema($this->mdlName);
      Sabel_DB_Migration_Classes_Restore::forCreate($fp, $schema);

      $tblName  = convert_to_tablename($this->mdlName);
      $accessor = Sabel_DB_Migration_Manager::getAccessor();
      $engine   = $accessor->getTableEngine($tblName);

      fwrite($fp, '$create->options("engine", "' . $engine . '");');
      fwrite($fp, "\n");
      fclose($fp);

      executeQuery("DROP TABLE " . convert_to_tablename($this->mdlName));
    } else {
      $path = $this->getRestoreFileName();
      $this->createTable(getCreate($path, $this));
    }
  }

  protected function changeColumnUpgrade($columns, $schema, $tblName)
  {
    foreach ($columns as $column) {
      $current = $schema->getColumnByName($column->name);
      $line = $this->alterChange($column, $current);
      executeQuery("ALTER TABLE $tblName MODIFY $line");
    }
  }

  protected function changeColumnDowngrade($columns, $schema, $tblName)
  {
    foreach ($columns as $column) {
      $line = $this->createColumnAttributes($column);
      executeQuery("ALTER TABLE $tblName MODIFY $line");
    }
  }

  protected function createColumnAttributes($col)
  {
    $line   = array();
    $line[] = $col->name;
    $line[] = $this->getTypeString($col);
    $line[] = $this->getNullableString($col);
    $line[] = $this->getDefaultValue($col);

    if ($col->increment) $line[] = "AUTO_INCREMENT";
    return implode(" ", $line);
  }

  protected function alterChange($column, $current)
  {
    $line   = array();
    $line[] = $column->name;

    $c = ($column->type === null) ? $current : $column;
    $line[] = $this->getTypeString($c, false);

    if ($c->isString()) {
      $max = ($column->max === null) ? $current->max : $column->max;
      $line[] = "({$max})";
    }

    $c = ($column->nullable === null) ? $current : $column;
    $line[] = $this->getNullableString($c);

    $d  = $column->default;
    $cd = $current->default;

    if ($d === $cd) {
      $line[] = $this->getDefaultValue($current);
    } else {
      $this->valueCheck($column, $d);
      $line[] = $this->getDefaultValue($column);
    }

    if ($column->increment) $line[] = "AUTO_INCREMENT";
    return implode(" ", $line);
  }

  private function getTypeString($col, $withLength = true)
  {
    if (!$withLength) return $this->types[$col->type];

    if ($col->isString()) {
      return $this->types[$col->type] . "({$col->max})";
    } else {
      return $this->types[$col->type];
    }
  }

  private function getNullableString($column)
  {
    return ($column->nullable === false) ? "NOT NULL" : "";
  }

  private function valueCheck($column, $default)
  {
    if ($default === null) return true;

    if (($column->isBool() && !is_bool($default)) ||
        ($column->isNumeric() && !is_numeric($default))) {
      throw new Exception("invalid value for default.");
    } else {
      return true;
    }
  }

  protected function getBooleanAttr($value)
  {
    $v = ($value === true) ? "1" : "0";
    return "DEFAULT $v COMMENT 'boolean'";
  }
}
