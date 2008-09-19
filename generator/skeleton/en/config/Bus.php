<?php

class Config_Bus extends Sabel_Bus_Config
{
  protected $logging    = false;
  
  protected $processors = array("addon"       => "Processor_Addon",
                                "request"     => "Processor_Request",
                                "response"    => "Processor_Response",
                                "router"      => "Processor_Router",
                                "session"     => "Processor_Session",
                                "controller"  => "Processor_Controller",
                                "helper"      => "Processor_Helper",
                                "initializer" => "Processor_Initializer",
                                "action"      => "Processor_Action",
                                "view"        => "Processor_View");
  
  protected $interfaces = array("request"     => "Sabel_Request",
                                "response"    => "Sabel_Response",
                                "session"     => "Sabel_Session",
                                "view"        => "Sabel_View",
                                "controller"  => "Sabel_Controller_Page");
  
  protected $configs    = array("map"         => "Config_Map",
                                "addon"       => "Config_Addon",
                                "database"    => "Config_Database");
  
  public function getProcessors()
  {
    $baseDir = RUN_BASE . DS . LIB_DIR_NAME . DS . "processor" . DS;
    
    foreach (array_keys($this->processors) as $name) {
      Sabel::fileUsing($baseDir . ucfirst($name) . ".php", true);
    }
    
    return $this->processors;
  }
}
