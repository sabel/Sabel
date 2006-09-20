<?php

class Sabel_DB_Schema_General
{
  protected $connectName = '';
  protected $recordObj   = null;

  public function getTable($tName)
  {
    $schemaClass = $this->connectName . '_' . $tName;
    if (is_null($schema = Sabel_DB_SimpleCache::get($schemaClass))) {
      if (class_exists($schemaClass, false)) {
        $sc   = new $schemaClass();
        $cols = array();
        foreach ($sc->get() as $cName => $params) {
          $co = new Sabel_DB_Schema_Column();
          $cols[$cName] = $co->make($params);
        }
      } else {
        $cols = $this->createColumns($tName);
      }
      $schema = new Sabel_DB_Schema_Table($tName, $cols);
      Sabel_DB_SimpleCache::add($schemaClass, $schema);
    }
    return $schema;
  }
}
