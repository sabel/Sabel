<?php

/**
 * Sabel_DB_Migration_Base
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Migration_Base
{
  protected $type       = "";
  protected $filePath   = "";
  protected $mdlName    = "";
  protected $command    = "";
  protected $options    = array();
  protected $driver     = null;
  protected $accessor   = null;
  protected $sqlPrimary = false;

  public function __construct($accessor, $driver, $filePath, $type)
  {
    $this->type     = $type;
    $this->driver   = $driver;
    $this->accessor = $accessor;

    $exp  = explode("/", $filePath);
    $file = $exp[count($exp) - 1];
    list ($num, $mdlName, $command) = explode("_", $file);

    $this->filePath = $filePath;
    $this->mdlName  = $mdlName;
    $this->command  = $command;
  }

  protected function setOptions($opts) {}

  public function execute()
  {
    $command = $this->command;
    if (method_exists($this, $command)) {
      $this->$command();
    } else {
      throw new Exception($command . "() method not found in Sabel_DB_Migration_Base");
    }
  }

  public function create()
  {
    if ($this->type === "upgrade") {
      $cols = $this->createColumns();
      $this->createTable($cols);
    } else {
      $this->executeQuery("DROP TABLE " . convert_to_tablename($this->mdlName));
    }
  }

  public function drop()
  {
    if ($this->type === "upgrade") {
      $restore = $this->getRestoreFileName();
      if (!is_file($restore)) {
        $fp = fopen($restore, "w");
        $this->writeRestoreFile($fp, true);
      }

      $this->executeQuery("DROP TABLE " . convert_to_tablename($this->mdlName));
    } else {
      $cols = $this->createColumns($this->getRestoreFileName());
      $this->createTable($cols);
    }
  }

  protected function createColumns($filePath = null)
  {
    $parser = new Sabel_DB_Migration_Parser();

    if ($filePath === null) {
      $fp = fopen($this->filePath, "r");
    } else {
      $fp = fopen($filePath, "r");
    }

    $cols  = array();
    $lines = array();
    $opts  = array();
    $isOpt = false;

    while (!feof($fp)) {
      $line = trim(fgets($fp, 256));

      if ($line === "options:") {
        $isOpt = true; continue;
      }

      if ($isOpt) {
        $opts[] = $line;
      } elseif ($line === "" && !empty($lines)) {
        $cols[] = $parser->toColumn($lines);
        $lines = array();
      } elseif ($line !== "") {
        $lines[] = $line;
      }
    }
    
    if (!empty($lines)) $cols[] = $parser->toColumn($lines);

    if (!empty($opts)) $this->setOptions($opts);
    fclose($fp);

    return $cols;
  }

  protected function getCreateSql($cols)
  {
    $pKey  = array();
    $query = array();

    foreach ($cols as $col) {
      if ($col->primary) $pKey[] = $col->name;
      $query[] = $this->createColumnAttributes($col);
    }

    if ($pKey && !$this->sqlPrimary) {
      $query[] = "PRIMARY KEY(" . implode(", ", $pKey) . ")";
    }

    $query   = implode(", ", $query);
    $tblName = convert_to_tablename($this->mdlName);
    return "CREATE TABLE $tblName (" . $query . ")";
  }

  protected function changeColumn()
  {
    $tblName = convert_to_tablename($this->mdlName);
    $schema  = $this->getTableSchema();

    if ($this->type === "upgrade") {
      $cols = $this->createColumns();
      $columnNames = array();
      foreach ($cols as $col) $columnNames[] = $col->name;

      $restore = $this->getRestoreFileName();
      if (!is_file($restore)) {
        $columns = array();

        foreach ($schema->getColumns() as $column) {
          if (in_array($column->name, $columnNames)) $columns[] = $column;
        }

        $fp = fopen($restore, "w");
        $this->writeRestoreFile($fp, true, $columns);
      }

      $this->changeColumnUpgrade($cols, $schema, $tblName);
    } else {
      $cols = $this->createColumns($this->getRestoreFileName());
      $this->changeColumnDowngrade($cols, $schema, $tblName);
    }
  }

  protected function getTableSchema()
  {
    return $this->accessor->get(convert_to_tablename($this->mdlName));
  }

  protected function executeQuery($query)
  {
    $this->driver->setSql($query)->execute();
  }

  protected function getMigrationFileName()
  {
    $exp = explode("/", $this->filePath);
    return $exp[count($exp) - 1];
  }

  protected function getRestoreFileName()
  {
    $file = $this->getMigrationFileName();
    $num  = substr($file, 0, strpos($file, "_"));
    return MIG_DIR . "/restore_" . $num;
  }

  protected function writeRestoreFile($fp, $isClose = true, $columns = null)
  {
    if ($columns === null) {
      $columns = $this->getTableSchema()->getColumns();
    }

    foreach ($columns as $column) {
      fwrite($fp, $column->name . ":\n");
      if ($column->isString()) {
        fwrite($fp, "  type: " . $column->type . "(" . $column->max . ")");
      } else {
        fwrite($fp, "  type: " . $column->type);
      }

      fwrite($fp, "\n");

      if ($column->nullable) {
        fwrite($fp, "  nullable: TRUE\n");
      } else {
        fwrite($fp, "  nullable: FALSE\n");
      }

      if ($column->primary)   fwrite($fp, "  primary: TRUE\n");
      if ($column->increment) fwrite($fp, "  increment: TRUE\n");

      $d = $column->default;
      if ($d === null) {
        fwrite($fp, "  default: NULL");
      } elseif ($column->isBool()) {
        if ($d) {
          fwrite($fp, "  default: TRUE");
        } else {
          fwrite($fp, "  default: FALSE");
        }
      } elseif (is_int($d) || is_float($d)) {
        fwrite($fp, "  default: $d");
      } else {
        fwrite($fp, "  default: '{$d}'");
      }

      fwrite($fp, "\n\n");
    }

    if ($isClose) fclose($fp);
  }

  protected function getDropColumns()
  {
    $fp   = fopen($this->filePath, "r");
    $cols = array();

    while (!feof($fp)) {
      $line = trim(fgets($fp, 256));
      if ($line === "") continue;
      $cols[] = $line;
    }

    fclose($fp);
    return $cols;
  }

  protected function escape($value)
  {
    return $this->driver->escape($value);
  }

  protected function parseForForeignKey($line)
  {
    $line = str_replace('FKEY', 'FOREIGN KEY', $line);
    return preg_replace('/\) /', ') REFERENCES ', $line, 1);
  }
}
