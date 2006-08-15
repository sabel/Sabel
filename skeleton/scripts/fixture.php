<?php

class Fixture
{
  public function __construct()
  {
    $absolute_path = dirname(realpath(__FILE__));
    define('RUN_BASE', $absolute_path);

    $paths = explode(':', ini_get('include_path'));
    array_shift($paths);

    foreach ($paths as $path) {
      if (is_dir($path . '/Sabel')) {
        define('SABEL_USE_INCLUDE_PATH', true);
      }
    }

    require_once('lib/setup.php');

    $conf = new Sabel_Config_Yaml('config/database.yml');
    $dev = $conf->read('development');
    $fm = '%s:host=%s;dbname=%s';
    $con['dsn'] = sprintf($fm, $dev['driver'], $dev['host'], $dev['database']);
    $con['user'] = $dev['user'];
    $con['pass'] = $dev['password'];

    Sabel_Edo_DBConnection::addConnection('default', 'pdo', $con);
    uses('sabel.edo.RecordObject');
  }
  
  public function command()
  {
    $target = $_SERVER['argv'][1];
    echo "\n";
    require_once("fixtures/{$target}.php");
    $fixtureClass = 'Fixture_'.$target;
    $fixture = new $fixtureClass();
    
    try {
      $fixture->drop();
      echo "DROP TABLE $target.\n";
    } catch (Exception $e) {
      echo "drop failed because $target table does not exists.\n";
    }
    
    try {
      $fixture->create();
      echo "CREATE TABLE $target.\n";
    } catch (Exception $e) {
      echo "cant't create table $target.\n";
    }
    
    
  }
}

$fixture = new Fixture();
$fixture->command();