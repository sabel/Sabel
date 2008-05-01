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
    define("ENVIRONMENT", $this->getEnvironment());
    Sabel_DB_Config::initialize(new Config_Database());
    
    $method = "generate" . $this->checkArguments();
    $this->$method();
  }
  
  private function generateModel()
  {
    $models = array();
    array_shift($this->arguments);
    
    foreach ($this->arguments as $mdlName) {
      $filePath = MODELS_DIR_PATH . DS . $mdlName . ".php";
      if (file_exists($filePath)) {
        $classFile = $mdlName . ".php";
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
        $lines[] = "class {$mdlName} extends Sabel_DB_Model";
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
  
  private function generateFlowController()
  {
    array_shift($this->arguments);
    
    $mdlName  = $this->arguments[0];
    $module   = (isset($this->arguments[1])) ? $this->arguments[1] : "index";
    $formName = lcfirst($mdlName) . "Form";
    $skelDir  = dirname(__FILE__) . DS . "generator";
    
    $controllerName = ucfirst($module) . "_Controllers_" . $mdlName;
    
    $orderColumns = array();
    $columns = MODEL($mdlName)->getColumns();
    foreach ($columns as $column) {
      if ($column->isNumeric() || $column->isDatetime() || $column->isDate()) {
        $orderColumns[] = '"' . $column->name . '"';
      }
    }
    
    $orderColumns = "array(" . implode(", ", $orderColumns) . ")";
    
    ob_start();
    include ($skelDir . DS . "FlowController.php");
    $contents = str_replace("<#", "<?", ob_get_clean());
    
    $fs = new Sabel_Util_FileSystem(MODULES_DIR_PATH);
    $file = $fs->mkfile($module . DS . "controllers" . DS . $mdlName . ".php");
    $file->write($contents)->save();
    
    $tplDir = MODULES_DIR_PATH . DS . $module . DS . VIEW_DIR_NAME . DS . lcfirst($mdlName);
    if (!$fs->isDir($tplDir)) $fs->mkdir($tplDir, 0775);
    
    foreach (scandir($skelDir . DS . "flowControllerTemplates") as $item) {
      if ($item{0} === ".") continue;
      ob_start();
      include ($skelDir . DS . "flowControllerTemplates" . DS . $item);
      $contents = str_replace(array("<#", "#>"), array("<?", "?>"), ob_get_clean());
      file_put_contents($tplDir . DS . $item, $contents);
    }
  }
  
  private function generateUploadController()
  {
    array_shift($this->arguments);
    
    $ctrlName = ucfirst($this->arguments[0]);
    $module   = (isset($this->arguments[1])) ? $this->arguments[1] : "index";
    $skelDir  = dirname(__FILE__) . DS . "generator";
    
    $controllerName = ucfirst($module) . "_Controllers_" . $ctrlName;
    
    ob_start();
    include ($skelDir . DS . "UploadController.php");
    $contents = str_replace("<#", "<?", ob_get_clean());
    
    $fs = new Sabel_Util_FileSystem(MODULES_DIR_PATH);
    $file = $fs->mkfile($module . DS . "controllers" . DS . $ctrlName . ".php");
    $file->write($contents)->save();
    
    $tplDir = MODULES_DIR_PATH . DS . $module . DS . VIEW_DIR_NAME . DS . lcfirst($ctrlName);
    if (!$fs->isDir($tplDir)) $fs->mkdir($tplDir, 0775);
    
    $tplName  = "upload" . TPL_SUFFIX;
    
    ob_start();
    include ($skelDir . DS . "uploadControllerTemplates" . DS . $tplName);
    $contents = str_replace(array("<#", "#>"), array("<?", "?>"), ob_get_clean());
    file_put_contents($tplDir . DS . $tplName, $contents);
  }
  
  private function getEnvironment()
  {
    if (Sabel_Console::hasOption("e", $this->arguments)) {
      $opts = Sabel_Console::getOption("e", $this->arguments);
      if (($env = environment($opts[0])) === null) {
        $this->error("invalid environment.");
        exit;
      } else {
        return $env;
      }
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
    
    $target = strtolower($arguments[0]);
    $types  = array("model", "flowcontroller", "uploadcontroller");
    
    if (!in_array($target, $types, true)) {
      $this->usage();
      exit;
    }
    
    return $target;
  }
  
  public function usage()
  {
    echo "Usage: sakle Generator Model MODEL_NAME\n";
    echo "Usage: sakle Generator FlowController MODEL_NAME [MODULE_NAME]\n";
    echo "Usage: sakle Generator UploadController CONTROLLER_NAME [MODULE_NAME]\n";
    echo "\n";
  }
}
