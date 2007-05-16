<?php

class Sabel_Controller_Plugin_Dependency extends Sabel_Controller_Page_Plugin
{
  private $helperPrefix = ".php";
  private $dependency = null;
  
  public function onCreateController($destination)
  {
    list($m, $c, $a) = $destination->toArray();
    
    $appDir       = "app";
    $depDir       = "dependency";
    $sharedHelper = "application";
    
    $helpers = array(array($appDir, $depDir, $sharedHelper),
                     array($appDir, $m, $depDir, $sharedHelper),
                     array($appDir, $m, $depDir, ucfirst($c)),
                     array($appDir, $m, $depDir, $c, $a));
    
    if (is_file($this->createPath($helpers[0]))) {
      //
    } elseif (is_file($this->createPath($helpers[1]))) {
      //
    } elseif (is_file($this->createPath($helpers[2]))) {
      $className = ucfirst($helpers[2][1]) . "_" . ucfirst($helpers[2][2]) ."_". ucfirst($helpers[2][3]);
      $ins = new $className();
      $ins->setController($this->controller);
      $ins->setter();
      $this->dependency = $ins;
    } elseif (is_file($this->createPath($helpers[3]))) {
      $className = ucfirst($helpers[2][1]) . "_" .
                   ucfirst($helpers[2][2]) . "_" .
                   ucfirst($helpers[2][3]) . "_" .
                   ucfirst($helpers[2][4]);
    }
  }
  
  public function onExecuteAction($method)
  {
    $result = false;
    if (is_object($this->dependency)) {
      $result = $this->dependency->execute($method);
    }
    return $result;
  }
  
  private function createPath($helper)
  {
    return RUN_BASE . "/" . join('/', $helper) . $this->helperPrefix;
  }
}
