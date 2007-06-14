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
