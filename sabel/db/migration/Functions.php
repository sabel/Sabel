<?php

function executeQuery($query)
{
  Sabel_DB_Migration_Manager::getDriver()->setSql($query)->execute();
}

function getSchema($mdlName)
{
  $accessor = Sabel_DB_Migration_Manager::getAccessor();
  return $accessor->get(convert_to_tablename($mdlName));
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
  echo "[\x1b[1;34mMESSAGE\x1b[m]: " . $message . "\n";
}

