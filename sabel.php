<?php

define('SKELETON_DIR', dirname(realpath(__FILE__)) . '/skeleton');

class DirectoryTraverser
{
  protected $directories = null;
  
  public function __construct()
  {
    $this->directories = new DirectoryIterator(SKELETON_DIR);
  }

  public function traverse($fromElement = null)
  {
    $element = (is_null($fromElement)) ? $this->directories : $fromElement;
    
    foreach ($element as $e) {
      if (!$e->isDot() && $e->isDir()) {
        $child = $e->getPath() .'/'. $e->getFileName();
        $dir = ltrim(str_replace(SKELETON_DIR, '', $child), '/');
        if (is_dir($dir)) {
          echo "${dir} already exists.\n";
        } else {
          echo "create ${dir}\n";
          mkdir($dir);
        }
        $this->traverse(new DirectoryIterator($child));
      } else if (!$e->isDot()) {
        $child = $e->getPath() .'/'. $e->getFileName();
        $file = ltrim(str_replace(SKELETON_DIR, '', $child), '/');
        if (is_file($file)) {
          echo "${file} already exists.\n";
        } else {
          echo "create ${file}\n";
          fwrite(fopen($file, 'w'), file_get_contents($child));
          if ($file == 'logs/sabel.log') {
            @chown('logs/sabel.log', 'www');
            chmod('logs/sabel.log', 0777);
          }
        }
      }
    }
  }
}

$dt = new DirectoryTraverser();
$dt->traverse();