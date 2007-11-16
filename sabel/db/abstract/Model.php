<?php

/**
 * Sabel_DB_Abstract_Model
 *
 * @abstract
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_DB_Abstract_Model extends Sabel_Object
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
    $updateValues = array();

  public function __construct()
  {
    $this->initialize();
  }

  protected function initialize($mdlName = null)
  {
    if ($mdlName === null) {
      $mdlName = get_class($this);
    }

    $this->modelName = $mdlName;

    if ($this->tableName === "") {
      $this->tableName = convert_to_tablename($mdlName);
    }

    $this->schema = Sabel_DB_Schema::create($this->tableName, $this->connectionName);
    $this->schemaCols = $this->schema->getColumns();
    $this->columns = array_keys($this->schemaCols);
  }

  public function setConnectionName($connectionName)
  {
    $this->connectionName = $connectionName;
  }

  public function getConnectionName()
  {
    return $this->connectionName;
  }

  public function __set($key, $val)
  {
    if (isset($this->schemaCols[$key])) {
      $val = $this->schemaCols[$key]->cast($val);
    }

    $this->values[$key] = $val;
    if ($this->selected) $this->updateValues[$key] = $val;
  }

  public function unsetValue($key)
  {
    unset($this->values[$key]);
    unset($this->updateValues[$key]);
  }

  public function setValues(array $values)
  {
    foreach ($values as $key => $val) {
      $this->__set($key, $val);
    }
  }

  public function __get($key)
  {
    if (isset($this->values[$key])) {
      return $this->values[$key];
    } else {
      return null;
    }
  }

  public function setTableName($tblName)
  {
    $this->tableName = $tblName;
  }

  public function getTableName()
  {
    return $this->tableName;
  }

  public function getName()
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

  public function getColumns()
  {
    return $this->schemaCols;
  }

  public function getPrimaryKey()
  {
    return $this->schema->getPrimaryKey();
  }

  public function getSequenceColumn()
  {
    return $this->schema->getSequenceColumn();
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

  public function setAttributes($attributes)
  {
    $pkey = $this->schema->getPrimaryKey();
    if (is_string($pkey)) $pkey = (array)$pkey;

    if (empty($pkey)) {
      $selected = false;
    } else {
      $selected = true;
      foreach ($pkey as $key) {
        if (!isset($attributes[$key])) {
          $selected = false;
          break;
        }
      }
    }

    $columns = $this->schemaCols;
    foreach ($attributes as $key => &$val) {
      if (isset($columns[$key])) {
        $val = $columns[$key]->cast($val);
      }
    }

    $this->values   = $attributes;
    $this->selected = $selected;

    return $this;
  }
}
