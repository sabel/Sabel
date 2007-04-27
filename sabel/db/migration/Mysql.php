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
        $this->writeRestoreFile($fp, false);

        $tblName = convert_to_tablename($this->mdlName);
        $engine  = $this->accessor->getTableEngine($tblName);

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
      $line = $this->createColumnAttributes($col, $current);
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

  protected function createColumnAttributes($col, $current = null)
  {
    $line   = array();
    $line[] = $col->name;

    if (isset($current) && $col->type === "EMPTY") {
      if ($current->isString()) {
        $line[] = $this->types[$current->type] . "({$current->max})";
      } else {
        $line[] = $this->types[$current->type];
      }
    } else {
      if ($col->isString()) {
        $line[] = $this->types[$col->type] . "({$col->max})";
      } else {
        $line[] = $this->types[$col->type];
      }
    }

    if (isset($current) && $col->nullable === "EMPTY") {
      if (!$current->nullable) $line[] = "NOT NULL";
    } else {
      if ($col->nullable === false) $line[] = "NOT NULL";
    }

    $cd = $col->default;

    if (isset($current) && $cd === "EMPTY" && $current->default !== null) {
      if ($current->isBool()) {
        $line[] = $this->getBooleanAttr($current->default);
      } elseif ($current->isString()) {
        $line[] = "DEFAULT '{$current->default}'";
      } else {
        $line[] = "DEFAULT {$current->default}";
      }
    } elseif ($cd !== "EMPTY") {
      if ($col->isBool()) {
        $line[] = $this->getBooleanAttr($cd);
      } elseif ($col->isString()) {
        $line[] = ($cd === null) ? "DEFAULT ''" : "DEFAULT '{$cd}'";
      } elseif ($cd !== null) {
        $line[] = "DEFAULT $cd";
      }
    }

    if ($col->increment) $line[] = "AUTO_INCREMENT";

    return implode(" ", $line);
  }

  protected function getBooleanAttr($value)
  {
    $val = (in_array($value, array("true", "TRUE", 1))) ? 1 : 0;
    return "DEFAULT $val COMMENT 'boolean'";
  }
}
