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
    
    $method = $this->getFixtureMethod();
    $this->defineEnvironment($this->arguments[0]);
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
          $instance->$method();
        }
      } else {
        $filePath = $this->fixturesDir . DS . $fixtureName . ".php";
        if (Sabel::fileUsing($filePath, true)) {
          $className = "Fixture_" . $fixtureName;
          $instance  = new $className();
          $instance->$method();
          $this->success(ucfirst($method) . " " . $fixtureName);
        } else {
          $this->error("no such fixture file. '{$filePath}'");
        }
      }
    }
  }
  
  protected function getFixtureMethod()
  {
    $method = "upFixture";
    $arguments = $this->arguments;
    
    if (Sabel_Console::hasOption("up", $arguments)) {
      $index = array_search("--up", $arguments, true);
      unset($arguments[$index]);
      $arguments = array_values($arguments);
    }
    
    if (Sabel_Console::hasOption("down", $arguments)) {
      $index = array_search("--down", $arguments, true);
      unset($arguments[$index]);
      $arguments = array_values($arguments);
      $method = "downFixture";
    }
    
    $this->arguments = $arguments;
    return $method;
  }
  
  public function usage()
  {
    echo "Usage: sakle Fixture [OPTION] ENVIRONMENT FIXTURE_NAME " . PHP_EOL;
    echo PHP_EOL;
    echo "  ENVIRONMENT:  production | test | development" . PHP_EOL;
    echo "  FIXTURE_NAME: fixture name or 'all'" . PHP_EOL;
    echo PHP_EOL;
    echo "  OPTION:" . PHP_EOL;
    echo "    --up      up fixture(default)" . PHP_EOL;
    echo "    --down    down fixture" . PHP_EOL;
    echo PHP_EOL;
    echo "Example: sakle Fixture development User" . PHP_EOL;
    echo PHP_EOL;
  }
  
  protected function export()
  {
    dump($this->arguments);
    exit;
    unset($this->arguments[0]);
    
    $fp = fopen(RUN_BASE . "/test.csv", "w+");
    $models = MODEL("Foo")->select();
    foreach ($models as $model) {
      fputcsv($fp, $model->toArray());
    }
    
    fclose($fp);
    exit;
    
    foreach ($this->arguments as $mdlName) {
      $lines  = array();
      
      $code = array("<?php" . PHP_EOL);
      $code[] = "class Fixture_{$mdlName} extends Sabel_Test_Fixture";
      $code[] = "{";
      $code[] = "  public function upFixture()";
      $code[] = "  {";
      
      $models = MODEL($mdlName)->select();
      foreach ($models as $model) {
        $code[] = '    $this->insert(' . $this->createLine($model->toArray()) . ');';
      }
      
      $code[] = "  }" . PHP_EOL;
      $code[] = "  public function downFixture()";
      $code[] = "  {";
      $code[] = '    $this->deleteAll();';
      $code[] = "  }";
      $code[] = "}";
      
      $path = $this->fixturesDir . DS . $mdlName . ".php";
      file_put_contents($path, implode(PHP_EOL, $code));
      
      $this->success("export $mdlName Records to '" . substr($path, strlen(RUN_BASE) + 1) . "'");
    }
  }
  
  protected function createLine($row)
  {
    $line = array();
    foreach ($row as $col => $val) {
      if (is_string($val)) {
        $val = '"' . str_replace('"', '\\"', $val) . '"';
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
