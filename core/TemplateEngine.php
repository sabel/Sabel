<?php

interface HtmlTemplateService
{
  public function assign($key, $value);
  public function retrieve();
  public function selectName($name);
  public function selectPath($path);
  public function rendering();
}

class TemplateEngine implements HtmlTemplateService
{
  private $impl;

  public function __construct()
  {
    $this->impl = new SavantEngineImpl();
    $this->impl->configuration();
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

class SavantEngineImpl
{
  private $savant  = null;
  
  private $tplpath = null;
  private $tplname = null;

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

  public function setTemplateName($name)
  {
    $this->tplname = $name;
  }

  public function setTemplatePath($path)
  {
    $this->tplpath = $path;
  }

  public function configuration()
  {
    // ignore
  }

  public function display()
  {
    $this->savant->display($this->getTemplateFullPath());
  }

  protected function getTemplateFullPath()
  {
    return $this->tplpath . $this->tplname;
  }
  
}

?>