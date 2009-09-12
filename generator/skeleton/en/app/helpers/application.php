<?php

function h($string, $charset = DEFAULT_ENCODING)
{
  return htmlescape($string, $charset);
}

function xh($string, $charset = DEFAULT_ENCODING)
{
  return xmlescape($string, $charset);
}

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

/**
 * create uri for css, image, js, etc...
 */
function linkto($file)
{
  if ($bus = Sabel_Context::getContext()->getBus()) {
    if ($bus->get("NO_VIRTUAL_HOST")) {
      return dirname($_SERVER["SCRIPT_NAME"]) . "/" . $file;
    }
  }
  
  return "/" . $file;
}

function get_uri_prefix($secure = false, $absolute = false)
{
  $prefix = "";
  
  if ($secure || $absolute) {
    $server = (isset($_SERVER["SERVER_NAME"])) ? $_SERVER["SERVER_NAME"] : "localhost";
    $prefix = (($secure) ? "https" : "http") . "://" . $server;
  }
  
  if ($bus = Sabel_Context::getContext()->getBus()) {
    if ($bus->get("NO_VIRTUAL_HOST") && isset($_SERVER["SCRIPT_NAME"])) {
      $prefix .= $_SERVER["SCRIPT_NAME"];
    }
    
    if ($bus->get("NO_REWRITE_MODULE") && defined("NO_REWRITE_PREFIX")) {
      $prefix .= "?" . NO_REWRITE_PREFIX . "=";
    }
  }
  
  return $prefix;
}

/**
 * create uri
 */
function uri($param, $secure = false, $absolute = false)
{
  $context = Sabel_Context::getContext();
  $prefix  = get_uri_prefix($secure, $absolute);
  
  return $prefix . "/" . $context->getCandidate()->uri($param);
}

/**
 * internal request.
 */
function __include($uri, $values = array(), $method = Sabel_Request::GET, $withLayout = false)
{
  $requester = new Sabel_Request_Internal($method);
  $requester->values($values)->withLayout($withLayout);
  return $requester->request($uri)->getResult();
}

function mb_trim($string)
{
  $string = new Sabel_Util_String($string);
  return $string->trim()->toString();
}

function to_date($date, $format)
{
  return Helpers_Date::format($date, constant("Helpers_Date::" . $format));
}
