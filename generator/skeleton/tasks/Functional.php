<?php

if(!defined("RUN_BASE")) define("RUN_BASE", getcwd());

Sabel::fileUsing("tasks/environment.php");
Sabel::fileUsing("config/database.php");

//Sabel::using('Sabel_Sakle_Task');
//Sabel::using('Sabel_DB_Connection');
//Sabel::using('Sabel_DB_Model');

//Sabel::using("Sabel_Test_Functional");
//Sabel::using("Sabel_Test_FunctionalRunner");

/**
 * Functional
 *
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Functional extends Sabel_Sakle_Task
{
  public function run($arguments)
  {
    if (isset($arguments[2])) {
      define ("ENVIRONMENT", environment($arguments[2]));
    } else {
      define ("ENVIRONMENT", TEST);
    }
    
    if (!isset($arguments[1])) {
      throw new Exception("model name must be specified");
    }
    
    Sabel_Test_FunctionalRunner::create()->start($arguments[1]);
  }
}