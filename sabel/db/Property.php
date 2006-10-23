<?php

/**
 * Sabel_DB_Property
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Property
{
  private
    $properties    = array(),
    $overrideProps = array();

  private
    $conditions       = array(),
    $selectConditions = array(),
    $childConditions  = array(),
    $constraints      = array(),
    $childConstraints = array();

  private
    $data     = array(),
    $newData  = array(),
    $selected = false;

  public function __construct($mdlName, $mdlProps)
  {
    $props = array('table'               => '',
                   'structure'           => 'normal',
                   'withParent'          => false,
                   'projection'          => '*',
                   'myChildren'          => null,
                   'defChildConstraints' => array());

    foreach ($mdlProps as $key => $val) {
      if (array_key_exists($key, $props)) $props[$key] = $val;
    }

    $sClass = 'Schema_' . $mdlName;
    if (class_exists($sClass, false)) {
      $sc = new $sClass();
      $properties = array('connectName'  => $sc->getConnectName(),
                          'primaryKey'   => $sc->getPrimaryKey(),
                          'incrementKey' => $sc->getIncrementKey(),
                          'tableEngine'  => $sc->getTableEngine());
    } else {
      $properties = array('connectName'  => 'default',
                          'primaryKey'   => 'id',
                          'incrementKey' => 'id',
                          'tableEngine'  => null);
    }

    $props['autoNumber'] = (isset($properties['incrementKey']));
    if ($props['table'] === '') $this->initTableName($mdlName, $properties);

    $this->overrideProps = $props;
    $this->properties    = $properties;
  }

  private function initTableName($mdlName, &$properties)
  {
    $properties['table'] = convert_to_tablename($mdlName);
  }

  public function setTableName($tblName)
  {
    $this->properties['table'] = $tblName;
  }

  public function __call($column, $args)
  {
    @list($arg1, $arg2) = $args;
    $this->setCondition($column, $arg1, $arg2);
  }

  public function __set($key, $val)
  {
    $this->data[$key] = $val;
    if ($this->selected) $this->newData[$key] = $val;
  }

  public function __get($key)
  {
    if (array_key_exists($key, $this->properties)) {
      return $this->properties[$key];
    } else {
      return (isset($this->data[$key])) ? $this->data[$key] : null;
    }
  }

  public function setProperties($row)
  {
    if (!is_array($row)) {
      $errorMsg = 'Error: setProperties(). argument should be an array.';
      throw new Exception($errorMsg);
    }
    foreach ($row as $key => $val) $this->data[$key] = $val;
  }

  public function setConnectName($connectName)
  {
    $this->properties['connectName'] = $connectName;
  }

  public function getData()
  {
    return $this->data;
  }

  public function toArray()
  {
    return $this->data;
  }

  public function getNewData()
  {
    return $this->newData;
  }

  public function unsetNewData()
  {
    $this->newData = array();
  }

  public function isSelected()
  {
    return $this->selected;
  }

  public function enableSelected()
  {
    $this->selected = true;
  }

  public function enableParent()
  {
    $this->overrideProps['withParent'] = true;
  }

  public function setProjection($p)
  {
    $this->overrideProps['projection'] = (is_array($p)) ? join(',', $p) : $p;
  }

  public function setCondition($arg1, $arg2 = null, $arg3 = null)
  {
    if (empty($arg1)) return null;

    if (is_object($arg1) || is_array($arg1)) {
      $this->conditions[] = $arg1;
    } else {
      if (is_null($arg2)) {
        $arg3 = null;
        $arg2 = $arg1;
        $arg1 = $this->properties['primaryKey'];
      }
      $this->conditions[] = new Sabel_DB_Condition($arg1, $arg2, $arg3);
    }
  }

  public function receiveCondition($conditions)
  {
    $this->conditions = $conditions;
  }

  public function getCondition()
  {
    return $this->conditions;
  }

  public function setChildConstraint($arg1, $arg2 = null)
  {
    if (isset($arg1) && is_array($arg2)) {
      foreach ($arg2 as $key => $val) $this->childConstraints[$arg1][$key] = $val;
    } else if (isset($arg2)) {
      $this->overrideProps['defChildConstraints'] = array($arg1 => $arg2);
    } else {
      $this->overrideProps['defChildConstraints'] = $arg1;
    }
  }

  public function receiveChildConstraint($constraints)
  {
    $this->childConstrains = $constraints;
  }

  public function getChildConstraint()
  {
    return $this->childConstraints;
  }

  public function setChildCondition($arg1, $arg2 = null, $arg3 = null)
  {
    if (is_object($arg1) || is_array($arg1)) {
      $this->childConditions[] = $arg1;
    } else {
      $condition = new Sabel_DB_Condition($arg1, $arg2, $arg3);
      $this->childConditions[] = $condition;
    }
  }

  public function getChildCondition()
  {
    return $this->childConditions;
  }

  public function setConstraint($arg1, $arg2 = null)
  {
    if (!is_array($arg1)) $arg1 = array($arg1 => $arg2);

    foreach ($arg1 as $key => $val) {
      if (isset($val)) $this->constraints[$key] = $val;
    }
  }

  public function receiveConstraint($constraints)
  {
    $this->constraints = $constraints;
  }

  public function getConstraint()
  {
    return $this->constraints;
  }

  public function setDefChildConstraint($constraints)
  {
    $this->overrideProps['defChildConstraints'] = $constraints;
  }

  public function unsetCondition()
  {
    $this->conditions  = array();
    $this->constraints = array();
  }

  public function unsetChildCondition()
  {
    $this->childConditions  = array();
    $this->childConstraints = array();
  }

  public function setSelectCondition($key, $condition)
  {
    $this->selectConditions[$key] = $condition;
  }

  public function getSelectCondition()
  {
    return $this->selectConditions;
  }

  public function receiveSelectCondition($conditions)
  {
    $this->selectConditions = $conditions;
  }

  public function checkIncColumn()
  {
    return ($this->isAutoNumber()) ? $this->properties['incrementKey'] : false;
  }

  /**
   * this method is for mysql.
   * examine the engine of the table.
   *
   */
  private function getTableEngine($driver = null)
  {
    $engine = $this->properties['tableEngine'];

    if (is_null($engine)) {
      $msg = 'schema class is not found. please generate it by schema.php';
      // @todo
      //trigger_error($msg, E_USER_NOTICE);

      $cn = $this->properties['connectName'];
      $sc = Sabel_DB_Connection::getSchema($cn);
      $sa = new Sabel_DB_Schema_Accessor($cn, $sc);

      return $sa->getTableEngine($this->properties['table'], $driver);
    } else {
      return $engine;
    }
  }

  public function checkTableEngine($driver = null)
  {
    $engine = $this->getTableEngine($driver);
    if ($engine !== 'InnoDB' && $engine !== 'BDB') {
      $msg = "begin transaction, but a table engine of the '{$this->table}' is {$engine}.";
      trigger_error($msg, E_USER_NOTICE);
      return false;
    } else {
      return true;
    }
  }

  public function getStructure()
  {
    return $this->getPropsValue('structure');
  }

  public function isAutoNumber()
  {
    return $this->getPropsValue('autoNumber');
  }

  public function isWithParent()
  {
    return $this->getPropsValue('withParent');
  }

  public function getMyChildren()
  {
    return $this->getPropsValue('myChildren');
  }

  public function getProjection()
  {
    return $this->getPropsValue('projection');
  }

  public function getDefChildConstraint()
  {
    return $this->getPropsValue('defChildConstraints');
  }

  private function getPropsValue($key)
  {
    return (isset($this->overrideProps[$key])) ? $this->overrideProps[$key] : null;
  }

  /**
   * an alias for setConstraint.
   *
   */
  public function sconst($arg1, $arg2 = null)
  {
    $this->setConstraint($arg1, $arg2);
  }

  /**
   * an alias for setChildConstraint.
   *
   */
  public function cconst($arg1, $arg2 = null)
  {
    $this->setChildConstraint($arg1, $arg2);
  }

  /**
   * an alias for setChildCondition.
   *
   */
  public function ccond($arg1, $arg2 = null)
  {
    $this->setChildCondition($arg1, $arg2);
  }
}
