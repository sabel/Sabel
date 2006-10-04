#!/opt/local/php/bin/php
<?php

require_once('setup.php');

$commands = array();

while (!feof(STDIN)) {
  echo 'sabel> ';
  $in = trim(fgets(STDIN));
  if ($in === 'exit') break;
  if ($in === 'ver') {
    echo "0.1\n";
    continue;
  }
  
  if (isset($in[0]) && $in[0] === '!') {
    $inLength = count($in) - 1;
    foreach ($commands as $command) {
      $in = $command;
    }
  } else {
    $commands[] = $in;
  }
  
  try {
    eval($in);
  } catch (Exception $e) {
    print $e->getMessage() . "\n";
  }
  
}