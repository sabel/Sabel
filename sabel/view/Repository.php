<?php

/**
 * Sabel_View_Repository
 *
 * @category   View
 * @package    org.sabel.view
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_View_Repository extends Sabel_Object
{
  protected
    $templates = array(),
    $tplName   = null;
    
  public function __construct($name, Sabel_View_Template $template)
  {
    $this->addTemplate($name, $template);
  }
  
  public function addTemplate($name, Sabel_View_Template $template)
  {
    if (is_string($name) && $name !== "") {
      $this->templates[$name] = $template;
    } else {
      $message = "argument(1) must be a string.";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  public function getTemplate($name)
  {
    if (isset($this->templates[$name])) {
      return $this->templates[$name];
    } else {
      return null;
    }
  }
  
  public function getTemplates()
  {
    return $this->templates;
  }
  
  public function setTemplateName($tplName)
  {
    $this->tplName = $tplName;
  }
  
  public function getTemplateName()
  {
    return $this->tplName;
  }
  
  public function getValidTemplate($tplPath = null)
  {
    if ($tplPath === null && $this->tplName === null) {
      throw new Sabel_Exception_Runtime("template name is null.");
    } else {
      if ($tplPath === null) $tplPath = $this->tplName;
      foreach ($this->templates as $template) {
        $template->name($tplPath);
        if ($template->isValid()) return $template;
      }
    }
    
    return null;
  }
  
  public function getContents($tplPath = null)
  {
    $template = $this->getValidTemplate($tplPath);
    return ($template !== null) ? $template->getContents() : "";
  }
  
  public function create($name, $tplPath, $contents = "")
  {
    if ($template = $this->getTemplate($name)) {
      $template->name($tplPath);
      return $template->create($contents);
    } else {
      throw new Sabel_Exception_Runtime("such a location name is not registered.");
    }
  }
  
  public function delete($name, $tplPath)
  {
    if ($template = $this->getTemplate($name)) {
      $template->name($tplPath);
      return $template->delete();
    } else {
      throw new Sabel_Exception_Runtime("such a location name is not registered.");
    }
  }
  
  public function isValid($name, $tplPath)
  {
    if ($template = $this->getTemplate($name)) {
      $template->name($tplPath);
      return $template->isValid();
    } else {
      throw new Sabel_Exception_Runtime("such a location name is not registered.");
    }
  }
}
