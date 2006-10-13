<?php

class Sabel_DB_Executer
{
  protected $model  = null;
  protected $driver = null;

  public function __construct($model)
  {
    $this->model = $model;
    $this->initialize($model);
  }

  public function initialize($model = null)
  {
    if (is_null($model)) $model = $this->model;

    $driver = $this->driver = Sabel_DB_Connection::createDBDriver($model->getConnectName());
    if ($driver instanceof Sabel_DB_Driver_Native_Mssql) {
      $driver->setDefaultOrderKey($model->getPrimaryKey());
    }
  }

  public function getDriver()
  {
    return $this->driver;
  }

  public function execute()
  {
    $model = $this->model;

    $driver = $this->driver;
    $driver->makeQuery($model->getCondition(), $model->getConstraint());
    $this->tryExecute($driver);
    return $driver->getResultSet();
  }

  public function update($data)
  {
    $model = $this->model;

    $driver = $this->driver;
    $driver->setUpdateSQL($model->getTableName(), $data);
    $driver->makeQuery($model->getCondition());
    $this->tryExecute($driver);
  }

  public function insert($data, $idColumn)
  {
    $model = $this->model;

    try {
      $driver = $this->driver;
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
      $driver = $this->driver;
      foreach ($data as $val) $driver->executeInsert($model->getTableName(), $val, $idColumn);
    } catch (Exception $e) {
      $this->executeError($e->getMessage());
    }
  }

  public function executeQuery($sql, $param)
  {
    $this->tryExecute($this->driver, $sql, $param);
    return $this->driver->getResultSet();
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
