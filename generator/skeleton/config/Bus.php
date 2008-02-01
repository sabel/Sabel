<?php

class Config_Bus extends Sabel_Bus_Config
{
  public function configure()
  {
    $processors = array("request",     "router",   "addon",
                        "controller",  "location", "helper",
                        "initializer", "executer", "exception",
                        "response",    "view");
                        
    $bus = $this->bus;
    $baseDir = RUN_BASE . DS . LIB_DIR_NAME . DS . "processor" . DS;
    
    foreach ($processors as $name) {
      $processor = ucfirst($name);
      Sabel::fileUsing($baseDir . $processor. PHP_SUFFIX, true);
      $className = "Processor_" . $processor;
      $bus->addProcessor(new $className($name));
    }
    
    return $this;
  }
}
