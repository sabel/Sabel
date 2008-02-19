<?php

class Config_Bus extends Sabel_Bus_Config
{
  protected $processors = array("request"     => "Processor_Request",
                                "router"      => "Processor_Router",
                                "session"     => "Processor_Session",
                                "addon"       => "Processor_Addon",
                                "controller"  => "Processor_Controller",
                                "location"    => "Processor_Location",
                                "helper"      => "Processor_Helper",
                                "initializer" => "Processor_Initializer",
                                "executer"    => "Processor_Executer",
                                "response"    => "Processor_Response",
                                "view"        => "Processor_View");
  
  protected $configs = array("map"      => "Config_Map",
                             "addon"    => "Config_Addon",
                             "database" => "Config_Database");
  
  public function getProcessors()
  {
    $baseDir = RUN_BASE . DS . LIB_DIR_NAME . DS . "processor" . DS;
    
    foreach (array_keys($this->processors) as $name) {
      Sabel::fileUsing($baseDir . ucfirst($name) . PHP_SUFFIX, true);
    }
    
    return $this->processors;
  }
}
