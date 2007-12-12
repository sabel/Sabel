<?php

/**
 * Generator
 *
 * @category  Sakle
 * @package   org.sabel.sakle
 * @author    Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright 2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Generator extends Sabel_Sakle_Task
{
  protected $arguments = array();
  
  public function run($arguments)
  {
    $environment = $this->getEnvironment($arguments);
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
    Sabel_DB_Config::initialize();
    
    $models = array();
    $args = $this->arguments;
    
    unset($args[0]);
    unset($args[1]);
    
    foreach ($args as $mdlName) {
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
        $lines[] = "class {$mdlName} extends Sabel_DB_Abstract_Model";
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
    $argc = count($this->arguments);
    
    if ($argc === 3) {
      $module = "Index";
      $method = "create";
      $controller = $this->arguments[2];
    } elseif ($argc === 4) {
      $module = $this->arguments[2];
      $controller = $this->arguments[3];
      $method = "create";
    } elseif ($argc === 5) {
      $module = $this->arguments[2];
      $controller = $this->arguments[3];
      $method = $this->arguments[4];
    } else {
      $this->error("too many arguments");
      $this->usage();
      exit;
    }
    
    $clsName = ucfirst($module) . "_Controllers_" . $controller;
    
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
    
    $prefixes = array(""         => "create",
                      "correct"  => "confirm",
                      "confirm"  => "do",
                      "do"       => "complete",
                      "complete" => "");
                      
    $code = array();
    $code[] = "<?php";
    $code[] = "";
    $code[] = "/**";
    $code[] = " * @executer flow";
    $code[] = " */";
    $code[] = "class $clsName extends Flow_Page";
    $code[] = "{";
    
    $i = 0;
    $last = count($prefixes) - 1;
    $tpls = array();
    
    foreach ($prefixes as $m => $next) {
      $name = ($m === "") ? $method : $m . ucfirst($method);
      $tpls[] = $name;
      $code[] = "  /**";
      
      if ($next === "") {
        $code[] = "   * @end flow";
        $code[] = "   */";
        $code[] = "  public function $name()";
        $code[] = "  {";
        $code[] = "  }";
        $code[] = "}";
      } else {
        if ($m === "") {
          $code[] = "   * @flow start";
        }
        
        $code[] = "   * @next {$next}" . ucfirst($method);
        $code[] = "   */";
        $code[] = "  public function {$name}()";
        $code[] = "  {";
        $code[] = "  }";
        $code[] = "  ";
      }
      
      $i++;
    }
    
    $tplDir = $vPath . DS . lcfirst($controller);
    if (!is_dir($tplDir)) mkdir ($tplDir);
    
    foreach ($tpls as $tpl) {
      $tplPath = $tplDir . DS . $tpl . TPL_SUFFIX;
      file_put_contents($tplPath, PHP_EOL);
      $this->success("create template $tpl" . TPL_SUFFIX);
    }
    
    file_put_contents($filePath, implode(PHP_EOL, $code));
    $this->success("create controller $clsName");
  }
  
  private function getEnvironment($arguments)
  {
    $index = array_search("-e", $arguments, true);
    
    if ($index === false) {
      $this->arguments = $arguments;
      return DEVELOPMENT;
    }
    
    if (isset($arguments[$index + 1])) {
      $environment = environment($arguments[$index + 1]);
      unset($arguments[$index]);
      unset($arguments[$index + 1]);
      $this->arguments = array_values($arguments);
      return $environment;
    }
  }
  
  private function checkArguments()
  {
    $arguments = $this->arguments;
    
    if (count($arguments) <= 2) {
      $this->usage();
      exit;
    }
    
    $target = $arguments[1];
    
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
