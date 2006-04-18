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
      // 標準実装？
      $this->impl = new SmartyEngineImpl();
    }
    $this->impl->configuration();
  }

  public function changeEngine($inc)
  {
    $this->impl = $inc;
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
    $this->smarty->compile_dir = 'compile';
  }

  public function display()
  {
    $this->smarty->template_dir = $this->tplpath;
    $this->smarty->compile_id   = $this->tplpath;
    $this->smarty->display($this->tplname);
  }
}

?>
