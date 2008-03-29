<?php

function a($uri, $anchor, $uriQuery = "")
{
  if ($uriQuery === "") {
    return sprintf('<a href="%s">%s</a>', uri($uri), $anchor);
  } else {
    return sprintf('<a href="%s?%s">%s</a>', uri($uri), $uriQuery, $anchor);
  }
}

function ah($param, $anchor, $uriQuery = "")
{
  return a($param, h($anchor), $uriQuery);
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
  $path = "/css/{$file}.css";
  
  if (defined("URI_IGNORE")) {
    $path = dirname($_SERVER["SCRIPT_NAME"]) . $path;
  }
  
  return '<link rel="stylesheet" href="' . $path . '" type="text/css" />';
}

function h($string, $charset = null)
{
  return htmlescape($string, $charset);
}

function mb_trim($string)
{
  return preg_replace('/^[\s　]*(.*?)[\s　]*$/u', '$1', $string);
}

function to_date($date, $format)
{
  return Helpers_Date::format($date, constant("Helpers_Date::" . $format));
}

function __include($uri, $values = array(), $method = Sabel_Request::GET, $withLayout = false)
{
  $requester = new Sabel_Request_Internal($method);
  $requester->values($values)->withLayout($withLayout);
  return $requester->request($uri)->getResult();
}
