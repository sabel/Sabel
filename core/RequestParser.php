<?php

require_once('ParsedRequest.php');

class RequestParser
{
  public function parse()
  {
    global $sabelfilepath;

    $uri = $_SERVER['REQUEST_URI'];

    $path = split('/', $sabelfilepath);
    array_shift($path);
    foreach ($path as $p => $v) {
      if ($v == $path[count($path) - 2]) {
        $dir = $v;
      }
    }

    $sp = split('/', $uri);
    array_shift($sp);

    $request = array();
    $matched = true;
    foreach ($sp as $p => $v) {
      if ($matched)   $request[] = $v;
      // if ($v == $dir) $matched = true;
    }

    return new ParsedRequest($request);
  }
}

?>
