<?php

if(!defined("RUN_BASE")) define("RUN_BASE", getcwd());

Sabel::fileUsing("tasks/environment.php");

/**
 * Fixture
 *
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Fixture extends Sabel_Sakle_Task
{
  public function run($arguments)
  {
    if (!defined("ENVIRONMENT")) {
      if (isset($arguments[1])) {
        define ("ENVIRONMENT", constant(strtoupper($arguments[1])));
      } else {
        define ("ENVIRONMENT", TEST);
      }
    }
    
    Sabel::fileUsing(RUN_BASE . "/config/connection.php");
    
    if (isset($arguments[2]) && $arguments[2] === "run") {
      $fixtureName = "Fixtures_" . $arguments[3];
      
      try {
        $this->printMessage("up fixture");
        if (class_exists($fixtureName)) eval("{$fixtureName}::upFixture();");
      } catch (Exception $e) {
        $this->printMessage($e->getMessage());
      }
    } elseif (isset($arguments[2]) && $arguments[2] === "import") {
      $this->printMessage("import");
      
      if (isset($arguments[3]) && $arguments[3] === "all") {
        $this->printMessage("create all fixtures");
        $name = array_keys(get_db_params());
        $sa = new Sabel_DB_Schema_Accessor($name[0]);
        
        foreach ($sa->getTableList() as $table) {
          if ($table == "sversion") continue;
          $this->createModelFixture(convert_to_modelname($table));
        }
        
      } else {
        $modelName = $arguments[3];
        $this->createModelFixture($modelName);
      }
    }
  }
  
  /**
   * create fixture class from template with schema
   *
   * @param string $modelName
   */
  protected function createModelFixture($modelName)
  {
    $model = MODEL($modelName);
    $instancies = $model->select();
    
    $lines = array();
    
    if ($instancies) {
      foreach ($instancies as $instance) {
        $lines[] = '$model = ' . 'new ' . $modelName . "();\n";
        foreach ($instance->getColumnNames() as $column) {
          $line = '$model->' . $column . " = ";
          
          if ($column == "") {
            $line .= 0;
          } elseif (is_numeric($instance->$column)) {
            $line .= $instance->$column . ";";
          } else {
            $line .= "'" . addslashes($instance->$column) . "';";
          }
          
          $lines[] = $line . "\n";
        }
        $lines[] = 'if(!$model->save()) {' . "\n";
        $lines[] = '  dump($model->getErrors());' . "\n";
        $lines[] = "}\n";
        
        $tblName = convert_to_tablename($modelName);
        ob_start();
        include RUN_BASE . "/tests/fixtures/Template.tphp";
        $fixtureFile = ob_get_clean();
        $fixtureFile = str_replace("#?php", "?php", $fixtureFile);
        $path = RUN_BASE . "/tests/fixtures/".$modelName.".php";
        $result = file_put_contents($path, $fixtureFile);
        if ($result) {
          $this->printMessage("create " . $modelName);
        } else {
          $this->printMessage("fail " . $modelName);
        }
      }
    } else {
      $this->printMessage("no instance found in " . $modelName);
    }
  }

}
