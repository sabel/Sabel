<?php

/**
 * Sabel_View_Locator
 *
 * @category   View
 * @package    org.sabel.view
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_View_Locator_File implements Sabel_View_Locator
{  
  public function locate($condition)
  {
    if ($condition->isActionDefault()) {
      return $this->locateFromActionDefault($condition);
    } else {
      return $this->locateFromPathAndName($condition);
    }
  }
  
  private final function locateFromActionDefault($condition)
  {
    $candidate  = $condition->getCandidate();
    
    $module     = $candidate->getModule();
    $controller = $candidate->getController();
    $action     = $candidate->getAction();
    
    $tplpath  = RUN_BASE;
    $tplpath .= Sabel_Const::MODULES_DIR;
    $tplpath .= $module . DIR_DIVIDER;
    
    $controllerSpecificTplpath = null;
    $controllerSpecific = null;
    
    if (is_dir($tplpath."views/".$controller)) {
      $controllerSpecificTplpath = $tplpath."views/".$controller;
      
      $controllerSpecific = $controllerSpecificTplpath . "/" .
                            $action . Sabel_Const::TEMPLATE_POSTFIX;
    }
    
    $location = new stdClass();
    
    $tplpath .= Sabel_Const::TEMPLATE_DIR;
    
    // make name of template such as "controller.method.tpl"
    $tplname  = $controller;
    $tplname .= Sabel_Const::TEMPLATE_NAME_SEPARATOR;
    $tplname .= $action;
    
    if (!$condition->getPartial() && is_readable($controllerSpecific)) {
      $tplname = "/" . $action . Sabel_Const::TEMPLATE_POSTFIX;
      $location->renderer = new Sabel_View_Renderer_Class();
      $location->path     = $controllerSpecificTplpath;
    } elseif (is_readable($tplpath . $tplname . ".pjs")) {
      $tplname .= ".pjs";
      $location->renderer = new Sabel_View_Renderer_PHP();
      $location->path     = $tplpath;
    } elseif(is_readable($tplpath . $tplname . Sabel_Const::TEMPLATE_POSTFIX)) {
      $tplname .= Sabel_Const::TEMPLATE_POSTFIX;
      $location->renderer = new Sabel_View_Renderer_Class();
      $location->path = $tplpath;
    } else {
      $location->renderer = new Sabel_View_Renderer_Class();
      $location->path = $tplpath;
    }
    
    $location->name = $tplname;
    
    $resource = new Sabel_View_Resource_Template();
    $resource->setRenderer($location->renderer);
    $resource->setPath($location->path);
    $resource->setName($location->name);
    
    $result = new stdClass();
    $result->template = $resource;
    $result->layout   = $this->locateLayout($location->path, "layout.tpl");
    
    return $result;
  }
  
  private final function locateFromPathAndName($condition)
  {
    $candidate  = $condition->getCandidate();
    
    $module     = $candidate->getModule();
    $controller = $candidate->getController();
    $action     = $candidate->getAction();
    
    $tplpath  = RUN_BASE;
    $tplpath .= Sabel_Const::MODULES_DIR;
    $tplpath .= $module . DIR_DIVIDER;
    
    $location = new stdClass();
    
    $location->name = $name = $condition->getName();
    
    $controllerSpecificTplpath = null;
    $controllerSpecific = null;
    
    if (is_dir($tplpath . "views/" . $controller)) {
      $controllerSpecificTplpath = $tplpath . "views/" . $controller;
      
      $controllerSpecific = $controllerSpecificTplpath . "/" .
                            $name . Sabel_Const::TEMPLATE_POSTFIX;
    } else {
      $tplpath .= "views/";
    }
        
    $fullpath = $tplpath . $controller.".".$name .Sabel_Const::TEMPLATE_POSTFIX;
    
    if (is_readable(RUN_BASE . "/app/views/" . $name)) {
      $location->renderer = new Sabel_View_Renderer_Class();
      $location->path = RUN_BASE . "/app/views/";
    } elseif (is_readable($tplpath . Sabel_Const::TEMPLATE_DIR . $name)) {
      $location->renderer = new Sabel_View_Renderer_Class();
      $location->path     = $tplpath . Sabel_Const::TEMPLATE_DIR;
    } elseif (!$condition->getPartial() && is_readable($controllerSpecific)) {
      $location->renderer = new Sabel_View_Renderer_Class();
      $location->path     = $controllerSpecificTplpath;
      $location->name     = "/".$name . Sabel_Const::TEMPLATE_POSTFIX;
    } elseif (is_readable($fullpath)) {
      $location->renderer = new Sabel_View_Renderer_Class();
      $location->path = $tplpath;
      $location->name = $controller.".".$name.Sabel_Const::TEMPLATE_POSTFIX;
    } elseif ($tplpath.$name) {
      $location->renderer = new Sabel_View_Renderer_Class();
      $location->path = $tplpath;
      $location->name = $name;
    }
    
    $resource = new Sabel_View_Resource_Template();
    $resource->setRenderer($location->renderer);
    $resource->setPath($location->path);
    $resource->setName($location->name);
    
    return $resource;
  }
  
  public function locateLayout($path, $layout)
  {
    $usersLayoutName = $layout . Sabel_Const::TEMPLATE_POSTFIX;
    
    $result = new stdClass();
    
    if (is_file($path . $usersLayoutName)) {
      $name  = $usersLayoutName;
      $result->path = $path;
      $result->name = $name;
    } elseif (is_file($path . Sabel_Const::DEFAULT_LAYOUT)) {
      $result->path = $path;
      $result->name = Sabel_Const::DEFAULT_LAYOUT;
    } elseif (is_file(RUN_BASE . "/app/views/" . Sabel_Const::DEFAULT_LAYOUT)) {
      $result->name = Sabel_Const::DEFAULT_LAYOUT;
      $result->path = RUN_BASE . "/app/views/";
    } elseif (is_file($path . Sabel_Const::DEFAULT_LAYOUT)) {
      $result->name  = Sabel_Const::DEFAULT_LAYOUT;
      $result->path = $path;
    }
    
    $result->renderer = new Sabel_View_Renderer_Class();
    
    $resource = new Sabel_View_Resource_Template();
    $resource->setRenderer($result->renderer);
    $resource->setPath($result->path);
    $resource->setName($result->name);
    
    return $resource;
  }
}