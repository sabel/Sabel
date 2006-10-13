<?php

class Sabel_DB_Relation extends Sabel_DB_Mapper
{
  protected $parentTables = array();
  protected $structure    = 'normal';

  public function addParent($row, $tableName, $structure)
  {
    $this->parentTables = array($tableName);
    $this->structure    = $structure;

    foreach ($row as $key => $val) {
      if (strpos($key, '_id') !== false) {
        $table = str_replace('_id', '', $key);
        $modelName = array_map('ucfirst', explode('_', $table));
        $row[join('', $modelName)] = $this->addParentModels($table, $val);
      }
    }
    return $row;
  }

  protected function addParentModels($table, $id)
  {
    $table = strtolower($table);
    if ($this->structure !== 'tree' && $this->isAcquired($table)) return null;

    $model = $this->newClass($table);
    if (is_null($id)) return $model;

    if (!is_array($row = Sabel_DB_SimpleCache::get($table . $id))) {
      $model->setCondition($model->getIncColumn(), $id);
      $this->getStatement($model)->setBasicSQL("SELECT {$model->getProjection()} FROM $table");
      $resultSet = $this->getExecuter($model)->execute();

      if (!$row = $resultSet->fetch()) {
        $model->enableSelected();
        $model->id = $id;
        return $model;
      }
      Sabel_DB_SimpleCache::add($table . $id, $row);
    }

    foreach ($row as $key => $val) {
      if (strpos($key, '_id') !== false) {
        $key = str_replace('_id', '', $key);
        $modelName = array_map('ucfirst', explode('_', $key));
        $row[join('', $modelName)] = $this->addParentModels($key, $val);
      } else {
        $row[$key] = $val;
      }
    }
    $this->setSelectedProperty($model, $row);
    $model->unsetNewData();
    return $model;
  }

  private function isAcquired($table)
  {
    if (in_array($table, $this->parentTables)) return true;
    $this->parentTables[] = $table;
    return false;
  }
}
