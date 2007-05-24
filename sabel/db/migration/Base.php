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
      $remove = false;
    } else {
      $fp = fopen($filePath, "r");
      $remove = true;
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
    if ($remove) unlink($filePath);

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

  protected function getRestoreFileName()
  {
    $exp   = explode("/", $this->filePath);
    $count = count($exp);

    $path = array();
    for ($i = 0; $i < $count; $i++) {
      if ($i === $count - 1) {
        $file = $exp[$i];
      } else {
        $path[] = $exp[$i];
      }
    }

    $path = implode("/", $path) . "/restores";
    if (!is_dir($path)) mkdir($path, 0755);

    $num = substr($file, 0, strpos($file, "_"));
    return $path . "/restore_" . $num;
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

  protected function custom()
  {
    $tmpDir = MIG_DIR . "/temporary";
    mkdir($tmpDir);

    $num = 1;
    $fileName = "";
    $files = array();
    $lines = array();

    $fp = fopen($this->filePath, "r");

    while (!feof($fp)) {
      $line = trim(fgets($fp, 256));
      if (empty($lines) && $line === "") continue;
      if (substr($line, 0, 3) === "###") {
        if (!empty($lines)) {
          $this->writeTemporaryFile($lines, "{$tmpDir}/{$num}_{$fileName}");
          $num++;
          $lines = array();
        }

        $fileName = trim(str_replace("#", "", $line));
        continue;
      }

      $lines[] = $line;
    }

    $this->writeTemporaryFile($lines, "{$tmpDir}/{$num}_{$fileName}");
  }

  private function writeTemporaryFile($lines, $path)
  {
    $fp = fopen($path, "w");
    foreach ($lines as $line) fwrite($fp, $line. "\n", 256);
    fclose($fp);
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

  protected function parseForForeignKey($line)
  {
    $line = str_replace('FKEY', 'FOREIGN KEY', $line);
    return preg_replace('/\) /', ') REFERENCES ', $line, 1);
  }
}
