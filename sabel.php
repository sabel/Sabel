<?php

define('ABSOLUTE_PATH', dirname(realpath(__FILE__)));

class DirectoryCreator
{
  public function create($name)
  {
    
    if (is_dir($name)) {
      echo "$name already exists.\n";
    } else {
      echo "create $name\n";
      mkdir($name);
    }
  }
}

$directries = array('app',
                    'app/index', 'app/index/controllers', 'app/index/models', 'app/index/views',
                    'config', 'data', 'lib', 'logs',
                    'public', 'public/images', 'public/js', 'public/css',
                    'scripts', 'skeleton', 'test');

class FileCreator
{
  protected $targetFiles = array();
  protected $sourceFiles = array();
  
  public function __construct()
  {
    $this->targetFiles = array('public/index.php',
                               'public/.htaccess',
                               'lib/setup.php',
                               'config/map.yml',
                               'config/database.yml',
                               'app/index/controllers/index.php',
                               'sabel.php');
                               
    $this->sourceFiles = array('/skeleton/index.php',
                               '/skeleton/htaccess',
                               '/skeleton/setup.php',
                               '/skeleton/map.yml',
                               '/skeleton/database.yml',
                               '/skeleton/controller_index.php',
                               '/skeleton/sabel.php');
  }
  
  public function create()
  {
    $fp = fopen('logs/sabel.log', 'w');
    chown('logs/sabel.log', 'www');
    chmod('logs/sabel.log', 0777);
    fclose($fp);
    
    $targetFiles = $this->targetFiles;
    $sourceFiles = $this->sourceFiles;
    foreach ($targetFiles as $pos => $file) {
      if (is_file($file)) {
        echo "${file} already exists.\n";
      } else {
        echo "create ${file}\n";
        fwrite(fopen($file, 'w'), file_get_contents(ABSOLUTE_PATH . $sourceFiles[$pos]));
      }
    }
  }
}

$d = new DirectoryCreator();
foreach ($directries as $directory) {
  $d->create($directory);
}

$fc = new FileCreator();
$fc->create();

start.
read skeleton directory structure.
read system information.
fill skeleton.
write directory structure.
write filled sekeleton files.
end.