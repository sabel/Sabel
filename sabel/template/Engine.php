<?php

interface HtmlTemplateService
{
  public function assign($key, $value);
  public function retrieve();
  public function selectName($name);
  public function selectPath($path);
  public function rendering();
}

class HtmlTemplate implements HtmlTemplateService
{
  private $impl;
  
  public function __construct($ins = null)
  {
    $this->impl = ($ins instanceof BaseEngineImpl) ? $ins : new PhpEngineImpl();
    $this->impl->configuration();
  }
  
  public function changeEngine($inc)
  {
    if ($ins instanceof BaseEngineImpl) $this->impl = $inc;
  }
  
  public function assign($key ,$value)
  {
    $this->impl->assign($key, $value);
  }
  
  public function retrieve()
  {
    return $this->impl->retrieve();
  }
  
  public function selectName($name)
  {
    $this->impl->setTemplateName($name);
  }
  
  public function selectPath($path)
  {
    $this->impl->setTemplatePath($path);
  }
  
  public function rendering()
  {
    $this->impl->display();
  }
}

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

class Form
{
  const FORM_START = '<form action="%s" method="%s">';
  const FORM_END   = '</form>';
  const INPUT_TEXT = '<input type="text" name="%s" value="%s" /> <br />';
  const INPUT_AREA = '<textarea type="text" name="%s" style="width: 30em; height: 30em;">%s</textarea> <br />';
  
  public static function create($table, $obj, $action, $method)
  {
    uses('sabel.db.InformationSchema');
    $is = new Sabel_DB_Schema('default', 'default');
    
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
    if ($column->type == Edo_Type::INT || $column->type == Edo_Type::STRING) {
      return sprintf(self::INPUT_TEXT, $name, '');
    } elseif ($column->type == Edo_Type::TEXT) {
      return sprintf(self::INPUT_AREA, $name, '');
    }
  }
  
  public static function edit($table, $obj, $action, $method)
  {
    uses('sabel.db.InformationSchema');
    $is = new Sabel_DB_Schema('default', 'default');
    
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
    if ($column->type == Edo_Type::INT || $column->type == Edo_Type::STRING) {
      return sprintf(self::INPUT_TEXT, $name, $obj->$name);
    } elseif ($column->type == Edo_Type::TEXT) {
      return sprintf(self::INPUT_AREA, $name, $obj->$name);
    }
  }
}

class PhpEngineImpl extends BaseEngineImpl implements TemplateEngineImpl
{
  protected $attributes;
  
  public function assign($key, $value)
  {
    $this->attributes[$key] = $value;
  }
  
  public function put($key)
  {
    echo $this->attributes[$key];
  }
  
  public function __get($key)
  {
    return $this->attributes[$key];
  }
  
  public function retrieve()
  {
    uses('sabel.db.InformationSchema');
    $is = new Sabel_DB_Schema('default', 'default');
    $table = $is->getTable('blog');
    
    if (count($this->attributes) != 0) extract($this->attributes, EXTR_SKIP);
    extract(Re::get(), EXTR_SKIP);
    ob_start();
    include($this->getTemplateFullPath());
    $content = ob_get_clean();
    return $content;
  }
  
  public function load_template($name)
  {
    $t = clone $this;
    $t->setTemplateName($name . '.tpl');
    echo $t->retrieve();
  }
  
  public function configuration()
  {
  }
  
  public function display()
  {
    if (is_file($this->tplpath . 'layout.tpl')) {
      $this->content_for_layout = $this->retrieve();
      $this->setTemplateName('layout.tpl');
    }
    echo $this->retrieve();
  }
}

class SavantEngineImpl extends BaseEngineImpl implements TemplateEngineImpl
{
  private $savant  = null;
  
  public function __construct()
  {
    require_once('Savant3/Savant3.php');
    $this->savant = new Savant3();
  }
  
  public function assign($key, $value)
  {
    $this->savant->assign($key, $value);
  }
  
  public function retrieve()
  {
    $fullpath = $this->getTemplateFullPath();
    
    if (file_exists($fullpath)) {
      return $this->savant->fetch($fullpath);
    } else {
      // @todo Exception handling.
    }
  }
  
  public function configuration()
  {
  }
  
  public function display()
  {
    $path = $this->getTemplateFullPath();
    if (!is_file($path))
      throw new SabelException("template isn't found: " . "'".$path."'");
      
    $this->savant->display($path);
  }
}

class SmartyEngineImpl extends BaseEngineImpl implements TemplateEngineImpl
{
  private $smarty  = null;
  
  public function __construct()
  {
    $this->smarty = new Smarty();
  }
  
  public function assign($key, $value)
  {
    $this->smarty->assign($key, $value);
  }
  
  public function retrieve()
  {
    $this->smarty->template_dir = $this->tplpath;
    $this->smarty->compile_id   = $this->tplpath;
    return $this->smarty->fetch($this->tplname);
  }
  
  public function configuration()
  {
    $this->smarty->compile_dir = RUN_BASE . '/compile';
    $this->smarty->load_filter('output','trimwhitespace');
  }
  
  public function display()
  {
    $this->smarty->template_dir = $this->tplpath;
    $this->smarty->compile_id   = $this->tplpath;
    $this->smarty->display($this->tplname);
  }
}