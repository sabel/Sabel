<?php

if(!defined("RUN_BASE")) define("RUN_BASE", getcwd());

Sabel::fileUsing("tasks/environment.php");

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
    
    Sabel::fileUsing("config/connection.php");
    
    if (!isset($arguments[1])) {
      throw new Exception("model name must be specified");
    }
    
    Sabel_Test_FunctionalRunner::create()->start($arguments[1]);
  }
}
