<?php

/**
 * Sabel_DB_Condition_Builder_Pdo
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Condition_Builder_Pdo extends Sabel_DB_Condition_Builder_Base
{
  protected $count = 1;

  public function initialize($driver)
  {
    $this->count  = 1;
    $this->driver = $driver;
  }

  public function buildNormal($condition)
  {
    $driver  = $this->driver;
    $bindKey = $this->dotRep($condition->key . $this->count++);
    $bindVal = $driver->escape($condition->value);
    $driver->setBindValues(array($bindKey => $bindVal));
    return $this->getKey($condition) . " = :{$bindKey}";
  }

  public function buildBetween($condition)
  {
    $driver = $this->driver;

    $f   = $this->count++;
    $t   = $this->count++;
    $val = $driver->escape($condition->value);

    $driver->setBindValues(array("from{$f}" => $val[0],
                                 "to{$t}"   => $val[1]));

    return $this->getKey($condition) . " BETWEEN :from{$f} AND :to{$t}";
  }

  public function buildCompare($condition)
  {
    $driver  = $this->driver;
    $bindKey = $this->dotRep($condition->key . $this->count++);

    list ($lg, $val) = $condition->value;

    $driver->setBindValues(array($bindKey => $driver->escape($val)));
    return $condition->key . " $lg :{$bindKey}";
  }

  protected function createLike($val, $condition, $esc = null)
  {
    $driver  = $this->driver;
    $bindKey = $this->dotRep($condition->key . $this->count++);
    $bindVal = $driver->escape($val);
    $driver->setBindValues(array($bindKey => $bindVal));

    $query = $this->getKey($condition) . " LIKE :{$bindKey}";
    if (isset($esc)) $query .= " escape '{$esc}'";

    return $query;
  }

  private function dotRep($bindKey)
  {
    return (strpos($bindKey, ".") === false) ? $bindKey : str_replace(".", "_", $bindKey);
  }
}
