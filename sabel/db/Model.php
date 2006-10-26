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
    $models = array();
    foreach ($mdlNames as $name) $models[] = self::getClass($name);
    return new Sabel_DB_Fusion($models, $mdlNames);
  }

  private static function getClass($mdlName)
  {
    if (class_exists($mdlName, false)) return new $mdlName();

    if (!class_exists('Sabel_DB_Empty', false)) {
      eval('class Sabel_DB_Empty extends Sabel_DB_Relation{}');
    }
    $model   = new Sabel_DB_Empty();
    $tblName = convert_to_tablename($mdlName);
    $model->setTableName($tblName);
    $model->setSchema($tblName);
    return $model;
  }
}
