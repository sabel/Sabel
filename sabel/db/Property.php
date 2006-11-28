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
    $schema  = array(),
    $columns = array();

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

    foreach (array_keys($props) as $key) {
      if (isset($mdlProps[$key])) $props[$key] = $mdlProps[$key];
    }

    if (array_key_exists('connectName', $mdlProps)) {
      $conName = $mdlProps['connectName'];
    } else {
      $conName = 'default';
    }

    $properties = $this->initSchema($mdlName, $conName, $props['table']);

    if (is_null($properties['primaryKey']))
      trigger_error('primary key not found in ' . $properties['table'], E_USER_NOTICE);

    $this->overrideProps = $props;
    $this->properties    = $properties;
  }

  public function initSchema($mdlName, $conName, $tblName)
  {
    $tblName = ($tblName === '') ? convert_to_tablename($mdlName) : $tblName;
    $cache   = Sabel_DB_SimpleCache::get('schema_' . $tblName);

    if ($cache) {
      $this->schema  = $cache;
      $this->columns = Sabel_DB_SimpleCache::get('columns_' . $tblName);
      return Sabel_DB_SimpleCache::get('props_' . $tblName);
    }

    $sName    = Sabel_DB_Connection::getSchema($conName);
    $accessor = new Sabel_DB_Schema_Accessor($conName, $sName);
    $clsName  = 'Schema_' . $mdlName;

    if (class_exists($clsName, false)) {
      $sClass     = new $clsName();
      $properties = $sClass->getProperty();
      $properties['table'] = $tblName;

      $tblSchema  = $accessor->getTable($tblName);
    } else {
      $database   = Sabel_DB_Connection::getDB($conName);
      $engine     = ($database === 'mysql') ? $accessor->getTableEngine($tblName) : null;
      $tblSchema  = $accessor->getTable($tblName);

      $properties = array('connectName'  => $conName,
                          'primaryKey'   => $tblSchema->getPrimaryKey(),
                          'incrementKey' => $tblSchema->getIncrementKey(),
                          'tableEngine'  => $engine,
                          'table'        => $tblName);
    }

    $this->schema  = $tblSchema;
    $this->columns = array_keys($tblSchema->getColumns());

    Sabel_DB_SimpleCache::add('columns_' . $tblName, $this->columns);
    Sabel_DB_SimpleCache::add('props_'   . $tblName, $properties);

    return $properties;
  }

  public function set($prop)
  {
    $myProp =& $this->properties;

    $myProp['table']        = $prop['table'];
    $myProp['connectName']  = (isset($prop['connectName']))  ? $prop['connectName']  : 'default';
    $myProp['incrementKey'] = (isset($prop['incrementKey'])) ? $prop['incrementKey'] : null;
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
    if (array_key_exists($key, $this->properties)) {
      return $this->properties[$key];
    }

    if (!isset($this->data[$key])) return null;
    return $this->convertData($key, $this->data[$key]);
  }

  public function convertData($key, $data)
  {
    $schema = $this->schema->getColumns();
    if (!isset($schema[$key])) return $data;

    switch ($schema[$key]->type) {
      case Sabel_DB_Schema_Const::INT:
        return (int)$data;
      case Sabel_DB_Schema_Const::FLOAT:
      case Sabel_DB_Schema_Const::DOUBLE:
        return (float)$data;
      case SabeL_DB_Schema_Const::BOOL:
        if (is_int($data)) {
          $data = ($data === 1);
        } else {
          $data = (in_array($data, array('1', 't', 'true')));
        }
    }
    return $data;
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

  public function getSchema()
  {
    return $this->schema;
  }

  public function getColumns()
  {
    return $this->columns;
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

  /**
   * this method is for mysql.
   * examine the engine of the table.
   *
   * @param  object $driver driver an instance of Driver_Native_Mysql or Driver_Pdo_Driver
   * @return string table engine.
   */
  public function getStructure()
  {
    return $this->overrideProps['structure'];
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
