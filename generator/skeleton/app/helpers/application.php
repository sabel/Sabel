<?php

function a($uri, $anchor, $param = null)
{
  if ($param === null) {
    return '<a href="'.uri($uri).'">'.$anchor.'</a>';
  } else {
    return '<a href="'.uri($uri).$param.'">'.$anchor.'</a>';
  }
  
}

function ah($param, $anchor)
{
  return a($param, htmlspecialchars($anchor));
}

function uri($param)
{
  $aCreator = new Sabel_View_Uri();
  $ignored = "";
  if (defined("URI_IGNORE")) {
    $ignored = $_SERVER["SCRIPT_NAME"];
  }
  return $ignored . $aCreator->uri($param);
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