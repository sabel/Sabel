<?php

/**
 * Sabel_DB_Property
 *
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
    $selectConditions = array(),
    $childConditions  = array(),
    $childConstraints = array();

  private
    $data     = array(),
    $newData  = array(),
    $selected = false;

  public function __construct($mdlName = null, $mdlProps = null)
  {
    if (is_null($mdlName)) return null;

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

    if ($props['table'] === '') {
      $this->initTableName($mdlName, $properties);
    } else {
      $properties['table'] = $props['table'];
    }

    if (is_null($properties['primaryKey']))
      trigger_error('primary key not found in '.$properties['table'], E_USER_NOTICE);

    $this->overrideProps = $props;
    $this->properties    = $properties;
  }

  public function set($properties)
  {
    $this->properties = $properties;
  }

  private function initTableName($mdlName, &$properties)
  {
    $properties['table'] = convert_to_tablename($mdlName);
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

  public function setSchema($tblName)
  {
    $sClass = get_schema_by_tablename($tblName);

    if ($sClass) {
      $properties = $this->initSchema($sClass);
      $this->overrideProps['autoNumber'] = (isset($properties['incrementKey']));
      $this->properties = array_merge($this->properties, $properties);
    }
  }

  public function __set($key, $val)
  {
    $this->data[$key] = $val;
    if ($this->selected) $this->newData[$key] = $val;
  }

  public function dataSet($key, $val)
  {
    $this->data[$key] = $val;
  }

  public function __get($key)
  {
    $properties = $this->properties;
    if (isset($properties[$key]))  return $properties[$key];
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

  public function getValidateData()
  {
    return ($this->isSelected()) ? $this->newData : $this->data;
  }

  public function setProperties($row)
  {
    if (!is_array($row)) {
      $errorMsg = 'Sabel_DB_Property::setProperties(). argument should be an array.';
      throw new Exception($errorMsg);
    }
    foreach ($row as $key => $val) $this->data[$key] = $val;
  }

  public function hasSchema()
  {
    return (!empty($this->schema));
  }

  public function getSchema()
  {
    $cols = array();
    foreach ($this->schema as $colName => $colInfo) {
      $co = new Sabel_DB_Schema_Column();
      $co->name = $colName;
      $cols[$colName] = $co->make($colInfo);
    }

    return $cols;
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

  public function setTableName($tblName)
  {
    $this->properties['table'] = $tblName;
  }

  public function setChildConstraint($arg1, $arg2 = null)
  {
    if (isset($arg1) && is_array($arg2)) {
      foreach ($arg2 as $key => $val) $this->childConstraints[$arg1][$key] = $val;
    } elseif (isset($arg2)) {
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

  public function setDefChildConstraint($constraints)
  {
    $this->overrideProps['defChildConstraints'] = $constraints;
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

  public function getStructure()
  {
    return $this->overrideProps['structure'];
  }

  public function isAutoNumber()
  {
    return $this->overrideProps['autoNumber'];
  }

  public function isWithParent()
  {
    return $this->overrideProps['withParent'];
  }

  public function getMyChildren()
  {
    return $this->overrideProps['myChildren'];
  }

  public function getProjection()
  {
    return $this->overrideProps['projection'];
  }

  public function getDefChildConstraint()
  {
    return $this->overrideProps['defChildConstraints'];
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
