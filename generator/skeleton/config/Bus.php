<?php

class Config_Bus extends Sabel_Bus_Config
{
  public function configure()
  {
    $processors = array("addon", "request", "router",
                        "controller", "location", "initializer",
                        "redirector", "executer", "exception",
                        "response", "view");
                        
    $baseDir = RUN_BASE . DS . LIB_DIR_NAME . DS . "processor" . DS;
    
    foreach ($processors as $name) {
      $processor = ucfirst($name);
      Sabel::fileUsing($baseDir . $processor. PHP_SUFFIX, true);
      $className = "Processor_" . $processor;
      $this->add(new $className($name));
    }
    
    return $this;
  }
}
