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
class Sabel_DB_Migration_Mysql extends Sabel_DB_Migration_Common
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

  public function setOptions($options)
  {
    foreach ($options as $opt) {
      if ($opt === "") continue;
      list ($name, $value) = array_map("trim", explode(":", $opt));
      $this->options[$name] = $value;
    }
  }

  public function createTable($cols)
  {
    $query = $this->getCreateSql($cols);

    if (isset($this->options["engine"])) {
      $query .= " ENGINE=" . $this->options["engine"];
    }

    $this->executeQuery($query);
  }

  public function drop()
  {
    if ($this->type === "upgrade") {
      $restore = $this->getRestoreFileName();
      if (!is_file($restore)) {
        $fp = fopen($restore, "w");
        $this->writeRestoreFile($fp);

        $tblName  = convert_to_tablename($this->mdlName);
        $accessor = Sabel_DB_Migration_Manager::getAccessor();
        $engine   = $accessor->getTableEngine($tblName);

        fwrite($fp, "options:\n");
        fwrite($fp, "  engine: {$engine}\n");
        fclose($fp);
      }

      $this->executeQuery("DROP TABLE " . convert_to_tablename($this->mdlName));
    } else {
      $cols = $this->createColumns($this->getRestoreFileName());
      $this->createTable($cols);
    }
  }

  protected function changeColumnUpgrade($cols, $schema, $tblName)
  {
    foreach ($cols as $col) {
      $current = $schema->getColumnByName($col->name);
      $line = $this->alterChange($col, $current);
      $this->executeQuery("ALTER TABLE $tblName MODIFY $line");
    }
  }

  protected function changeColumnDowngrade($cols, $schema, $tblName)
  {
    foreach ($cols as $col) {
      $line = $this->createColumnAttributes($col);
      $this->executeQuery("ALTER TABLE $tblName MODIFY $line");
    }
  }

  protected function createColumnAttributes($col)
  {
    $line   = array();
    $line[] = $col->name;
    $line[] = $this->getTypeString($col);
    $line[] = $this->getNullableString($col);
    $line[] = $this->getDefaultString($col);

    if ($col->increment) $line[] = "AUTO_INCREMENT";
    return implode(" ", $line);
  }

  protected function alterChange($col, $current)
  {
    $line   = array();
    $line[] = $col->name;

    $c = ($col->type === Sabel_DB_Migration_Tools_Parser::IS_EMPTY) ? $current : $col;
    $line[] = $this->getTypeString($c);

    $c = ($col->nullable === Sabel_DB_Migration_Tools_Parser::IS_EMPTY) ? $current : $col;
    $line[] = $this->getNullableString($c);

    $d  = $col->default;
    $cd = $current->default;

    if ($d === Sabel_DB_Migration_Tools_Parser::IS_EMPTY && $cd !== null) {
      if ($current->isBool()) {
        $line[] = $this->getBooleanAttr($cd);
      } elseif ($current->isString()) {
        $line[] = "DEFAULT '{$cd}'";
      } else {
        $line[] = "DEFAULT $cd";
      }
    } else {
      $line[] = $this->getDefaultString($col);
    }

    if ($col->increment) $line[] = "AUTO_INCREMENT";
    return implode(" ", $line);
  }

  private function getTypeString($col)
  {
    if ($col->isString()) {
      return $this->types[$col->type] . "({$col->max})";
    } else {
      return $this->types[$col->type];
    }
  }

  private function getNullableString($col)
  {
    return ($col->nullable === false) ? "NOT NULL" : "";
  }

  private function getDefaultString($col)
  {
    $d = $col->default;

    if ($d === Sabel_DB_Migration_Tools_Parser::IS_EMPTY) {
      return "";
    } else {
      if ($col->isBool()) {
        return $this->getBooleanAttr($d);
      } elseif ($col->isString()) {
        return ($d === null) ? "DEFAULT ''" : "DEFAULT '{$d}'";
      } elseif ($d !== null) {
        return "DEFAULT $d";
      }
    }
  }

  private function getBooleanAttr($value)
  {
    return "DEFAULT " . (($value) ? 1 : 0) . " COMMENT 'boolean'";
  }
}
