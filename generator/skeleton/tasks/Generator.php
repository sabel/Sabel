<?php

/**
 * Generator
 *
 * @category   Sakle
 * @package    org.sabel.sakle
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Generator extends Sabel_Sakle_Task
{
  public function run()
  {
    $environment = $this->getEnvironment();
    $target = $this->checkArguments();
    
    if ($target === "model") {
      $this->generateModel($environment);
    } elseif ($target === "controller") {
      $this->generateController();
    }
  }
  
  private function generateModel($environment)
  {
    define("ENVIRONMENT", $environment);
    Sabel_DB_Config::initialize(CONFIG_DIR_PATH . DS . "connection" . PHP_SUFFIX);
    
    $models = array();
    array_shift($this->arguments);
    
    foreach ($this->arguments as $mdlName) {
      $filePath = MODELS_DIR_PATH . DS . $mdlName . PHP_SUFFIX;
      if (file_exists($filePath)) {
        $classFile = $mdlName . PHP_SUFFIX;
        $this->warning("model '{$classFile}' already exists. (SKIP)");
      } else {
        $columns = MODEL($mdlName)->getColumnNames();
        $lines   = array();
        $lines[] = "<?php" . PHP_EOL;
        $lines[] = "Sabel_DB_Model_Localize::setColumnNames(";
        $lines[] = "  \"{$mdlName}\",";
        $lines[] = "  array(";
        
        foreach ($columns as $column) {
          $lines[] = "    \"{$column}\" => \"{$column}\",";
        }
        
        $lines[] = "  )";
        $lines[] = ");" . PHP_EOL;
        $lines[] = "class {$mdlName} extends Db_Model";
        $lines[] = "{";
        $lines[] = "  ";
        $lines[] = "}";
        
        $this->success("create model {$mdlName}");
        $fp = fopen($filePath, "w");
        fwrite($fp, implode(PHP_EOL, $lines));
        fclose($fp);
      }
    }
  }
  
  private function generateController()
  {
    array_shift($this->arguments);
    $argc = count($this->arguments);
    
    if ($argc === 1) {
      $module = "index";
      $controller = $this->arguments[0];
    } elseif ($argc === 2) {
      $module = $this->arguments[0];
      $controller = $this->arguments[1];
    } else {
      $this->error("too many arguments");
      $this->usage();
      exit;
    }
    
    $clsName = ucfirst($module) . "_Controllers_" . ucfirst($controller);
    
    $mPath = MODULES_DIR_PATH . DS . lcfirst($module);
    if (!is_dir($mPath)) mkdir($mPath);
    
    $cPath = $mPath . DS . "controllers";
    if (!is_dir($cPath)) mkdir($cPath);
    
    $vPath = $mPath . DS . VIEW_DIR_NAME;
    if (!is_dir($vPath)) mkdir($vPath);
    
    $filePath = $cPath . DS . $controller . PHP_SUFFIX;
    if (is_file($filePath)) {
      $this->error("controller $controller already exists.");
      exit;
    }
    
    $actions = array();
    $console = new Sabel_Console();
    
    while (true) {
      $input = $console->read("action");
      if ($input === false) break;
      if (!in_array($input, $actions, true)) {
        $actions[] = $input;
      }
    }
    
    $code = array("<?php" . PHP_EOL);
    $code[] = "class $clsName extends Sabel_Controller_Page";
    $code[] = "{";
    
    if ($actions) {
      foreach ($actions as $action) {
        $code[] = "  public function $action()";
        $code[] = "  {";
        $code[] = "    ";
        $code[] = "  }";
        $code[] = "  ";
      }
    }
    
    $code[] = "}";
    
    file_put_contents($filePath, implode(PHP_EOL, $code));
    $this->success("create controller $clsName");
    
    $tplDir = $vPath . DS . lcfirst($controller);
    if (!is_dir($tplDir)) mkdir ($tplDir);
    
    if (empty($actions)) return;
    
    foreach ($actions as $action) {
      $tplPath = $tplDir . DS . $action. TPL_SUFFIX;
      file_put_contents($tplPath, PHP_EOL);
      $this->success("create template $action" . TPL_SUFFIX);
    }
  }
  
  private function getEnvironment()
  {
    if (Sabel_Console::hasOption("e", $this->arguments)) {
      return environment(Sabel_Console::getOption("e", $this->arguments));
    } else {
      return DEVELOPMENT;
    }
  }
  
  private function checkArguments()
  {
    $arguments = $this->arguments;
    
    if (count($arguments) < 2) {
      $this->usage();
      exit;
    }
    
    $target = $arguments[0];
    
    if (!in_array($target, array("model", "controller"), true)) {
      $this->usage();
      exit;
    }
    
    return $target;
  }
  
  public function usage()
  {
    echo "Usage: sakle Generator\n";
  }
}
