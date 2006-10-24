<?php

class Sabel_DB_FusionModel
{
  private
    $models       = array(),
    $modelNames   = array(),
    $makedModels  = array(),
    $modelsData   = array(),
    $fusionedData = array();

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
        $this->updateModels[] = $mdlName;
        $this->modelsNewData[$mdlName][$key] = $val;
      }
    }
  }

  public function __get($key)
  {
    foreach ($this->modelsData as $mdlName => $data) {
      if (isset($data[$key])) return $data[$key];
    }
  }

  public function toArray()
  {
    return $this->fusionedData;
  }

  public function setFusionCondition($unitCondition)
  {
    if (!is_array($unitCondition)) $unitCondition = (array)$unitCondition;

    $modelCondition = array();
    foreach ($unitCondition as $condition) {
      list($p, $c)   = explode(':', $condition);
      if (strpos('.', $p) === false) {
        $pm = $p;
        $cm = $c;
        $pk = 'id';
        $ck = convert_to_tablename($pm) . '_id';
      } else {
        list($pm, $pk) = explode('.', $p);
        list($cm, $ck) = explode('.', $c);
      }
      $modelCondition[$pm][$cm] = array($pk, $ck);
    }
    $this->unitCondition = $modelCondition;
  }

  public function selectOne($column, $val)
  {
    $baseModel = $this->baseModel;
    $model = $this->models[$baseModel]->selectOne($column, $val);
    $this->makedModels[$baseModel] = $model;
    $this->modelsData[$baseModel]  = $model->getData();
    $this->makeModel($baseModel, $model);

    $data = array();
    foreach ($this->makedModels as $model) {
      $mdlName = convert_to_modelname($model->table);
      $data[$mdlName] = $model->getData();
    }
    $this->addPrefixData();

    foreach ($this->modelsData as $data)
      $this->fusionedData = array_merge($data, $this->fusionedData);

    return $this;
  }

  public function makeModel($mdlName, $model)
  {
    if (!array_key_exists($mdlName, $this->unitCondition)) return null;

    foreach ($this->unitCondition[$mdlName] as $cm => $keys) {
      list($pk, $ck) = $keys;
      $model = $this->models[$cm]->selectOne($ck, $model->$pk);
      $this->makedModels[$cm] = $model;
      $this->modelsData[$cm]  = $model->getData();
      $this->makeModel($cm, $model);
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
    foreach ($this->updateModels as $mdlName) {
      $model   = $this->makedModels[$mdlName];
      $newData = $this->modelsNewData[$mdlName];
      foreach ($newData as $key => $val) {
        $key = str_replace("{$mdlName}_", '', $key);
        $model->$key = $val;
      }
      $model->save();
    }
  }
}
