<?php

class TestProcessor_Location extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    list ($module, $controller) = $bus->get("destination")->toArray();
    
    $controller = new Sabel_View_Template_File($module . DS . VIEW_DIR_NAME . DS . $controller . DS);
    $view = new Sabel_View_Object("controller", $controller);
    
    $module = new Sabel_View_Template_File($module . DS . VIEW_DIR_NAME . DS);
    $view->addTemplate("module", $module);
    
    $app = new Sabel_View_Template_File(VIEW_DIR_NAME . DS);
    $view->addTemplate("app", $app);
    
    $bus->set("view", $view);
    $bus->get("controller")->setAttribute("view", $view);
  }
}
