<?php

class Config_Bus extends Sabel_Bus_Config
{
  protected $logging    = false;
  
  protected $processors = array("request"     => "Processor_Request",
                                "router"      => "Processor_Router",
                                "session"     => "Processor_Session",
                                "addon"       => "Processor_Addon",
                                "controller"  => "Processor_Controller",
                                "helper"      => "Processor_Helper",
                                "initializer" => "Processor_Initializer",
                                "executer"    => "Processor_Executer",
                                "response"    => "Processor_Response",
                                "view"        => "Processor_View");
  
  protected $interfaces = array("request"     => "Sabel_Request",
                                "session"     => "Sabel_Session",
                                "response"    => "Sabel_Response",
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
