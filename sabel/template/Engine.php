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
    if ($ins instanceOf BaseEngineImpl) {
      $this->impl = $ins;
    } else {
      $this->impl = new PhpEngineImpl();
    }
    $this->impl->configuration();
  }
  
  public function changeEngine($inc)
  {
    if ($ins instanceof BaseEngineImpl) {
      $this->impl = $inc;
    }
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
    if (count($this->attributes) != 0) extract($this->attributes, EXTR_SKIP);
    ob_start();
    include($this->getTemplateFullPath());
    $content = ob_get_clean();
    ob_flush();
    return $content;
  }
  
  public function load_template($name)
  {
    $t = clone $this;
    $t->setTemplateName($name.'.tpl');
    echo $t->retrieve();
  }
  
  public function configuration()
  {
  }
  
  public function display()
  {
    if (is_file($this->tplpath.'layout.tpl')) {
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