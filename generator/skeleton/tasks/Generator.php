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
          $lines[] = "    \"{$column}\" => \"\",";
        }
        
        $lines[] = "  )";
        $lines[] = ");" . PHP_EOL;
        $lines[] = "class {$mdlName} extends Sabel_DB_Abstract_Model";
        $lines[] = "{";
        $lines[] = "  ";
        $lines[] = "}";
        
        $fp = fopen($filePath, "w");
        fwrite($fp, implode(PHP_EOL, $lines));
        fclose($fp);
      }
    }
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
