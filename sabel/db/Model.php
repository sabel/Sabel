<?php

class Sabel_DB_Model
{
  private $models = array();

  public static function load($mdlNames)
  {
    if (!is_array($mdlNames)) {
      return self::getClass($mdlNames);
    } else {
      return self::createUnitedModel($mdlNames);
    }
  }

  private static function createUnitedModel($mdlNames)
  {
    $models = array();
    foreach ($mdlNames as $name) {
      $models[] = self::getClass($name);
    }
    return new Sabel_DB_UnitedModel($models, $mdlNames);
  }

  private static function getClass($mdlName)
  {
    if (class_exists($mdlName)) return new $mdlName();

    if (!class_exists('Sabel_DB_Empty', false)) {
      eval('class Sabel_DB_Empty extends Sabel_DB_Wrapper{}');
    }
    $model = new Sabel_DB_Empty();
    $model->setTableName(convert_to_tablename($mdlName));
    return $model;
  }
}
