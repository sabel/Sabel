<?php

Sabel::using('Sabel_DB_SimpleCache');

/**
 * Sabel_DB_Model_Property
 *
 * @category   DB
 * @package    org.sabel.db
 * @subpackage model
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Model_Property
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

  public function __construct($mdlName, $mdlProps)
  {
    $props = array('table'      => '',
                   'structure'  => 'normal',
                   'withParent' => false,
                   'projection' => '*',
                   'myChildren' => null);

    foreach (array_keys($props) as $key) {
      if (isset($mdlProps[$key])) $props[$key] = $mdlProps[$key];
    }

    if (array_key_exists('childConstraints', $mdlProps)) {
      foreach ($mdlProps['childConstraints'] as $cldName => $param) {
        $this->cconst($cldName, $param);
      }
    }

    if (array_key_exists('connectName', $mdlProps)) {
      $conName = $mdlProps['connectName'];
    } else {
      $conName = 'default';
    }

    $this->initSchema($mdlName, $conName, $props['table']);
    $this->overrideProps = $props;
  }

  protected function initSchema($mdlName, $conName, $tblName)
  {
    $tblName = ($tblName === '') ? convert_to_tablename($mdlName) : $tblName;
    $cache   = Sabel_DB_SimpleCache::get('schema_' . $tblName);

    if ($cache) {
      $this->schema     = $cache;
      $this->columns    = Sabel_DB_SimpleCache::get('columns_' . $tblName);
      $this->properties = Sabel_DB_SimpleCache::get('props_' . $tblName);
      return null;
    }

    $sClsName  = 'Schema_' . $mdlName;
    $tblSchema = create_schema($sClsName);

    if (is_object($tblSchema)) {
      $sClass     = new $sClsName();
      $properties = $sClass->getProperty();
      $properties['table'] = $tblName;
    } else {
      list($tblSchema, $properties) = $this->createSchema($conName, $tblName);
    }

    $this->schema  = $tblSchema;
    $this->columns = array_keys($tblSchema->getColumns());

    Sabel_DB_SimpleCache::add('schema_'  . $tblName, $tblSchema);
    Sabel_DB_SimpleCache::add('columns_' . $tblName, $this->columns);
    Sabel_DB_SimpleCache::add('props_'   . $tblName, $properties);

    if ($properties['primaryKey'] === null)
      trigger_error('primary key not found in ' . $properties['table'], E_USER_NOTICE);

    $this->properties = $properties;
  }

  protected function createSchema($conName, $tblName)
  {
    Sabel::using('Sabel_DB_Schema_Accessor');

    $scmName    = Sabel_DB_Connection::getSchema($conName);
    $database   = Sabel_DB_Connection::getDB($conName);
    $accessor   = new Sabel_DB_Schema_Accessor($conName, $scmName);
    $engine     = ($database === 'mysql') ? $accessor->getTableEngine($tblName) : null;
    $tblSchema  = $accessor->getTable($tblName);

    $properties = array('connectName'  => $conName,
                        'primaryKey'   => $tblSchema->getPrimaryKey(),
                        'incrementKey' => $tblSchema->getIncrementKey(),
                        'tableEngine'  => $engine,
                        'table'        => $tblName);

    return array($tblSchema, $properties);
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
    if (!isset($this->data[$key])) return null;
    return $this->convertData($key, $this->data[$key]);
  }

  public function convertData($key, $data)
  {
    $schema = $this->schema->getColumns();
    if (!isset($schema[$key])) return $data;

    switch ($schema[$key]->type) {
      case Sabel_DB_Type_Const::INT:
        return (int)$data;
      case Sabel_DB_Type_Const::FLOAT:
      case Sabel_DB_Type_Const::DOUBLE:
        return (float)$data;
      case SabeL_DB_Type_Const::BOOL:
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

  public function getTableProperties()
  {
    return $this->properties;
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

  public function setChildConstraint($mdlName, $constraints)
  {
    if (!is_array($constraints)) {
      throw new Exception('Error:setChildConstraint() second argument must be an array.');
    } else {
      foreach ($constraints as $key => $val) {
        $this->childConstraints[$mdlName][$key] = $val;
      }
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
      Sabel::using('Sabel_DB_Condition');
      $condition = new Sabel_DB_Condition($arg1, $arg2, $arg3);
      $this->childConditions[] = $condition;
    }
  }

  public function getChildCondition()
  {
    return $this->childConditions;
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

  /**
   * an alias for setChildConstraint.
   *
   */
  public function cconst($mdlName, $constraints)
  {
    $this->setChildConstraint($mdlName, $constraints);
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
