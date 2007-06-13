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
  protected $pkeys    = array();
  protected $fkeys    = array();
  protected $uniques  = array();

  abstract protected function getBooleanAttr($value);

  public function __construct($filePath, $type, $dirPath = null)
  {
    $this->type     = $type;
    $this->filePath = $filePath;
    $this->dirPath  = ($dirPath === null) ? MIG_DIR : $dirPath;

    $file = getFileName($filePath);
    @list ($num, $mdlName, $command) = explode("_", $file);

    $this->version = $num;
    $this->mdlName = $mdlName;

    if ($mdlName === "Mix.php") {
      $this->command = "custom";
    } elseif (($pos = strpos($command, ".")) !== false) {
      $this->command = substr($command, 0, $pos);
    } else {
      $this->command = $command;
    }
  }

  public function setOptions($key, $val) {}

  public function setPrimaryKeys($pkeys)
  {
    $this->pkeys = $pkeys;
  }

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
      $create = new Sabel_DB_Migration_Classes_Create();
      eval ($this->getPhpSource());
      $this->createTable($create->getColumns($this));
    } else {
      $this->executeQuery("DROP TABLE " . convert_to_tablename($this->mdlName));
    }
  }

  public function drop()
  {
    if ($this->type === "upgrade") {
      $restore = $this->getRestoreFileName();
      if (is_file($restore)) unlink($restore);

      $schema = $this->getTableSchema();

      $fp = fopen($restore, "w");
      Sabel_DB_Migration_Classes_Restore::forCreate($fp, $schema);
      fclose($fp);

      $this->executeQuery("DROP TABLE " . convert_to_tablename($this->mdlName));
    } else {
      $create = new Sabel_DB_Migration_Classes_Create();
      eval ($this->getPhpSource($this->getRestoreFileName()));
      $this->createTable($create->getColumns($this));
    }
  }

  public function addColumn()
  {
    $add = new Sabel_DB_Migration_Classes_AddColumn();
    eval ($this->getPhpSource());

    $columns = $add->getAddColumns();
    $tblName = convert_to_tablename($this->mdlName);

    if ($this->type === "upgrade") {
      foreach ($columns as $column) {
        $line = $this->createColumnAttributes($column);
        $this->executeQuery("ALTER TABLE $tblName ADD " . $line);
      }
    } else {
      foreach ($columns as $column) {
        $this->executeQuery("ALTER TABLE $tblName DROP " . $column->name);
      }
    }
  }

  public function dropColumn()
  {
    $restore = $this->getRestoreFileName();
    $tblName = convert_to_tablename($this->mdlName);

    $drop = new Sabel_DB_Migration_Classes_dropColumn();
    eval ($this->getPhpSource());

    if ($this->type === "upgrade") {
      if (is_file($restore)) unlink($restore);

      $columns = $drop->getDropColumns();
      $currentCols = array();

      $schema = $this->getTableSchema();
      foreach ($schema->getColumns() as $column) {
        if (in_array($column->name, $columns)) $currentCols[] = $column;
      }

      $fp = fopen($restore, "w");
      Sabel_DB_Migration_Classes_Restore::forColumns($fp, $currentCols);
      fclose($fp);

      foreach ($columns as $column) {
        $this->executeQuery("ALTER TABLE $tblName DROP $column");
      }
    } else {
      $add = new Sabel_DB_Migration_Classes_AddColumn();
      eval ($this->getPhpSource($this->getRestoreFileName()));

      $columns = $add->getAddColumns();
      $tblName = convert_to_tablename($this->mdlName);

      foreach ($columns as $column) {
        $line = $this->createColumnAttributes($column);
        $this->executeQuery("ALTER TABLE $tblName ADD " . $line);
      }
    }
  }

  public function query()
  {
    $query = new Sabel_DB_Migration_Classes_Query();
    eval ($this->getPhpSource());

    if ($this->type === "upgrade") {
      $queries = $query->getUpgradeQueries();
    } else {
      $queries = $query->getDowngradeQueries();
    }

    foreach ($queries as $query) {
      $this->executeQuery($query);
    }
  }

  protected function getPhpSource($path = null)
  {
    if ($path === null) $path = $this->filePath;

    $content = file_get_contents($path);
    $content = str_replace("->default(", "->defaultValue(", $content);

    return str_replace(array("<?php", "?>"), "", $content);
  }

  protected function getCreateSql($cols)
  {
    $query = array();

    foreach ($cols as $col) {
      $query[] = $this->createColumnAttributes($col);
    }

    if ($this->pkeys) {
      $query[] = "PRIMARY KEY(" . implode(", ", $this->pkeys) . ")";
    }

    if ($this->fkeys) {
      foreach ($this->fkeys as $colName => $param) {
        $query[] = "FOREIGN KEY ({$colName}) REFERENCES $param";
      }
    }

    if ($this->uniques) {
      foreach ($this->uniques as $unique) {
        $query[] = "UNIQUE (" . implode(", ", $unique) . ")";
      }
    }

    $tblName = convert_to_tablename($this->mdlName);
    return "CREATE TABLE $tblName (" . implode(", ", $query) . ")";
  }

  protected function changeColumn()
  {
    $tblName = convert_to_tablename($this->mdlName);
    $restore = $this->getRestoreFileName();
    $schema  = $this->getTableSchema();

    if ($this->type === "upgrade") {
      if (is_file($restore)) unlink($restore);

      $change = new Sabel_DB_Migration_Classes_ChangeColumn();
      eval ($this->getPhpSource());

      $columns = $change->getChangeColumns();
      $currentCols = array();

      $names = array();
      foreach ($columns as $column) $names[] = $column->name;

      $schema = $this->getTableSchema();
      foreach ($schema->getColumns() as $column) {
        if (in_array($column->name, $names)) $currentCols[] = $column;
      }

      $fp = fopen($restore, "w");
      Sabel_DB_Migration_Classes_Restore::forColumns($fp, $currentCols, '$change');
      fclose($fp);

      $this->changeColumnUpgrade($columns, $schema, $tblName);
    } else {
      $change = new Sabel_DB_Migration_Classes_ChangeColumn();
      eval ($this->getPhpSource($restore));

      $columns = $change->getChangeColumns();
      $this->changeColumnDowngrade($columns, $schema, $tblName);
    }
  }

  /*
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
      Sabel_DB_Migration_Tools_Restore::write($fp, null, $columns);
      fclose($fp);
    }
  }
  */

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

  protected function custom()
  {
    $custom = new Sabel_DB_Migration_Classes_Custom();
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

  protected function message($message)
  {
    echo "[\x1b[1;34mMESSAGE\x1b[m]: " . $message . "\n";
  }

  protected function getDefaultValue($column)
  {
    $d = $column->default;

    if ($column->isBool()) {
      return $this->getBooleanAttr($d);
    } elseif ($d === null) {
      return ($column->nullable === true) ? "DEFAULT NULL" : "";
    } elseif ($column->isNumeric()) {
      return "DEFAULT $d";
    } else {
      return "DEFAULT '{$d}'";
    }
  }
}

function arrange($columns)
{
  foreach ($columns as $column) {
    if ($column->primary === true) {
      $column->nullable = false;
    } elseif ($column->nullable === null) {
      $column->nullable = true;
    }

    if ($column->primary === null) {
      $column->primary = false;
    }

    if ($column->increment === null) {
      $column->increment = false;
    }

    if ($column->type === Sabel_DB_Type::STRING &&
        $column->max === null) $column->max = 255;

    if ($column->primary) $pkeys[] = $column->name;
  }

  return $columns;
}
