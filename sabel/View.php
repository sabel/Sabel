<?php

Sabel::using('Sabel_View_Renderer');
Sabel::using('Sabel_View_Uri');

class Sabel_View
{
  protected $renderer = null;
  protected $templatePath = '';
  protected $templateName = '';
  
  protected $templateFound = false;
  
  /**
   * @var array values for assignment to template
   */
  protected static $values = array();
  
  protected $layout = '';
  
  public function __construct($module = '', $controller = '', $action = '')
  {
    $this->decideTemplatePathAndNameByEntry($module, $controller, $action);
  }
  
  public function __set($key, $value)
  {
    self::$values[$key] = $value;
  }
  
  public function assign($key, $value)
  {
    self::$values[$key] = $value;
  }
  
  public function assignByArray($array)
  {
    if (is_array($array)) {
      self::$values = array_merge(self::$values, $array);
    }
    return $this;
  }
  
  public function setTemplatePath($path)
  {
    $this->templatePath = $path;
  }
  
  public function setTemplateName($name)
  {
    $this->templateName = $name;
    
    $pathToTemplate = $this->templatePath . $this->templateName;
    if (is_readable($pathToTemplate)) $this->templateFound = true;
  }
  
  public function isTemplateMissing()
  {
    return (!$this->templateFound);
  }
  
  public function setLayout($layout)
  {
    $this->layout = $layout;
  }
  
  public function setRenderer($renderer)
  {
    if ($renderer instanceof Sabel_View_Renderer) $this->renderer = $renderer;
  }
  
  public function enableCache()
  {
    $this->renderer->enableCache();
  }
  
  public function rendering($withLayout = true)
  {
    Sabel_Logger_Factory::create("file")->log("rendering: ".$this->templateName);
    $contents = $this->renderer->rendering($this->templatePath, $this->templateName, self::$values);
    
    if ($withLayout) {
      $found = false;
      
      $usersLayoutName = $this->layout . Sabel_Const::TEMPLATE_POSTFIX;
      
      if (is_file($this->templatePath . $usersLayoutName)) {
        $found = true;
        $name  = $usersLayoutName;
        $contents = $this->renderLayout($this->templatePath, $name, $contents);
      } elseif (is_file($this->templatePath . Sabel_Const::DEFAULT_LAYOUT)) {
        $found = true;
        $contents = $this->renderLayout($this->templatePath, Sabel_Const::DEFAULT_LAYOUT, $contents);
      } elseif (is_file(RUN_BASE . "/app/views/" . Sabel_Const::DEFAULT_LAYOUT)) {
        $found = true;
        $name  = Sabel_Const::DEFAULT_LAYOUT;
        $contents = $this->renderLayout(RUN_BASE . "/app/views/", $name, $contents);
      } elseif (is_file($this->templatePath . Sabel_Const::DEFAULT_LAYOUT)) {
        $found = true;
        $name  = Sabel_Const::DEFAULT_LAYOUT;
        $contents = $this->renderLayout($this->templatePath, $name, $contents);
      }
    }
    
    return $contents;
  }
  
  protected function renderLayout($path, $name, $contents)
  {
    $layout = new self();
    $layout->setTemplatePath($path);
    $layout->setTemplateName($name);
    $layout->assign('contentForLayout', $contents);
    
    return $layout->rendering(false);
  }
  
  public function decideTemplatePath($candidate, $partial = false)
  {
    $this->decideTemplatePathAndNameByEntry($candidate->getModule(),
                                            $candidate->getController(),
                                            $candidate->getAction(), $partial);
    return $this;
  }
  
  protected function decideTemplatePathAndNameByEntry($module, $controller, $action, $partial = false)
  {
    $tplpath  = RUN_BASE;
    $tplpath .= Sabel_Const::MODULES_DIR;
    $tplpath .= $module . DIR_DIVIDER;
    
    $controllerSpecificTplpath = null;
    $controllerSpecific = null;
    
    if (is_dir($tplpath."views/".$controller)) {
      $controllerSpecificTplpath = $tplpath."views/".$controller;
      $controllerSpecific = $controllerSpecificTplpath . "/" . $action . Sabel_Const::TEMPLATE_POSTFIX;      
    }
    
    $tplpath .= Sabel_Const::TEMPLATE_DIR;
    
    // make name string of template such as "controller.method.tpl"
    $tplname  = $controller;
    $tplname .= Sabel_Const::TEMPLATE_NAME_SEPARATOR;
    $tplname .= $action;
    
    if (!$partial && is_readable($controllerSpecific)) {
      $tplname = "/" . $action . Sabel_Const::TEMPLATE_POSTFIX;
      $this->renderer = Sabel::load('Sabel_View_Renderer_Class');
      $this->templateFound = true;
      $this->setTemplatePath($controllerSpecificTplpath);
    } elseif (is_readable($tplpath . $tplname . ".pjs")) {
      $tplname .= ".pjs";
      $this->renderer = Sabel::load('Sabel_View_Renderer_PHP');
      $this->templateFound = true;
      $this->setTemplatePath($tplpath);
    } elseif(is_readable($tplpath . $tplname . Sabel_Const::TEMPLATE_POSTFIX)) {
      $tplname .= Sabel_Const::TEMPLATE_POSTFIX;
      $this->renderer = Sabel::load('Sabel_View_Renderer_Class');
      $this->templateFound = true;
      $this->setTemplatePath($tplpath);
    } else {
      $this->renderer = Sabel::load('Sabel_View_Renderer_Class');
      $this->templateFound = false;
      $this->setTemplatePath($tplpath);
    }
    
    $this->setTemplateName($tplname);
  }
}