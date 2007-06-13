<?php

class Sabel_Plugin_MMValidator extends Sabel_Plugin_Base
{
  
  private $values = array();
  
  public function onBeforeAction()
  {
    $request = $this->controller->getRequest();
    $values  = $request->fetchPostValues();
        
    if (count($values) !== 0) {
      $models = $this->modelize($values);
      
      foreach ($models as $name => $model) {
        $name .= "Values";
        $this->controller->$name = $model;
        $this->values[$name] = $model;
      }
    }
    
    return true;
  }
  
  public function modelize($values)
  {
    $models = array();
    
    if (count($values) !== 0) {
      foreach ($values as $name => $value) {
        if (strpos($name, "::") !== false) {
          list($modelName, $property) = explode("::", $name);
          $models[$modelName][$property] = $value;
        }
      }
    }
    
    return $models;
  }
  
  public function assignErrors($targets, $values)
  {
    $models = $this->modelize($values);
        
    foreach ($targets as $target) {
      $modelName = strtolower($target->getModelName());
      $target->setValues($models[$modelName]);
    }
  }
  
  public function multipleValidate($models, $save = false)
  {
    $errors = array();
    
    foreach ($models as $model) {
      $errors = array_merge($model->validate(), $errors);
    }
    
    if ($errors) {
      $this->controller->errors = $errors;
    } elseif ($save) {
      foreach ($models as $model) {
        $model->save();
      }
    }
    
    if (!$errors) $this->controller->resetErrors();
    
    return $errors;
  }
}
