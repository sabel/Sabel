<?php

if(!defined("RUN_BASE")) define("RUN_BASE", getcwd());

Sabel::fileUsing("tasks/environment.php");
Sabel::fileUsing("config/database.php");

//Sabel::using('Sabel_Sakle_Task');
//Sabel::using('Sabel_DB_Migration');
//Sabel::using('Sabel_DB_Connection');
//Sabel::using('Sabel_DB_Model');

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
      if (isset($arguments[2])) {
        define ("ENVIRONMENT", environment($arguments[2]));
      } else {
        define ("ENVIRONMENT", TEST);
      }
    }
    
    $fixtureName = "Fixtures_" . $arguments[1];
    //Sabel::using($fixtureName);
    
    try {
      $this->printMessage("up fixture");
      if (class_exists($fixtureName)) eval("{$fixtureName}::upFixture();");
    } catch (Exception $e) {
      $this->printMessage($e->getMessage());
    }
  }
}