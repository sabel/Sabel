<?php

class UTIL
{
  public static $module;
  public static $controller;
  public static $method;
}

function js_include($file)
{
  $path = 'http://'.$_SERVER['HTTP_HOST'] . '/js/' . $file;
  echo '<script type="text/javascript" src="'.$path.'"></script>'."\n";
}


/**
 * below utility classes.
 */

function p($str)
{
  echo $str;
}

function ep($str)
{
  echo htmlspeicalchars($str);
}