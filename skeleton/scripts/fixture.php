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
  }
  
  public function command()
  {
    
  }
}

$fixture = new Fixture();
$fixture->command();