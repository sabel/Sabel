<?php

if(!defined("RUN_BASE")) define("RUN_BASE", getcwd());

Sabel::fileUsing("tasks/environment.php");
Sabel::fileUsing("config/database.php");

Sabel::using('Sabel_DB_Connection');
Sabel::using('Sabel_DB_Executer');
Sabel::using('Sabel_DB_Model');

Sabel::using("Sabel_Test_Model");
Sabel::using("Sabel_Test_ModelRunner");

/**
 * Migration
 *
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class UnitTest extends Sakle
{
  public function run($arguments)
  {
    if (!isset($arguments[1])) {
      throw new Exception("model name must be specified");
    }
    
    Sabel_Test_ModelRunner::create()->start($arguments[1]);
  }
}