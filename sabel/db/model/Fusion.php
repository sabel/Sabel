<?php

/**
 * Sabel_DB_Model_Fusion
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage model
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Model_Fusion
{
  private
    $models        = array(),
    $makedModels   = array(),
    $modelsData    = array(),
    $fusionedData  = array();

  private
    $baseModel     = '',
    $unitedCols    = array(),
    $combCondition = array();

  private
    $updateModels  = array(),
    $modelsNewData = array();

  public function __construct($models, $mdlNames)
  {
    foreach ($models as $model) {
      $mdlName = convert_to_modelname($model->getTableName());
      $this->models[$mdlName] = $model;
    }

    $this->baseModel = $mdlNames[0];
  }

  public function __set($key, $val)
  {
    foreach ($this->modelsData as $mdlName => $data) {
      if (isset($data[$key])) {
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

  public function setCombination($fusionCondition)
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
      $this->combCondition[$cModel][$pModel] = array($fKey, $pKey);
      $this->combCondition[$pModel][$cModel] = array($pKey, $fKey);
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
      $mdlName = convert_to_modelname($model->getTableName());
      $data[$mdlName] = $model->getData();
    }
    $this->addPrefixData();

    foreach ($this->modelsData as $data)
      $this->fusionedData = array_merge($data, $this->fusionedData);

    return $this;
  }

  public function createParents($mdlName, $model)
  {
    if (!isset($this->combCondition[$mdlName])) return null;
    $this->createModel($this->combCondition[$mdlName], $model, $mdlName);
  }

  protected function createModel($parents, $child, $mdlName)
  {
    $mdlNames = array();
    $models   = array();

    foreach ($parents as $parent => $keys) {
      $this->unsetCombCondition($parent, $mdlName);

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

  protected function unsetCombCondition($key1, $key2)
  {
    $cc =& $this->combCondition;
    unset($cc[$key1][$key2]);
    if (empty($cc[$key1])) unset($cc[$key1]);
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
      $mdlName = convert_to_modelname($model->getTableName());
      $schemas[$mdlName] = $this->setValueToSchema($mdlName, $columns);
    }
    return $this->mixSchema($schemas);
  }

  private function setValueToSchema($mdlName, $columns)
  {
    foreach ($this->modelsData[$mdlName] as $name => $data) {
      if (isset($columns[$name])) $columns[$name]->value = $data;
    }
    return $columns;
  }

  private function mixSchema($schemas)
  {
    $result = array();
    foreach ($schemas as $mdlName => $data) {
      foreach ($data as $key => $val) {
        if (!isset($result[$key])) $result[$key] = $val;
        $result["{$mdlName}_$key"] = $val;
      }
    }
    return $result;
  }

  public function save()
  {
    foreach ($this->updateModels as $mdlName) {
      $model = $this->makedModels[$mdlName];
      foreach ($this->modelsNewData[$mdlName] as $key => $val) {
        $key = str_replace("{$mdlName}_", '', $key);
        $model->$key = $val;
      }
      $this->makedModels[$mdlName]->save();
    }
  }
}
