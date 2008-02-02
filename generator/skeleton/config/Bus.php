<?php

class Config_Bus implements Sabel_Config
{
  public function configure()
  {
    $processors = array("request",     "router",   "addon",
                        "controller",  "location", "helper",
                        "initializer", "executer", "exception",
                        "response",    "view");
                        
    $bus = new Sabel_Bus();
    $baseDir = RUN_BASE . DS . LIB_DIR_NAME . DS . "processor" . DS;
    
    foreach ($processors as $name) {
      $processor = ucfirst($name);
      Sabel::fileUsing($baseDir . $processor . PHP_SUFFIX, true);
      $className = "Processor_" . $processor;
      $bus->addProcessor(new $className($name));
    }
    
    $bus->setConfig("map",   new Config_Map());
    $bus->setConfig("addon", new Config_Addon());
    
    return $bus;
  }
}
