<?php

interface TemplateEngineImpl
{
  public function assign($key, $value);
  public function retrieve();
  public function setTemplateName($name);
  public function setTemplatePath($path);
  public function configuration();
  public function display();
}

abstract class BaseEngineImpl 
{
  protected
    $tplpath = null,
    $tplname = null;
    
  public function setTemplateName($name)
  {
    $this->tplname = $name;
  }
  
  public function setTemplatePath($path)
  {
    $this->tplpath = $path;
  }
  
  protected function getTemplateFullPath()
  {
    return $this->tplpath . $this->tplname;
  }
}

class Re
{
  protected static $responses = array();
  
  public static function set($name, $value)
  {
    self::$responses[$name] = $value;
  }
  public static function get()
  {
    return self::$responses;
  }
}

uses('sabel.db.schema.Type');

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
    
    $buf = array();
    $buf[] = sprintf(self::FORM_START, $action, $method);
    
    foreach ($is->getTable($table)->getColumns() as $column) {
      $name  = $column->name;
      $buf[] = "{$name} <br />";
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
    
    $buf = array();
    $buf[] = sprintf(self::FORM_START, $action.$obj->id, $method);
    
    foreach ($is->getTable($table)->getColumns() as $column) {
      $name  = $column->name;
      $buf[] = "{$name} <br />";
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
