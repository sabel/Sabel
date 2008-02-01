<?php

function a($uri, $anchor, $param = null)
{
  if ($param === null) {
    return '<a href="' . uri($uri) . '">' . $anchor . '</a>';
  } else {
    return '<a href="' . uri($uri) . $param . '">' . $anchor . '</a>';
  }
}

function ah($param, $anchor)
{
  return a($param, htmlspecialchars($anchor));
}

function uri($uriParameter, $absolute = false, $secure = false)
{
  $ignored   = "";
  $uriPrefix = "";

  if ($absolute) {
    $protocol  = ($secure) ? "https" : "http";
    $uriPrefix = $protocol . "://" . Sabel_Environment::get("SERVER_NAME");
  }
  
  if (defined("URI_IGNORE")) {
    $ignored = $_SERVER["SCRIPT_NAME"];
  }
  
  $uri = Sabel_Context::getContext()->getCandidate()->uri($uriParameter);
  return $ignored . $uriPrefix . "/" . $uri;
}

function linkto($file)
{
  $ignored = "";
  if (defined("URI_IGNORE")) {
    $ignored = dirname($_SERVER["SCRIPT_NAME"]);
  }
  return $ignored . "/" . $file;
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

function h($content)
{
  return htmlspecialchars($content);
}

function mb_trim($string)
{
  return preg_replace('/^[\s　]*(.*?)[\s　]*$/u', '$1', $string);
}

function to_date($date, $format)
{
  return Helpers_Date::format($date, constant("Helpers_Date::" . $format));
}
