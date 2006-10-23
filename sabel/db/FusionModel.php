<?php

class Sabel_DB_FusionModel
{
  private
    $models      = array(),
    $modelNames  = array(),
    $makedModels = array(),
    $modelsData  = array();

  private
    $baseTable  = '',
    $baseModel  = '',
    $unitedCols = array(),
    $modelCols  = array();

  private
    $unitCondition = array();

  private
    $updateModels  = array(),
    $modelsNewData = array();

  public function __construct($models, $mdlNames)
  {
    for ($i = 0; $i < sizeof($models); $i++) {
      $tblName = convert_to_tablename($mdlNames[$i]);
      $this->modelCols[$tblName] = $models[$i]->getColumnNames();
    }

    foreach ($models as $model) {
      $this->models[convert_to_modelname($model->table)] = $model;
    }

    $this->modelNames = $mdlNames;
    $this->baseModel  = $mdlNames[0];
    $this->baseTable  = convert_to_tablename($mdlNames[0]);
  }

  public function __set($key, $val)
  {
    foreach ($this->modelsData as $mdlName => $data) {
      if (array_key_exists($key, $data)) {
        $this->modelsData[$mdlName][$key] = $val;
        $this->updateModels[] = $mdlName;
      }
    }
  }

  public function __get($key)
  {
    foreach ($this->modelsData as $mdlName => $data) {
      if (isset($data[$key])) return $data[$key];
    }
  }

  public function __call($method, $parameters)
  {
    //
  }

  public function setFusionCondition($unitCondition)
  {
    if (!is_array($unitCondition)) $unitCondition = (array)$unitCondition;

    $modelCondition = array();
    foreach ($unitCondition as $condition) {
      list($p, $c)   = explode(':', $condition);
      list($pm, $pk) = explode('.', $p);
      list($cm, $ck) = explode('.', $c);

      $modelCondition[$pm][$cm] = array($pk, $ck);
    }
    $this->unitCondition = $modelCondition;
  }

  public function selectOne($column, $val)
  {
    $model = $this->models[$this->baseModel]->selectOne($column, $val);
    $this->makedModels[] = $model;
    $this->modelsData[$this->baseModel] = $model->getData();
    $this->getModel($this->baseModel, $model);

    $data = array();
    foreach ($this->makedModels as $model) {
      $mdlName = convert_to_modelname($model->table);
      $data[$mdlName] = $model->getData();
    }
    $this->addPrefixData();
    return $this;
  }

  public function getModel($mdlName, $model)
  {
    if (!array_key_exists($mdlName, $this->unitCondition)) return null;

    foreach ($this->unitCondition[$mdlName] as $cm => $keys) {
      list($pk, $ck) = $keys;
      $model   = $this->models[$cm]->selectOne($ck, $model->$pk);
      $this->makedModels[] = $model;
      $this->modelsData[$cm] = $model->getData();
      $this->getModel($cm, $model);
    }
  }

  private function addPrefixData()
  {
    $modelsData =& $this->modelsData;

    foreach ($modelsData as $mdlName => $values) {
      foreach ($values as $key => $val) $modelsData[$mdlName]["{$mdlName}_{$key}"] = $val;
    }
  }

  public function schema()
  {
    $schemas = array();
    foreach ($this->models as $model) {
      $columns = $model->schema();
      $mdlName = convert_to_modelname($model->table);
      $schemas[$mdlName] = $this->setValueToSchema($mdlName, $columns);
    }
    return $this->remakeSchema($schemas);
  }

  private function setValueToSchema($mdlName, $columns)
  {
    foreach ($this->modelsData[$mdlName] as $name => $data) {
      if (isset($columns[$name])) $columns[$name]->value = $data;
    }
    return $columns;
  }

  private function remakeSchema($schemas)
  {
    $baseModelName = convert_to_modelname($this->baseTable);

    $result = array();
    foreach ($schemas as $mdlName => $data) {
      foreach ($data as $key => $val) {
        if (!array_key_exists($key, $result)) $result[$key] = $val;
        $result["{$mdlName}_$key"] = $val;
      }
    }
    return $result;
  }

  public function save()
  {
    foreach ($this->updateModels as $model) {

    }
  }
}
