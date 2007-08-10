<?php

/**
 * Sabel_DB_Model
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Model
{
  protected
    $connectionName = "default";

  protected
    $tableName  = "",
    $modelName  = "",
    $schema     = null,
    $columns    = array(),
    $schemaCols = array(),
    $selected   = false;

  protected
    $values       = array(),
    $updateValues = array(),
    $saveValues   = array();

  public function __construct($arg1 = null, $arg2 = null)
  {
    $this->initialize();
  }

  protected function initialize($mdlName = null)
  {
    if ($mdlName === null) $mdlName = get_class($this);
    $this->modelName = $mdlName;

    if ($this->tableName === "") {
      $this->tableName = convert_to_tablename($mdlName);
    }

    $this->schema = $schema = Sabel_DB_Schema_Loader::getSchema($this);
    $this->schemaCols = $columns = $schema->getColumns();
    $this->columns = array_keys($columns);

    if (Sabel_DB_Transaction::isActive()) {
      Sabel_DB_Transaction::begin($this->connectionName);
    }
  }

  public function setConnectionName($connectionName)
  {
    $this->connectionName = $connectionName;

    if (Sabel_DB_Transaction::isActive()) {
      Sabel_DB_Transaction::begin($connectionName);
    }
  }

  public function getConnectionName()
  {
    return $this->connectionName;
  }

  public function __set($key, $val)
  {
    $this->values[$key] = $val;
    if ($this->selected) $this->updateValues[$key] = $val;
  }

  public function setValues($values)
  {
    foreach ($values as $key => $val) {
      $this->__set($key, $val);
    }
  }

  public function __get($key)
  {
    if (!isset($this->values[$key])) return null;

    $value = $this->values[$key];
    if ($value === null) return null;

    $columns = $this->schemaCols;
    if (!isset($columns[$key])) return $value;
    return $columns[$key]->cast($value);
  }

  public function setTableName($tblName)
  {
    $this->tableName = $tblName;
  }

  public function getTableName()
  {
    return $this->tableName;
  }

  public function getModelName()
  {
    return $this->modelName;
  }

  public function getColumnNames()
  {
    return $this->columns;
  }

  public function getSchema()
  {
    return $this->schema;
  }

  public function getPrimaryKey()
  {
    return $this->schema->getPrimaryKey();
  }

  public function getIncrementColumn()
  {
    return $this->schema->getIncrementColumn();
  }

  public function setSaveValues($values)
  {
    if (is_array($values)) {
      return $this->saveValues = $values;
    } else {
      $e = new Sabel_DB_Exception_Model();
      throw $e->missing("setSaveValues", $values);
    }
  }

  public function getSaveValues()
  {
    return $this->saveValues;
  }

  public function setUpdateValues($values)
  {
    if (is_array($values)) {
      return $this->updateValues = $values;
    } else {
      $e = new Sabel_DB_Exception_Model();
      throw $e->missing("setUpdateValues", $values);
    }
  }

  public function getUpdateValues()
  {
    return $this->updateValues;
  }

  public function toArray()
  {
    return $this->values;
  }

  public function isSelected()
  {
    return $this->selected;
  }

  public function setProperties($row)
  {
    $pkey = $this->schema->getPrimaryKey();
    if (is_string($pkey)) $pkey = (array)$pkey;

    $selected = true;

    foreach ($pkey as $key) {
      if (!isset($row[$key])) {
        $selected = false;
        break;
      }
    }

    $this->values   = $row;
    $this->selected = $selected;
  }

  // @todo
  public function validate($ignores = array())
  {
    $validator = new Sabel_DB_Validator($this);
    return $validator->validate($ignores);
  }
}
