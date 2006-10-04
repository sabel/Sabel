<?php

function is_not_null($value)
{
  return (!is_null($value));
}

function is_not_object($object)
{
  return (!is_object($object));
}

function uri($params)
{
  $entry = null;
  
  $map = Sabel_Map_Facade::create();
  if (isset($params['entry'])) {
    $entry = $map->getEntry($params['entry']);
    unset($params['entry']);
    // @todo if $entry is not object.
  } else {
    $entry = $map->getCurrentEntry();
  }
  
  return 'http://' . $_SERVER['HTTP_HOST'] . '/' . $entry->uri($params);
}

function hyperlink($params, $anchor = null, $id = null, $class = null)
{
  $entry = null;
  
  $map = Sabel_Map_Facade::create();
  if (isset($params['entry'])) {
    $entry = $map->getEntry($params['entry']);
    unset($params['entry']);
    // @todo if $entry is not object.
  } else {
    $entry = $map->getCurrentEntry();
  }
  $uriPrefix = "http://{$_SERVER['HTTP_HOST']}";
  
  if (is_object($anchor)) {
    $anchor = $anchor->__toString();
  }
  
  $fmtUri = '<a id="%s" class="%s" href="%s/%s">%s</a>';
  return sprintf($fmtUri, $id, $class, $uriPrefix, $entry->uri($params), $anchor);
}

function a($param, $anchor)
{
  $buf = array();
  foreach (explode(',', $param) as $key => $part) {
    $line = array_map('trim', explode(':', $part));
    if ($line[0] === 'e') {
      $buf['entry'] = $line[1];
    } else {
      $buf[$line[0]] = $line[1];
    }
  }
  return hyperlink($buf, $anchor);
}