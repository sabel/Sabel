<?php

class Sabel_View
{
  protected $renderer = null;
  protected $templatePath = '';
  protected $templateName = '';
  
  /**
   * @var array values for assignment to template
   */
  protected $values = array();
  
  protected $layout = '';
  
  public function __construct($entry = null)
  {
    if (is_null($entry)) {
      $entry = Sabel_Context::getCurrentMapEntry();
    }
    
    if ($entry instanceof Sabel_Map_Entry) {
      $this->decideTemplatePathAndNameByEntry($entry);
    }
    
    $this->renderer = new Sabel_View_Renderer_Class();
  }
  
  public function __set($key, $value)
  {
    $this->values[$key] = $value;
  }
  
  public function assign($key, $value)
  {
    $this->values[$key] = $value;
  }
  
  public function assignByArray($array)
  {
    $this->values = array_merge($this->values, $array);
  }
  
  public function setTemplatePath($path)
  {
    $this->templatePath = $path;
  }
  
  public function setTemplateName($name)
  {
    $this->templateName = $name;
  }
  
  public function isTemplateMissing()
  {
    return (is_file($this->templatePath.$this->templateName.'.tpl'));
  }
  
  public function setLayout($layout)
  {
    $this->layout = $layout;
  }
  
  public function setRenderer($renderer)
  {
    if ($renderer instanceof Sabel_View_Renderer) $this->renderer = $renderer;
  }
  
  public function rendering($withLayout = true)
  {
    $contents = $this->renderer->rendering($this->templatePath, $this->templateName, $this->values);
    
    if ($withLayout) {
      $found = false;
      
      if (is_file($this->templatePath . 'layout.tpl')) {
        $found = true;
        $name = 'layout.tpl';
      }
      
      if (is_file($this->templatePath . $this->layout . '.tpl')) {
        $found = true;
        $name = $this->layout . '.tpl';
      }
      
      if ($found) {
        $layout = new self();
        $layout->setTemplatePath($this->templatePath);
        $layout->setTemplateName($name);
        $layout->assign('contentForLayout', $contents);
        
        return $layout->rendering(false);
      }
    } else {
      return $contents;
    }
  }
  
  protected function decideTemplatePathAndNameByEntry($entry)
  {
    $destination = $entry->getDestination();
    
    $tplpath  = RUN_BASE;
    $tplpath .= Sabel_Core_Const::MODULES_DIR;
    $tplpath .= $destination->module . '/';
    $tplpath .= Sabel_Core_Const::TEMPLATE_DIR;
    
    // make name string of template such as "controller.method.tpl"
    $tplname  = $destination->controller;
    $tplname .= Sabel_Core_Const::TEMPLATE_NAME_SEPARATOR;
    $tplname .= $destination->action;
    $tplname .= Sabel_Core_Const::TEMPLATE_POSTFIX;
    
    $this->setTemplatePath($tplpath);
    $this->setTemplateName($tplname);
  }
}