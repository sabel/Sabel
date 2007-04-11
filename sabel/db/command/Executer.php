<?php

/**
 * Sabel_DB_Command_Executer
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Command_Executer
{
  const SKIP = 0x01;

  protected $model  = null;
  protected $driver = null;
  protected $result = null;

  protected $arguments = array();

  protected $incrementId   = null;
  protected $beforeMethods = array();
  protected $afterMethods  = array();

  public function __construct($model)
  {
    $this->model = $model;
    $driver = Sabel_DB_Config::loadDriver($model->getConnectionName());

    $this->beforeMethods = $driver->getBeforeMethods();
    $this->afterMethods  = $driver->getAfterMethods();

    $this->driver = $driver;
  }

  public function getModel()
  {
    return $this->model;
  }

  public function getDriver()
  {
    return $this->driver;
  }

  public function getArguments()
  {
    return $this->arguments;
  }

  public function __call($command, $args)
  {
    $this->arguments = $args;

    $bms = $this->beforeMethods;
    $ams = $this->afterMethods;

    if (isset($bms[$command])) {
      if ($this->doMethods($bms[$command])) return $this;
    }

    $commander = Sabel_DB_Command_Loader::getClass($command);
    $result = $commander->execute($this);
    if ($result === self::SKIP) return $this;

    if (isset($bms["execute"])) {
      if ($this->doMethods($bms["execute"])) return $this;
    }

    $this->result = $this->driver->execute();

    if (isset($ams["execute"])) {
      if ($this->doMethods($ams["execute"])) return $this;
    }

    if (isset($ams[$command])) {
      if ($this->doMethods($ams[$command])) return $this;
    }

    return $this;
  }

  public function getResult()
  {
    return $this->result;
  }

  public function setResult($result)
  {
    $this->result = $result;
  }

  public function getIncrementId()
  {
    return $this->incrementId;
  }

  public function setIncrementId($id)
  {
    $this->incrementId = $id;
  }

  protected function doMethods($methods)
  {
    $skip = false;
    foreach ($methods as $method) {
      $result = $this->driver->$method($this);
      if ($result === self::SKIP) $skip = true;
    }

    return $skip;
  }
}
