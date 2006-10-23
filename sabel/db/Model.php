<?php

class Sabel_DB_Model
{
  private $models = array();

  public static function load($mdlName)
  {
    return self::getClass($mdlName);
  }

  public static function fusion($mdlNames)
  {
    return self::createFusionModel($mdlNames);
  }

  private static function createFusionModel($mdlNames)
  {
    $models = array();
    foreach ($mdlNames as $name) {
      $models[] = self::getClass($name);
    }
    return new Sabel_DB_FusionModel($models, $mdlNames);
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
