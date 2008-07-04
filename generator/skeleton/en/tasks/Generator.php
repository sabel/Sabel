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
  protected $fs = null;
  protected $renderer = "php";
  protected $skeletonDir = "";
  
  public function initialize()
  {
    $this->fs = new Sabel_Util_FileSystem();
    $this->skeletonDir = RUN_BASE . DS . "tasks" . DS . "skeleton";
  }
  
  public function run()
  {
    $this->defineEnvironmentByOption();
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
  
  private function generateController()
  {
    array_shift($this->arguments);
    
    $mdlName  = $this->arguments[0];
    $module   = (isset($this->arguments[1])) ? $this->arguments[1] : "index";
    $formName = lcfirst($mdlName) . "Form";
    
    $controllerName = ucfirst($module) . "_Controllers_" . $mdlName;
    
    $orderColumns = array();
    $_model = MODEL($mdlName);
    $metadata = $_model->getMetadata();
    $versionColumn = $_model->getVersionColumn();
    $columns = $metadata->getColumns();
    
    $allowColumns = array();
    foreach ($columns as $column) {
      if (!$column->increment) {
        $allowColumns[] = '"' . $column->name . '"';
      }
      
      if ($column->isNumeric() || $column->isDatetime() || $column->isDate()) {
        $orderColumns[] = '"' . $column->name . '"';
      }
    }
    
    $primaryColumn = $metadata->getPrimaryKey();
    $orderColumns  = "array(" . implode(", ", $orderColumns) . ")";
    $allowColumns  = implode(", ", $allowColumns);
    
    $vars = get_defined_vars();
    $this->_generateController($vars, "", $module . DS . "controllers" . DS . $mdlName . ".php");
    
    $tplDir = MODULES_DIR_PATH . DS . $module . DS . VIEW_DIR_NAME . DS . lcfirst($mdlName);
    if (!$this->fs->isDir($tplDir)) $this->fs->mkdir($tplDir);
    
    $vars = get_defined_vars();
    $this->_generateTemplates($vars, $tplDir, "general");
  }
  
  private function generateFlowController()
  {
    array_shift($this->arguments);
    
    $mdlName  = $this->arguments[0];
    $module   = (isset($this->arguments[1])) ? $this->arguments[1] : "index";
    $formName = lcfirst($mdlName) . "Form";
    
    $controllerName = ucfirst($module) . "_Controllers_" . $mdlName;
    
    $orderColumns = array();
    $_model = MODEL($mdlName);
    $metadata = $_model->getMetadata();
    $versionColumn = $_model->getVersionColumn();
    $columns = $metadata->getColumns();
    
    foreach ($columns as $column) {
      if ($column->isNumeric() || $column->isDatetime() || $column->isDate()) {
        $orderColumns[] = '"' . $column->name . '"';
      }
    }
    
    $primaryColumn = $metadata->getPrimaryKey();
    $orderColumns  = "array(" . implode(", ", $orderColumns) . ")";
    
    $vars = get_defined_vars();
    $this->_generateController($vars, "Flow", $module . DS . "controllers" . DS . $mdlName . ".php");
    
    $tplDir = MODULES_DIR_PATH . DS . $module . DS . VIEW_DIR_NAME . DS . lcfirst($mdlName);
    if (!$this->fs->isDir($tplDir)) $this->fs->mkdir($tplDir);
    
    $vars = get_defined_vars();
    $this->_generateTemplates($vars, $tplDir, "flow");
  }
  
  private function generateLoginController()
  {
    array_shift($this->arguments);
    
    $mdlName = $this->arguments[0];
    $module  = (isset($this->arguments[1])) ? $this->arguments[1] : "index";
    
    $controllerName = ucfirst($module) . "_Controllers_Login";
    $emailColumns = array("email", "mail_address", "mailaddress");
    $metadata = MODEL($mdlName)->getMetadata();
    
    $emailColumn = "email";
    foreach ($metadata->getColumns() as $column) {
      if (in_array($column->name, $emailColumns, true)) {
        $emailColumn = $column->name;
        break;
      }
    }
    
    $primaryColumn = $metadata->getPrimaryKey();
    
    $vars = get_defined_vars();
    $this->_generateController($vars, "Login", $module . DS . "controllers" . DS . "Login.php");
    
    $tplDir = MODULES_DIR_PATH . DS . $module . DS . VIEW_DIR_NAME . DS . "login";
    if (!$this->fs->isDir($tplDir)) $this->fs->mkdir($tplDir);
    
    $vars = get_defined_vars();
    $this->_generateTemplates($vars, $tplDir, "login");
  }
  
  private function generateUploadController()
  {
    array_shift($this->arguments);
    
    $ctrlName = ucfirst($this->arguments[0]);
    $module   = (isset($this->arguments[1])) ? $this->arguments[1] : "index";
    
    $controllerName = ucfirst($module) . "_Controllers_" . $ctrlName;
    $rfc1867_prefix = ini_get("apc.rfc1867_prefix");
    
    $vars = get_defined_vars();
    $this->_generateController($vars, "Upload", $module . DS . "controllers" . DS . $ctrlName . ".php");
    
    $tplDir = MODULES_DIR_PATH . DS . $module . DS . VIEW_DIR_NAME . DS . lcfirst($ctrlName);
    if (!$this->fs->isDir($tplDir)) $this->fs->mkdir($tplDir);
    
    $vars = get_defined_vars();
    $this->_generateTemplates($vars, $tplDir, "uploader");
  }
  
  private function _generateController($vars, $name, $path)
  {
    extract($vars, EXTR_OVERWRITE);
    
    ob_start();
    include ($this->skeletonDir . DS . "controllers" . DS . $name . "Controller.php");
    $contents = str_replace("<#", "<?", ob_get_clean());
    
    $this->fs->cd(MODULES_DIR_PATH);
    $this->fs->mkfile($path)->write($contents)->save();
    $this->success("Generate Controller " . MODULES_DIR_NAME . DS . $path);
  }
  
  private function _generateTemplates($vars, $targetDir, $type)
  {
    extract($vars, EXTR_OVERWRITE);
    
    $templatesDir = $this->skeletonDir . DS . "templates" . DS . $this->renderer . DS . $type;
    
    foreach (scandir($templatesDir) as $item) {
      if ($item{0} === ".") continue;
      
      ob_start();
      include ($templatesDir . DS . $item);
      $contents = str_replace(array("<#", "#>"), array("<?", "?>"), ob_get_clean());
      file_put_contents($targetDir . DS . $item, $contents);
      
      $relativePath = substr($targetDir . DS . $item, strlen(MODULES_DIR_PATH) + 1);
      $this->success("Generate Template " . MODULES_DIR_NAME . DS . $relativePath);
    }
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
    
    $renderers = array("sabel", "smarty", "savant");
    
    if (Sabel_Console::hasOption("r", $arguments)) {
      $opts = Sabel_Console::getOption("r", $arguments);
      if (isset($opts[0])) {
        $renderer = strtolower($opts[0]);
        if (in_array($renderer, $renderers, true)) {
          $this->renderer = $renderer;
        }
      }
    }
    
    $target = strtolower($arguments[0]);
    $types  = array("model", "controller", "flowcontroller",
                    "logincontroller", "uploadcontroller");
    
    if (!in_array($target, $types, true)) {
      $this->usage();
      exit;
    }
    
    $this->arguments = $arguments;
    
    return $target;
  }
  
  public function usage()
  {
    echo "Usage: sakle Generator Model MODEL_NAME\n";
    echo "       sakle Generator Controller MODEL_NAME [MODULE_NAME]\n";
    echo "       sakle Generator FlowController MODEL_NAME [MODULE_NAME]\n";
    echo "       sakle Generator LoginController MODEL_NAME [MODULE_NAME]\n";
    echo "       sakle Generator UploadController CONTROLLER_NAME [MODULE_NAME]\n";
    echo "\n";
  }
}
