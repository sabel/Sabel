<?php

class Sabel_Template_Director_Factory
{
  public static function create($entry)
  {
    $destination = $entry->getDestination();
    
    $classPath  = Sabel_Core_Const::MODULES_DIR . $destination->module;
    $classPath .= '/extensions/CustomTemplateDirector.php';
    
    $commonsPath  = Sabel_Core_Const::COMMONS_DIR;
    $commonsPath .= '/extensions/CustomTemplateDirector.php';
    
    if (is_file($classPath)) {
      require_once($classPath);
      return new CustomTemplateDirector($destination);
    } else if (is_file($commonsPath)) {
      require_once($commonsPath);
      return new CustomTemplateDirector($destination);
    } else {
      return new Sabel_Template_Director_Default($destination);
    }
  }
}