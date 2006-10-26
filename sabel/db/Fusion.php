<?php

class Sabel_DB_Fusion
{
  private
    $models       = array(),
    $modelNames   = array(),
    $makedModels  = array(),
    $modelsData   = array(),
    $fusionedData = array();

  private
    $baseModel  = '',
    $unitedCols = array();

  private
    $unitCondition = array();

  private
    $updateModels  = array(),
    $modelsNewData = array();

  public function __construct($models, $mdlNames)
  {
    for ($i = 0; $i < sizeof($models); $i++) {
      $tblName = convert_to_tablename($mdlNames[$i]);
    }

    foreach ($models as $model) {
      $this->models[convert_to_modelname($model->table)] = $model;
    }

    $this->modelNames = $mdlNames;
    $this->baseModel  = $mdlNames[0];
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

  public function setFusion($fusionCondition)
  {
    if (!is_array($fusionCondition)) $fusionCondition = (array)$fusionCondition;

    foreach ($fusionCondition as $condition) {
      list($child, $parent) = explode(':', $condition);
      if (strpos($child, '.') === false) {
        $pModel = $parent;
        $cModel = $child;
        $pKey   = 'id';
        $fKey   = convert_to_tablename($pModel) . '_id';
      } else {
        list($cModel, $fKey) = explode('.', $child);
        list($pModel, $pKey) = explode('.', $parent);
      }
      $this->unitCondition[$cModel][$pModel] = array($fKey, $pKey);
      $this->unitCondition[$pModel][$cModel] = array($pKey, $fKey);

    }
  }

  public function selectOne($column, $val)
  {
    $baseModel = $this->baseModel;
    $model = $this->models[$baseModel]->selectOne($column, $val);
    $this->makedModels[$baseModel] = $model;
    $this->modelsData[$baseModel]  = $model->getData();
    $this->createParents($baseModel, $model);

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

  public function createParents($mdlName, $model)
  {
    if (!array_key_exists($mdlName, $this->unitCondition)) return null;
    $this->createModel($this->unitCondition[$mdlName], $model, $mdlName);
  }

  private function createModel($parents, $child, $mdlName)
  {
    $mdlNames = array();
    $models   = array();

    foreach ($parents as $parent => $keys) {
      unset($this->unitCondition[$parent][$mdlName]);

      list($fKey, $pKey) = $keys;
      $model = $this->models[$parent]->selectOne($pKey, $child->$fKey);
      $this->modelsData[$parent]  = $model->getData();
      $this->makedModels[$parent] = $model;

      $mdlNames[] = $parent;
      $models[]   = $model;
    }

    for ($i = 0; $i < sizeof($mdlNames); $i++) {
      $this->createParents($mdlNames[$i], $models[$i]);
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
