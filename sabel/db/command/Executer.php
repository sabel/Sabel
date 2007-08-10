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
  const SKIP = -1;

  protected
    $model       = null,
    $driver      = null,
    $result      = null,
    $arguments   = array(),
    $incrementId = null;

  public function __construct($model)
  {
    if (!$model instanceof Sabel_DB_Model) {
      $name = get_class($model);
      throw new Exception("'{$name}' should be instance of Sabel_DB_Model.");
    }

    $this->model  = $model;
    $this->driver = Sabel_DB_Config::loadDriver($model->getConnectionName());

    if (!$this->driver instanceof Sabel_DB_Abstract_Driver) {
      $name = get_class($model);
      throw new Exception("'{$name}' should be instance of Sabel_DB_Abstract_Driver.");
    }
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

    $commander = Sabel_DB_Command_Loader::load($command);
    $commandId = $commander->getCommandId();

    if (!$this->doInterrupt("before", $commandId)) {
      $commander->execute($this);
      $this->doInterrupt("after", $commandId);
    }

    return $this;
  }

  public function setResult($result)
  {
    $this->result = $result;
  }

  public function getResult()
  {
    return $this->result;
  }

  public function getIncrementId()
  {
    return $this->incrementId;
  }

  public function setIncrementId($id)
  {
    $this->incrementId = $id;
  }

  protected function doInterrupt($type, $commandId)
  {
    $driver = $this->driver;

    if ($type === "before") {
      $methods = $driver->getBeforeMethods();
    } else {
      $methods = $driver->getAfterMethods();
    }

    if (isset($methods["all"])) {
      $method = $methods["all"];
      $driver->$method($this);
    }

    if (isset($methods[$commandId])) {
      $method = $methods[$commandId];
      return ($driver->$method($this) === self::SKIP);
    }
  }
}
