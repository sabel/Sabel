<?php

class Form
{
  const FORM_START = '<form action="%s" method="%s">';
  const FORM_END   = '</form>';
  const INPUT_TEXT = '<input type="text" name="%s" value="%s" /> <br />';
  const INPUT_AREA = '<textarea type="text" name="%s" style="width: 30em; height: 30em;">%s</textarea> <br />';
  
  public static function create($table, $obj, $action, $method)
  {
    uses('sabel.db.schema.Accessor');
    $is = new Sabel_DB_Schema_Accessor('default', 'default');
    
    $localizeConfig = new Sabel_Config_Yaml(RUN_BASE . '/config/localize.yml');
    $localize = $localizeConfig->read($table);
    
    $buf = array();
    $buf[] = sprintf(self::FORM_START, $action, $method);
    
    foreach ($is->getTable($table)->getColumns() as $column) {
      $name  = $column->name;
      $lname  = (isset($localize[$column->name])) ? $localize[$column->name] : $column->name;
      $buf[] = "{$lname} <br />";
      $buf[] = self::newInput($name, $column);
    }
    
    $buf[] = '<input type="submit" value="create" />';
    $buf[] = self::FORM_END;
    return join("\n", $buf);
  }
  
  public static function newInput($name, $column, $class = null)
  {
    if ($column->type == Sabel_DB_Schema_Type::INT || $column->type == Sabel_DB_Schema_Type::STRING) {
      return sprintf(self::INPUT_TEXT, $name, '');
    } elseif ($column->type == Sabel_DB_Schema_Type::TEXT) {
      return sprintf(self::INPUT_AREA, $name, '');
    }
  }
  
  public static function edit($table, $obj, $action, $method)
  {
    uses('sabel.db.schema.Accessor');
    $is = new Sabel_DB_Schema_Accessor('default', 'default');
    
    $localizeConfig = new Sabel_Config_Yaml(RUN_BASE . '/config/localize.yml');
    $localize = $localizeConfig->read($table);
    
    $buf = array();
    $buf[] = sprintf(self::FORM_START, $action.$obj->id, $method);
    
    foreach ($is->getTable($table)->getColumns() as $column) {
      $name  = $column->name;
      $lname  = (isset($localize[$column->name])) ? $localize[$column->name] : $column->name;
      $buf[] = "{$lname} <br />";
      $buf[] = self::input($obj, $name, $column);
    }
    
    $buf[] = '<input type="submit" value="confirm" />';
    $buf[] = self::FORM_END;
    return join("\n", $buf);
  }
  
  public static function input($obj, $name, $column, $class = null)
  {
    if ($column->type == Sabel_DB_Schema_Type::INT || $column->type == Sabel_DB_Schema_Type::STRING) {
      return sprintf(self::INPUT_TEXT, $name, $obj->$name);
    } elseif ($column->type == Sabel_DB_Schema_Type::TEXT) {
      return sprintf(self::INPUT_AREA, $name, $obj->$name);
    }
  }
}