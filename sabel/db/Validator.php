<?php

/**
 * Sabel_Db_Validator
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Validator extends Sabel_Object
{
  const OMITTED = "__OMITTED__";
  
  /**
   * @var string
   */
  protected $datetimeRegex = '/^[12]\d{3}-(0?[1-9]|1[0-2])-(0?[1-9]|[12]\d|3[01]) ((0?|1)[\d]|2[0-3]):(0?[\d]|[1-5][\d]):(0?[\d]|[1-5][\d])$/';
  
  /**
   * @var string
   */
  protected $dateRegex = '/^[12]\d{3}-(0?[1-9]|1[0-2])-(0?[1-9]|[12]\d|3[01])$/';
  
  protected $model = null;
  protected $mdlName = null;
  protected $isUpdate = false;
  protected $validateConfig = null;
  protected $messages = array();
  protected $displayNames = array();
  protected $errors = array();
  protected $ignores = array();
  
  public function __construct(Sabel_Db_Model $model)
  {
    $this->model    = $model;
    $this->mdlName  = $model->getName();
    $this->isUpdate = $model->isSelected();
    $this->messages = Sabel_Db_Validate_Config::getMessages();
    $this->localizedNames = Sabel_Db_Model_Localize::getColumnNames($this->mdlName);
  }
  
  public function setValidateConfig(Sabel_Db_Validate_Config $config)
  {
    $this->validateConfig = $config;
  }
  
  public function getErrors()
  {
    return $this->errors;
  }
  
  public function hasError()
  {
    return !empty($this->errors);
  }
  
  public function validate($ignores = array())
  {
    $this->ignores = $ignores;
    $this->errors  = array();
    
    $messages = $this->messages;
    $model    = $this->model;
    $columns  = $this->getColumns();
    
    foreach ($columns as $name => $column) {
      if (in_array($name, $ignores)) continue;
      
      $value = $column->value;
      if ($column->increment) {
        if ($value === self::OMITTED || $value === null || $this->isUpdate) continue;
        $message = __METHOD__ . "() don't set a value in '{$column->name}'(sequence column).";
        throw new Sabel_Db_Exception($message);
      }
      
      if ($this->nullable($column)) {
        if ($value === self::OMITTED) {
          $column->value = $value = null;
        }
      } else {
        $this->errors[] = $this->errorMessage($name, $value, "nullable");
        continue;
      }
      
      if (!$this->type($column)) {
        if ($column->isNumeric()) {
          $this->errors[] = $this->errorMessage($name, $value, "numeric");
        } else {
          $this->errors[] = $this->errorMessage($name, $value, "type");
        }
        continue;
      }
      
      if ($column->isString()) {
        if (!$this->length($column, "max")) {
          $message = $this->errorMessage($name, $value, "maxlength");
          $this->errors[] = str_replace("%MAX%", $column->max, $message);
          continue;
        } elseif (!$this->length($column, "min")) {
          $message = $this->errorMessage($name, $value, "minlength");
          $this->errors[] = str_replace("%MIN%", $column->min, $message);
          continue;
        }
      }
      
      if ($column->isNumeric() && $value !== null) {
        if (!$this->maximum($column)) {
          $message = $this->errorMessage($name, $value, "maximum");
          $this->errors[] = str_replace("%MAX%", $column->max, $message);
        } elseif (!$this->minimum($column)) {
          $message = $this->errorMessage($name, $value, "minimum");
          $this->errors[] = str_replace("%MIN%", $column->min, $message);
        }
      }
    }
    
    if ($uniques = $model->getMetadata()->getUniques()) {
      $this->unique($model, $uniques);
    }
    
    Sabel::using("Db_Validate_Config");
    if ($this->validateConfig === null && !class_exists("Db_Validate_Config", false)) {
      return $this->errors;
    }
  
    if ($this->validateConfig !== null) {
      $config = $this->validateConfig;
    } else {
      $config = new Db_Validate_Config();
    }
    
    $config->configure();
    $this->doCustomValidate($config, $columns);
    
    return $this->errors;
  }
  
  protected function getColumns()
  {
    $columns = array();
    $model   = $this->model;
    $schemas = $model->getColumns();
    
    if ($this->isUpdate) {
      $values = $model->getUpdateValues();
      foreach ($values as $name => $val) {
        if (isset($schemas[$name])) {
          $column = clone $schemas[$name];
          $column->setValue($val);
          $columns[$name] = $column;
        }
      }
    } else {
      $values = $model->toArray();
      foreach ($schemas as $name => $schema) {
        $column = clone $schema;
        if (isset($values[$name])) {
          $column->setValue($values[$name]);
        } else {
          $column->value = self::OMITTED;
        }
        
        $columns[$name] = $column;
      }
    }
    
    return $columns;
  }
  
  protected function getLocalizedName($colName)
  {
    $lNames = $this->localizedNames;
    return (isset($lNames[$colName])) ? $lNames[$colName] : $colName;
  }
  
  protected function errorMessage($colName, $value, $msgKey)
  {
    $search  = array("%VALUE%", "%NAME%");
    $replace = array($value, $this->getLocalizedName($colName));
    return str_replace($search, $replace, $this->messages[$msgKey]);
  }
  
  protected function nullable($column)
  {
    if ($column->nullable) return true;
    
    if (!$this->isUpdate && $column->value === self::OMITTED) {
      return ($column->default !== null);
    } else {
      return ($column->value !== null);
    }
  }
  
  protected function type($column)
  {
    if (($value = $column->value) === null) {
      return true;
    }
    
    /**
     *  don't care if value of integer column is too large.
     *  because the problem of the datatype occurs. (too large integer is float.)
     */
    if ($column->isInt(true) || $column->isSmallint()) {
      return ($value > PHP_INT_MAX || is_int($value));
    } elseif ($column->isBigint()) {
      return (is_numeric($value) && $value{0} !== "0");
    } elseif ($column->isBool()) {
      return is_bool($value);
    } elseif ($column->isFloat(false)) {
      return is_float($value);
    } elseif ($column->isDatetime()) {
      return (preg_match($this->datetimeRegex, $value) === 1);
    } elseif ($column->isDate()) {
      return (preg_match($this->dateRegex, $value) === 1);
    } else {
      return true;
    }
  }
  
  protected function length($column, $max_or_min)
  {
    static $func = "";
    
    if ($func === "") {
      $func = (extension_loaded("mbstring")) ? "mb_strlen" : "strlen";
    }
    
    if ($max_or_min === "max") {
      return ($func($column->value) <= $column->max);
    } elseif ($column->value === null) {
      return true;
    } else {
      return ($func($column->value) >= $column->min);
    }
  }
  
  protected function maximum($column)
  {
    return ($column->value <= $column->max);
  }
  
  protected function minimum($column)
  {
    return ($column->value >= $column->min);
  }
  
  protected function doCustomValidate(Sabel_Db_Validate_Config $config, $columns, $mdlName = null)
  {
    if ($mdlName === null) $mdlName = $this->mdlName;
    
    if ($config->has($mdlName)) {
      $columnConfigs = $config->get($mdlName)->getColumns();
      
      foreach ($columnConfigs as $key => $columnConfig) {
        if (strpos($key, "*") !== false) {
          $regex = "^" . str_replace("*", ".*", $key);
          foreach (array_keys($columns) as $colName) {
            if (preg_match("/{$regex}/", $colName)) {
              foreach ($columnConfig->getValidators() as $validator) {
                $this->_doCustomValidate($config, $colName, $validator);
              }
            }
          }
        } elseif (isset($columns[$key])) {
          foreach ($columnConfig->getValidators() as $validator) {
            $this->_doCustomValidate($config, $key, $validator);
          }
        }
      }
    }
    
    $parent = get_parent_class($mdlName);
    if ($parent !== false && $parent !== "Sabel_Db_Model") {
      $this->doCustomValidate($config, $columns, $parent);
    }
  }
  
  protected function _doCustomValidate($config, $colName, $validator)
  {
    $lName  = $this->getLocalizedName($colName);
    $params = array_merge(array($this->model, $colName, $lName), $validator->arguments);
    $errMsg = call_user_func_array(array($config, $validator->name), $params);
    if ($errMsg !== null) $this->errors[] = $errMsg;
  }
  
  protected function unique($model, $uniques)
  {
    $lNames = array();
    
    foreach ($uniques as $unique) {
      $values = array();
      foreach ($unique as $uni) {
        $lNames[] = $this->getLocalizedName($uni);
        if (($val = $model->$uni) === null) break 2;
        $model->setCondition($uni, $val);
        $values[] = $val;
      }
      
      $result = $model->selectOne();
      if (!$result->isSelected()) continue;
      
      if ($model->isSelected()) {
        $duplicate = false;
        $pkey = $model->getMetadata()->getPrimaryKey();
        
        foreach ((is_string($pkey)) ? array($pkey) : $pkey as $key) {
          if ($model->$key !== $result->$key) {
            $duplicate = true;
            break;
          }
        }
      } else {
        $duplicate = true;
      }
      
      if ($duplicate) {
        $message = str_replace("%VALUE%", implode(", ", $values), $this->messages["unique"]);
        $message = str_replace("%NAME%", implode(", ", $lNames), $message);
        $this->errors[] = $message;
      }
    }
  }
}
