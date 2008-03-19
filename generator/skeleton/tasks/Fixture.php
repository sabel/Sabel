<?php

/**
 * Fixture
 *
 * @category   Sakle
 * @package    org.sabel.sakle
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Fixture extends Sabel_Sakle_Task
{
  protected $fixturesDir = "";
  
  public function initialize()
  {
    $this->fixturesDir = RUN_BASE . DS . "tests" . DS . "fixture";
  }
  
  public function run()
  {
    if (count($this->arguments) < 2) {
      $this->usage();
      exit;
    }
    
    $isExport = false;
    if (Sabel_Console::hasOption("export", $this->arguments)) {
      $index = array_search("--export", $this->arguments, true);
      unset($this->arguments[$index]);
      $this->arguments = array_values($this->arguments);
      $isExport = true;
    }
    
    $this->defineEnvironment();
    Sabel_DB_Config::initialize(new Config_Database());
    
    if ($isExport) {
      $this->export();
    } else {
      $fixtureName = $this->arguments[1];
      
      if ($fixtureName === "all") {
        foreach (scandir($this->fixturesDir) as $item) {
          if ($item === "." || $item === "..") continue;
          Sabel::fileUsing($this->fixturesDir . DS . $item, true);
          $className = "Fixture_" . substr($item, 0, strlen($item) - 4);
          $instance  = new $className();
          $instance->upFixture();
        }
      } else {
        $filePath = $this->fixturesDir . DS . $fixtureName . ".php";
        if (Sabel::fileUsing($filePath, true)) {
          $className = "Fixture_" . $fixtureName;
          $instance  = new $className();
          $instance->upFixture();
        } else {
          $this->error("no such fixture file. '{$filePath}'");
        }
      }
    }
  }
  
  protected function defineEnvironment()
  {
    if (!defined("ENVIRONMENT")) {
      if (($env = environment($this->arguments[0])) === null) {
        $this->error("invalid environment. use 'development' or 'test', 'production'.");
      } else {
        define("ENVIRONMENT", $env);
      }
    }
  }
  
  public function usage()
  {
    echo "Usage: sakle Fixture ENVIRONMENT FIXTURE_NAME " . PHP_EOL;
    echo PHP_EOL;
    echo "  ENVIRONMENT:  production | test | development" . PHP_EOL;
    echo "  FIXTURE_NAME: fixture name or 'all'" . PHP_EOL;
    echo PHP_EOL;
    echo "Example: sakle Fixture development User" . PHP_EOL;
    echo PHP_EOL;
  }
  
  protected function export()
  {
    unset($this->arguments[0]);
    
    foreach ($this->arguments as $mdlName) {
      $lines  = array();
      $models = MODEL($mdlName)->select();
      foreach ($models as $model) {
        $data = $this->createLine($model->toArray());
        $lines[] = '$model->insert(' . $data . ');';
      }
      
      $code = array("<?php" . PHP_EOL);
      $code[] = "class Fixture_{$mdlName}";
      $code[] = "{";
      $code[] = "  public function upFixture()";
      $code[] = "  {";
      $code[] = '    $model = MODEL("' . $mdlName . '");';
      
      foreach ($lines as $line) {
        $code[] = "    $line";
      }
      
      $code[] = "  }" . PHP_EOL;
      $code[] = "  public function downFixture()";
      $code[] = "  {";
      $code[] = '    $stmt = MODEL("' . $mdlName . '")->prepareStatement();';
      $code[] = '    $stmt->setQuery("DELETE FROM ' . convert_to_tablename($mdlName) . '")->execute();';
      $code[] = "  }";
      $code[] = "}";
      
      $path = $this->fixturesDir . DS . $mdlName . ".php";
      file_put_contents($path, implode(PHP_EOL, $code));
    }
  }
  
  protected function createLine($row)
  {
    $line = array();
    foreach ($row as $col => $val) {
      if (is_string($val)) {
        $val = "'{$val}'";
      } elseif (is_bool($val)) {
        $val = ($val) ? "true" : "false";
      } elseif (is_null($val)) {
        $val = "null";
      }
      
      $line[] = "'{$col}' => $val";
    }
    
    return "array(" . implode(", ", $line) . ")";
  }
}
