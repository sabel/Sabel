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
  protected $type     = "";
  protected $filePath = "";
  protected $dirPath  = "";
  protected $mdlName  = "";
  protected $command  = "";
  protected $version  = 0;
  protected $fkeys    = array();
  protected $uniques  = array();

  // @todo
  protected $sqlPrimary = false;

  public function __construct($filePath, $type, $dirPath = null)
  {
    $this->type     = $type;
    $this->filePath = $filePath;
    $this->dirPath  = ($dirPath === null) ? MIG_DIR : $dirPath;

    $file = getFileName($filePath);
    list ($num, $mdlName, $command) = explode("_", $file);

    $this->version  = $num;
    $this->mdlName  = $mdlName;
    $this->command  = $command;
  }

  public function setOptions($opts) {}

  public function setForeignKeys($fkeys)
  {
    $this->fkeys = $fkeys;
  }

  public function setUniques($uniques)
  {
    $this->uniques = $uniques;
  }

  public function execute()
  {
    clearstatcache();

    $command = $this->command;
    if (method_exists($this, $command)) {
      $this->$command();
    } else {
      throw new Exception("command '$command' not found.");
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
        $this->writeRestoreFile($fp);
        fclose($fp);
      }

      $this->executeQuery("DROP TABLE " . convert_to_tablename($this->mdlName));
    } else {
      $cols = $this->createColumns($this->getRestoreFileName());
      $this->createTable($cols);
    }
  }

  public function query()
  {
    $parser = new Sabel_DB_Migration_Tools_Parser();
    if ($this->type === "upgrade") {
      $query = $parser->getUpgradeQuery($this->filePath);
    } else {
      $query = $parser->getDowngradeQuery($this->filePath);
    }

    $this->executeQuery($query);
  }

  protected function createColumns($filePath = null)
  {
    $parser = new Sabel_DB_Migration_Tools_Parser();

    if ($filePath === null) {
      $filePath = $this->filePath;
      $remove = false;
    } else {
      $remove = true;
    }

    $columns = $parser->toColumns($this, $filePath);
    if ($remove) unlink($filePath);
    return $columns;
  }

  protected function getDropColumns()
  {
    $parser = new Sabel_DB_Migration_Tools_Parser();
    return $parser->getDropColumns($this->filePath);
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

    if (!empty($this->fkeys)) {
      foreach ($this->fkeys as $colName => $param) {
        $query[] = "FOREIGN KEY ({$colName}) REFERENCES $param";
      }
    }

    if (!empty($this->uniques)) {
      foreach ($this->uniques as $column) {
        $query[] = "UNIQUE ({$column})";
      }
    }

    $tblName = convert_to_tablename($this->mdlName);
    return "CREATE TABLE $tblName (" . implode(", ", $query) . ")";
  }

  protected function changeColumn()
  {
    $tblName = convert_to_tablename($this->mdlName);
    $schema  = $this->getTableSchema();

    if ($this->type === "upgrade") {
      $cols = $this->createColumns();
      foreach ($cols as $col) $columnNames[] = $col->name;

      $this->writeCurrentColumnsAttr($columnNames, $schema);
      $this->changeColumnUpgrade($cols, $schema, $tblName);
    } else {
      $cols = $this->createColumns($this->getRestoreFileName());
      $this->changeColumnDowngrade($cols, $schema, $tblName);
    }
  }

  protected function getRestoreFileName()
  {
    $path = $this->dirPath . "/restores";
    if (!is_dir($path)) mkdir($path);

    return $path . "/restore_" . $this->version;
  }

  protected function writeRestoreFile($fp, $columns = null)
  {
    if ($columns === null) $columns = $this->getTableSchema()->getColumns();
    Sabel_DB_Migration_Tools_Restore::write($fp, $columns);
  }

  protected function writeCurrentColumnsAttr($cols, $schema = null)
  {
    $restore = $this->getRestoreFileName();

    if (!is_file($restore)) {
      $columns = array();
      if ($schema === null) $schema = $this->getTableSchema();

      foreach ($schema->getColumns() as $column) {
        if (in_array($column->name, $cols)) $columns[] = $column;
      }

      $fp = fopen($restore, "w");
      $this->writeRestoreFile($fp, $columns);
      fclose($fp);
    }
  }

  protected function custom()
  {
    $custom = new Sabel_DB_Migration_Tools_Custom();
    $className = get_class($this);

    if ($this->type === "upgrade") {
      $custom->prepareUpgrade($this->filePath);
      $custom->doUpgrade($className, $this->version);
    } else {
      $restoreFile = $this->getRestoreFileName();
      $custom->prepareDowngrade($restoreFile);
      $custom->doDowngrade($className);
    }
  }

  protected function getTableSchema()
  {
    $accessor = Sabel_DB_Migration_Manager::getAccessor();
    return $accessor->get(convert_to_tablename($this->mdlName));
  }

  protected function executeQuery($query)
  {
    Sabel_DB_Migration_Manager::getDriver()->setSql($query)->execute();
  }

  protected function parseForForeignKey($line)
  {
    $line = str_replace("FKEY", "FOREIGN KEY", $line);
    return preg_replace("/\) /", ") REFERENCES ", $line, 1);
  }
}
