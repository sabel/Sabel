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

Sabel::using('Sabel_DB_Migration');
Sabel::using('Sabel_DB_Connection');
Sabel::using('Sabel_DB_Executer');
Sabel::using('Sabel_DB_Model');

/**
 * Fixture
 *
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Fixture extends Sakle
{
  public function execute()
  {
    if (isset($this->arguments[2])) {
      define ("ENVIRONMENT", environment($this->arguments[2]));
    } else {
      define ("ENVIRONMENT", TEST);
    }
    
    $fixtureName = "Fixtures_" . $this->arguments[1];
    Sabel::using($fixtureName);
    
    try {
      if (class_exists($fixtureName)) eval("{$fixtureName}::upFixture();");
    } catch (Exception $e) {
      echo "fixture throws exception: " . $e->getMessage() . "\n";
    }
  }
}