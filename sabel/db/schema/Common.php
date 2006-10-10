<?php

abstract class Sabel_DB_Schema_Common
{
  protected $connectName = '';
  protected $recordObj   = null;

  public function getTable($tblName)
  {
    $schemaClass = 'Schema_' . join('', array_map('ucfirst', explode('_', $tblName)));
    if (is_null($schema = Sabel_DB_SimpleCache::get($schemaClass))) {
      if (class_exists($schemaClass, false)) {
        $sc   = new $schemaClass();
        $cols = array();
        foreach ($sc->get() as $colName => $params) {
          $co = new Sabel_DB_Schema_Column();
          $co->name = $colName;
          $cols[$colName] = $co->make($params);
        }
      } else {
        $cols = $this->createColumns($tblName);
      }
      $schema = new Sabel_DB_Schema_Table($tblName, $cols);
      Sabel_DB_SimpleCache::add($schemaClass, $schema);
    }
    return $schema;
  }

  protected abstract function createColumns($tblName);
}
