<?php

function is_not_null($value)
{
  return (!is_null($value));
}

function is_not_object($object)
{
  return (!is_object($object));
}

function urlFor($params)
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