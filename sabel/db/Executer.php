<?php

class Sabel_DB_Executer
{
  protected $model = null;

  public static function initialize($model)
  {
    $self = new self();
    $self->setModel($model);
    return $self;
  }

  public function setModel($model)
  {
    $this->model = $model;
  }

  public function getDriver($model)
  {
    return Sabel_DB_Connection::getDBDriver($model->getConnectName());
  }

  public function execute()
  {
    $model = $this->model;

    $conditions  = $model->getCondition();
    $constraints = $model->getConstraint();

    $driver = $this->getDriver($model);
    $driver->makeQuery($conditions, $constraints);
    $this->tryExecute($driver);
    return $driver->getResultSet();
  }

  public function update($data)
  {
    $model = $this->model;

    $driver = $this->getDriver($model);
    $driver->setUpdateSQL($model->getTableName(), $data);
    $driver->makeQuery($model->getCondition());
    $this->tryExecute($driver);
  }

  public function insert($data, $idColumn)
  {
    $model = $this->model;

    try {
      $driver = $this->getDriver($model);
      $driver->executeInsert($model->getTableName(), $data, $idColumn);
      return $driver->getLastInsertId();
    } catch (Exception $e) {
      $this->executeError($e->getMessage());
    }
  }

  public function multipleInsert($data, $idColumn)
  {
    $model = $this->model;

    try {
      $driver = $this->getDriver($model);
      foreach ($data as $val) $driver->executeInsert($model->getTableName(), $val, $idColumn);
    } catch (Exception $e) {
      $this->executeError($e->getMessage());
    }
  }

  public function executeQuery($sql, $param)
  {
    $driver = Sabel_DB_Connection::getDBDriver($this->model->getConnectName());
    $this->tryExecute($driver, $sql, $param);
    return $driver->getResultSet();
  }

  public function tryExecute($driver, $sql = null, $param = null)
  {
    try {
      $driver->execute($sql, $param);
    } catch (Exception $e) {
      $this->executeError($e->getMessage());
    }
  }

  public function executeError($errorMsg)
  {
    throw new Exception($errorMsg);
  }
}
