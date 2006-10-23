<?php

class Sabel_DB_UnitedModel
{
  private
    $models     = array(),
    $modelsData = array();

  private
    $baseTable  = '',
    $unitedCols = array(),
    $modelCols  = array();

  private
    $unitCondition = array();

  public function __construct($models, $mdlNames)
  {
    for ($i = 0; $i < sizeof($models); $i++) {
      $tblName = convert_to_tablename($mdlNames[$i]);
      $modelCols[$tblName] = $models[$i]->getColumnNames();
    }

    $this->models    = $models;
    $this->modelCols = $modelCols;
    $this->baseTable = convert_to_tablename($mdlNames[0]);
  }

  public function __get($key)
  {
    foreach ($this->modelsData as $mdlName => $data) {
      if (isset($data[$key])) return $data[$key];
    }
    return null;
  }

  public function __call($method, $parameters)
  {
    // @todo
  }

  public function setUnitCondition($unitCondition)
  {
    if (!is_array($unitCondition)) $unitCondition = (array)$unitCondition;

    $join = array();
    foreach ($unitCondition as $condition) {
      list($pKey, $cKey)   = explode(':', $condition);
      list($pTable, $pCol) = explode('.', $pKey);
      list($cTable, $cCol) = explode('.', $cKey);

      $pTable = convert_to_tablename($pTable);
      $cTable = convert_to_tablename($cTable);

      array_push($join, " LEFT JOIN $cTable ON {$cTable}.{$cCol} = {$pTable}.{$pCol}");
    }
    $this->unitCondition = join('', $join);
  }

  public function selectOne($column, $val)
  {
    $sql = array();
    foreach ($this->modelCols as $tblName => $columns) {
      if ($tblName === $this->baseTable) {
        foreach ($columns as $colName) array_push($sql, "{$tblName}.{$colName}, ");
      } else {
        $mdlName = convert_to_modelname($tblName);
        foreach ($columns as $colName) {
          array_push($sql, "{$tblName}.{$colName} AS {$mdlName}_{$colName}, ");
        }
      }
    }
    $baseTable = $this->baseTable;
    $condition = " WHERE {$baseTable}.{$column} = $val";
    $sql = 'SELECT ' . substr(join('', $sql), 0, -2) . " FROM $baseTable" . $this->unitCondition . $condition;

    $result = $this->models[0]->execute($sql);
    return ($result) ? $this->remakeData($result[0]) : false;
  }

  private function remakeData($model)
  {
    $modelsData   = array();
    $prefixTables = array_diff(array_keys($this->modelCols), (array)$this->baseTable);

    $data = $model->getData();
    foreach ($prefixTables as $tblName) {
      $mdlName = convert_to_modelname($tblName);
      foreach ($data as $key => $val) {
        if (strpos($key, "{$mdlName}_") === 0) {
          $newKey  = str_replace("{$mdlName}_", '', $key);
          $modelsData[$mdlName][$newKey] = $val;
          $modelsData[$mdlName][$key]    = $val;
          unset($data[$key]);
        }
      }
    }

    $baseData = array();
    $mdlName  = convert_to_modelname($this->baseTable);
    foreach ($data as $key => $val) $baseData[$mdlName][$key] = $val;
    $this->modelsData = array_merge($baseData, $modelsData);
    return $this;
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
    $result = $schemas[$baseModelName];
    unset($schemas[$baseModelName]);

    foreach ($schemas as $mdlName => $data) {
      foreach ($data as $key => $val) {
        if (!array_key_exists($key, $result)) $result[$key] = $val;
        $result["{$mdlName}_$key"] = $val;
      }
    }
    return $result;
  }
}
