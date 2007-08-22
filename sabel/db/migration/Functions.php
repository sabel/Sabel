<?php

// define types for migration.

define("_INT",      Sabel_DB_Type::INT);
define("_SMALLINT", Sabel_DB_Type::SMALLINT);
define("_BIGINT",   Sabel_DB_Type::BIGINT);
define("_FLOAT",    Sabel_DB_Type::FLOAT);
define("_DOUBLE",   Sabel_DB_Type::DOUBLE);
define("_STRING",   Sabel_DB_Type::STRING);
define("_TEXT",     Sabel_DB_Type::TEXT);
define("_DATETIME", Sabel_DB_Type::DATETIME);
define("_BOOL",     Sabel_DB_Type::BOOL);
define("_BYTE",     Sabel_DB_Type::BYTE);
define("_NULL",     "SDB_NULL_VALUE");

function getMigrationFiles($dirPath)
{
  $files = array();
  foreach (scandir($dirPath) as $file) {
    $num = substr($file, 0, strpos($file, "_"));
    if (is_numeric($num)) {
      if (isset($files[$num])) {
        $msg = "the migration file of the same version({$num}) exists.";
        Sabel_Sakle_Task::error($msg);
        exit;
      } else {
        $files[$num] = $file;
      }
    }
  }

  ksort($files);
  return $files;
}

function getFileName($path)
{
  $exp = explode(DIR_DIVIDER, $path);
  return $exp[count($exp) - 1];
}

function getCreate($path, $migClass)
{
  $create = new Sabel_DB_Migration_Classes_Create();
  eval (getPhpSource($path));
  return $create->getColumns($migClass);
}

function getAddColumns($path)
{
  $add = new Sabel_DB_Migration_Classes_AddColumn();
  eval (getPhpSource($path));
  return $add->getAddColumns();
}

function getDropColumns($path)
{
  $drop = new Sabel_DB_Migration_Classes_dropColumn();
  eval (getPhpSource($path));
  return $drop->getDropColumns();
}

function writeTable($schema, $path)
{
  $fp = fopen($path, "w");
  Sabel_DB_Migration_Classes_Restore::forCreate($fp, $schema);
  fclose($fp);
}

function writeColumns($schema, $path, $alterCols, $variable = '$add')
{
  $currentCols = array();

  foreach ($schema->getColumns() as $column) {
    if (in_array($column->name, $alterCols)) $currentCols[] = $column;
  }

  $fp = fopen($path, "w");
  Sabel_DB_Migration_Classes_Restore::forColumns($fp, $currentCols, $variable);
  fclose($fp);
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

function getPhpSource($path)
{
  $content = file_get_contents($path);
  $content = str_replace("->default(", "->defaultValue(", $content);

  return str_replace(array("<?php", "?>"), "", $content);
}

function message($message)
{
  $type = Sabel_DB_Migration_Manager::getApplyMode();

  if ($type === "upgrade") {
    Sabel_Sakle_Task::message($message);
  }
}

function executeQuery($query)
{
  $driver = Sabel_DB_Migration_Manager::getDriver();
  Sabel_DB_Statement::create($driver)->setSql($query)->execute();
}

function getSchema($mdlName)
{
  $accessor = Sabel_DB_Migration_Manager::getAccessor();
  return $accessor->get(convert_to_tablename($mdlName));
}

function is_table_exists($tblName)
{
  $accessor = Sabel_DB_Migration_Manager::getAccessor();
  return in_array($tblName, $accessor->getTableLists());
}
