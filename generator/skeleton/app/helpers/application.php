<?php

function a($uri, $anchor, $queryString = "")
{
  if ($queryString === "") {
    return sprintf('<a href="%s">%s</a>', uri($uri), $anchor);
  } else {
    return sprintf('<a href="%s?%s">%s</a>', uri($uri), $queryString, $anchor);
  }
}

function ah($param, $anchor, $queryString = "")
{
  return a($param, h($anchor), $queryString);
}

function linkto($file)
{
  if (defined("URI_IGNORE")) {
    return dirname($_SERVER["SCRIPT_NAME"]) . "/" . $file;
  } else {
    return "/" . $file;
  }
}

function css($file)
{
  $ignored = "";
  if (defined("URI_IGNORE")) {
    $ignored = dirname($_SERVER["SCRIPT_NAME"]);
    $fmt = '  <link rel="stylesheet" href="%s" type="text/css" />';
    return sprintf($fmt, $ignored . "/css/" . $file . ".css");;
  } else {
    $fmt = '  <link rel="stylesheet" href="%s" type="text/css" />';
    return sprintf($fmt, "/css/{$file}.css");
  }
}

function h($string, $charset = null)
{
  return htmlescape($string, $charset);
}

function mb_trim($string)
{
  return preg_replace('/^[\s　]*(.*?)[\s　]*$/u', '$1', $string);
}

function form_start($uri, $class = null, $id = null, $name = null)
{
  $html = '<form action="' . uri($uri) . '" method="post" ';
  
  if ($id    !== null) $html .= 'id="'    . $id    . '" ';
  if ($class !== null) $html .= 'class="' . $class . '" ';
  if ($name  !== null) $html .= 'name="'  . $name  . '" ';
  
  return $html . '><fieldset class="formField">' . PHP_EOL;
}

function form_end()
{
  return "</fieldset></form>";
}

function to_date($date, $format)
{
  return Helpers_Date::format($date, constant("Helpers_Date::" . $format));
}
