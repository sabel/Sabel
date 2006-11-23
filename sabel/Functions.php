<?php

function is_not_null($value)
{
  return (!is_null($value));
}

function is_not_object($object)
{
  return (!is_object($object));
}

function uri($params, $withDomain = true)
{
  $aCreator = Sabel_View_Uri::create();
  return $aCreator->uri($params, $withDomain);
}

function hyperlink($params, $anchor = null, $id = null, $class = null)
{
  $aCreator = Sabel_View_Uri::create();
  return $aCreator->hyperlink($params, $anchor, $id, $class);
}

function a($param, $anchor)
{
  $aCreator = Sabel_View_Uri::create();
  return $aCreator->aTag($param, $anchor);
}

function request($uri)
{
  $container = Container::create();
  $front     = $container->load('sabel.controller.Front');
  $response  = $front->ignition($uri);
  return $response['html'];
}

function dump($mixed)
{
  echo '<pre>';
  var_dump($mixed);
  echo '</pre>';
}

function array_ndpop(&$array) {
  $tmp = array_pop($array);
  $array[] = $tmp;
  return $tmp;
}