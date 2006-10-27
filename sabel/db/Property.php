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
    $schema = array();

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

    $clsName = 'Schema_' . $mdlName;
    if (class_exists($clsName, false)) {
      $properties = $this->initSchema(new $clsName());
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

  private function initSchema($sClass)
  {
    $ps = $sClass->getProperty();
    $properties = array('connectName'  => $ps['connectName'],
                        'primaryKey'   => $ps['primaryKey'],
                        'incrementKey' => $ps['incrementKey'],
                        'tableEngine'  => $ps['tableEngine']);

    $this->schema = $sClass->get();
    return $properties;
  }

  private function initTableName($mdlName, &$properties)
  {
    $properties['table'] = convert_to_tablename($mdlName);
  }

  public function setTableName($tblName)
  {
    $this->properties['table'] = $tblName;
  }

  public function setSchema($tblName)
  {
    $sClass = get_schema_by_tablename($tblName);
    if ($sClass) {
      $properties = $this->initSchema($sClass);
      $this->overrideProps['autoNumber'] = (isset($properties['incrementKey']));
      $this->properties = array_merge($this->properties, $properties);
    }
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
    $properties = $this->properties;
    if (isset($properties[$key])) return $properties[$key];
    if (!isset($this->data[$key])) return null;

    $data = $this->data[$key];
    return ($this->schema) ? $this->convertData($key, $data) : $data;
  }

  private function convertData($key, $data)
  {
    if (!isset($this->schema[$key])) return $data;

    switch ($this->schema[$key]['type']) {
      case Sabel_DB_Schema_Const::INT:
        return (int)$data;
      case SabeL_DB_Schema_Const::BOOL:
        return (in_array($data, array('1', 't', 'true')));
      case Sabel_DB_Schema_Const::FLOAT:
      case Sabel_DB_Schema_Const::DOUBLE:
        return (float)$data;
      default:
        return $data;
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

  public function hasSchema()
  {
    return (!empty($this->schema));
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

  /**
   * setting condition.
   *
   * @param mixed    $arg1 column name ( with the condition prefix ),
   *                       or value of primary key,
   *                       or object of Sabel_DB_Condition.
   * @param mixed    $arg2 condition value.
   * @param constant $arg3 denial ( Sabel_DB_Condition::NOT )
   * @return void
   */
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
      $condition = new Sabel_DB_Condition($arg1, $arg2, $arg3);
      $this->conditions[$condition->key] = $condition;
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

  /**
   * setting constraint.
   * the keys which you can use are 'group', 'having', 'order', 'limit', 'offset'.
   *
   * @param mixed $arg1 array constriant(s). or string key.
   * @param mixed $arg2 value of integer or value of string.
   * @return void
   */
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
   * @param  object $driver driver an instance of Sabel_DB_Driver_Native_Mysql
   *                                           or Sabel_DB_Driver_Pdo_Driver
   * @return string table engine.
   */
  private function getTableEngine($driver = null)
  {
    if (is_null($this->properties['tableEngine'])) {
      $msg = 'schema class is not found. please generate it by schema.php';
      //trigger_error($msg, E_USER_NOTICE);

      $cn = $this->properties['connectName'];
      $sc = Sabel_DB_Connection::getSchema($cn);
      $sa = new Sabel_DB_Schema_Accessor($cn, $sc);

      return $sa->getTableEngine($this->properties['table'], $driver);
    } else {
      return $this->properties['tableEngine'];
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
