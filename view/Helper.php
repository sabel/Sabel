<?php

define('LNAME', 'name');
define('MODULE', 'module');
define('CONTROLLER', 'controller');
define('METHOD', 'method');
define('ABSOLUTE', 'absolute');
define('IMG', 'img');
define('PARAM', 'param');

class UTIL
{
  public static $module;
  public static $controller;
  public static $method;
}

function linkTo($ar)
{
  $absolute = 'http://' . $_SERVER['HTTP_HOST'];
  $buf = array();
  array_push($buf, '<a href="');

  if (isset($ar[ABSOLUTE])) {
    array_push($buf, $absolute);
  }

  if (isset($ar[MODULE])) {
    array_push($buf, '/'.$ar[MODULE]);
    array_push($buf, '/'.$ar[CONTROLLER]);
    array_push($buf, '/'.$ar[METHOD]);
  } elseif (isset($ar[CONTROLLER])) {
    array_push($buf, '/'.UTIL::$module);
    array_push($buf, '/'.$ar[CONTROLLER]);
    array_push($buf, '/'.$ar[METHOD]);
  } elseif (isset($ar[METHOD])) {
    array_push($buf, '/'.UTIL::$module);
    array_push($buf, '/'.UTIL::$controller);
    array_push($buf, '/'.$ar[METHOD]);
  }

  if (isset($ar[PARAM])) {
    array_push($buf, '/'.$ar[PARAM]);
  }

  array_push($buf, '">');

  if (isset($ar[LNAME])) {
    array_push($buf, $ar[LNAME]);
    if (isset($ar[IMG])) {
      array_push($buf, '<img src="'.$absolute .'/'. $ar[IMG].'">');
    }
  } else {
    if (isset($ar[IMG])) {
      array_push($buf, '<img src="'.$absolute .'/'. $ar[IMG].'">');
    } else {
      array_push($buf, 'link');
    }
  }

  array_push($buf, '</a>');

  echo join('', $buf);
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

?>