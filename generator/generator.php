<?php

require_once('Sabel/Container.php');

class SabelDirectoryAndFileCreator
{
  public function accept($element, $type, $child = null)
  {
    if ($type === 'dir') {
      if (is_dir($element)) {
        echo "${element} already exists.\n";
      } else {
        echo "create ${element}\n";
        mkdir($element);
      }
    } else if ($type === 'file') {
      if (is_file($element)) {
        echo "${element} already exists.\n";
      } else {
        echo "create ${element}\n";
        fwrite(fopen($element, 'w'), file_get_contents($child));
        if ($element == 'logs/sabel.log') {
          @chown('logs/sabel.log', 'www');
          chmod('logs/sabel.log', 0777);
        }
      }
    }
  }
}

$dt = new DirectoryTraverser(dirname(__FILE__) . '/skeleton');
$dt->visit(new SabelDirectoryAndFileCreator());
$dt->traverse();