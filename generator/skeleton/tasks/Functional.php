<?php

define("RUN_BASE", getcwd());
add_include_path("/tests");

if (!defined('PRODUCTION'))  define('PRODUCTION',  0x01);
if (!defined('TEST'))        define('TEST',        0x05);
if (!defined('DEVELOPMENT')) define('DEVELOPMENT', 0x0A);

add_include_path('/app');
add_include_path('/app/models');
add_include_path('/lib');

define("__TRUE__",  "true");
define("__FALSE__", "false");

Sabel::fileUsing("config/database.php");

Sabel::using('Sabel_Sakle_Task');

Sabel::using('Sabel_DB_Connection');
Sabel::using('Sabel_DB_Model');

Sabel::using("Sabel_Test_Functional");
Sabel::using("Sabel_Test_FunctionalRunner");

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