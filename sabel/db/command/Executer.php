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
  protected $model  = null;
  protected $driver = null;
  protected $result = null;

  protected $incrementId   = null;
  protected $beforeMethods = array();
  protected $afterMethods  = array();

  public function __construct($model)
  {
    $this->model  = $model;
    $this->driver = $driver = load_driver($model->getConnectionName());

    $this->beforeMethods = $driver->getBeforeMethods();
    $this->afterMethods  = $driver->getAfterMethods();
  }

  public function getModel()
  {
    return $this->model;
  }

  public function getDriver()
  {
    return $this->driver;
  }

  public function __call($callMethod, $args)
  {
    $bms = $this->beforeMethods;
    $ams = $this->afterMethods;

    if (isset($bms[$callMethod])) $this->doMethods($bms[$callMethod]);

    switch ($callMethod) {
      case "select":
        Sabel_DB_Command_Select::build($this);
        break;

      case "update":
        Sabel_DB_Command_Update::build($this);
        break;

      case "insert":
        Sabel_DB_Command_Insert::build($this);
        break;

      case "arrayInsert":
        Sabel_DB_Command_ArrayInsert::build($this);
        break;

      case "delete":
        Sabel_DB_Command_Delete::build($this);
        break;

      case "query":
        $inp = (isset($args[1])) ? $args[1] : null;
        Sabel_DB_Command_Query::build($this, $args[0], $inp);
        break;

      default:
        throw new Exception("no such command. '{$callMethod}'");
    }

    if (isset($bms["execute"])) $this->doMethods($bms["execute"]);
    $this->result = $this->driver->execute();
    if (isset($ams["execute"])) $this->doMethods($ams["execute"]);

    if (isset($ams[$callMethod])) $this->doMethods($ams[$callMethod]);

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

  public function begin()
  {
    $this->driver->begin($this->model->getConnectionName());
  }

  public function commit()
  {
    $this->driver->loadTransaction()->commit();
  }

  public function rollback()
  {
    $this->driver->loadTransaction()->rollback();
  }

  protected function doMethods($methods)
  {
    foreach ($methods as $method) $this->driver->$method($this);
  }
}
