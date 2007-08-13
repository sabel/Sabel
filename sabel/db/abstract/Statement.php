<?php

/**
 * Sabel_DB_Abstract_Statement
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Abstract_Statement
{
  protected $sql = "";
  protected $driver = null;
  protected $bindValues = array();

  abstract public function getStatementType();

  public function __construct(Sabel_DB_Abstract_Driver $driver)
  {
    $this->driver = $driver;
  }

  public function setSql($sql)
  {
    $this->sql = $sql;

    return $this;
  }

  public function getSql()
  {
    return $this->sql;
  }

  public function execute()
  {
    $bindParam = $this->createBindParam($this->bindValues);
    return $this->driver->execute($this->sql, $bindParam);
  }

  public function setBind($bindValues, $add = true)
  {
    if ($add) {
      foreach ($bindValues as $key => $val) {
        $this->bindValues[$key] = $val;
      }
    } else {
      $this->bindValues = $bindValues;
    }
  }

  protected function createBindParam($bindValues)
  {
    $bindParam = array();

    foreach ($bindValues as $key => $value) {
      $bindParam[":{$key}"] = $value;
    }

    $this->bindValues = array();
    return $bindParam;
  }
}
