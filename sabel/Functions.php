<?php

function is_not_null($value)
{
  return (!is_null($value));
}

function is_not_object($object)
{
  return (!is_object($object));
}

function urlFor()
{
  $map = Sabel_Map_Facade::create();
  $entry   = $map->getEntry(func_get_arg(0));
  $request = $entry->getRequest();
  
  $model = null;
  if (2 < func_num_args()) $model = func_get_arg(2);
  
  $buf = '';
  foreach ($entry->getUri() as $element) {
    $name = $element->getName();
    switch ($name) {
      case 'module':
        $buf .= '/' . $request->module;
        break;
      case 'controller':
        $buf .= '/' . $request->controller;
        break;
      case 'action':
        $buf .= '/' . func_get_arg(1);
        break;
      case 'id':
        $buf .= (is_object($model)) ? '/'.$model->$name->value : '';
        break;
    }
  }
  
  return $buf;
}