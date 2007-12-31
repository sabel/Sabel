<?php

/**
 * Sabel_DB_Validator
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Validator extends Sabel_Object
{
  protected
    $model   = null,
    $mdlName = null;
    
  protected
    $messages      = array(),
    $displayNames  = array(),
    $datetimeRegex = "",
    $dateRegex     = "";
    
  protected
    $errors  = array(),
    $ignores = array();
    
  public function __construct(Sabel_DB_Abstract_Model $model)
  {
    $this->model   = $model;
    $this->mdlName = $model->getName();
    
    $configs = Sabel_DB_Validate_Config::getConfigs();
    
    $this->messages       = $configs["messages"];
    $this->datetimeRegex  = $configs["datetimeRegex"];
    $this->dateRegex      = $configs["dateRegex"];
    $this->localizedNames = Sabel_DB_Model_Localize::getColumnNames($this->mdlName);
  }
  
  public function getErrors()
  {
    return $this->errors;
  }
  
  public function hasError()
  {
    return !(empty($this->errors));
  }
  
  protected function getColumns()
  {
    $columns = array();
    $model   = $this->model;
    $schemas = $model->getColumns();
    
    if ($model->isSelected()) {
      $values = $model->getUpdateValues();
      foreach ($values as $name => $val) {
        if (isset($schemas[$name])) {
          $column = clone $schemas[$name];
          $column->value  = $val;
          $columns[$name] = $column;
        }
      }
    } else {
      $values = $model->toArray();
      foreach ($schemas as $name => $schema) {
        $column = clone $schema;
        $column->value  = (isset($values[$name])) ? $values[$name] : null;
        $columns[$name] = $column;
      }
    }
    
    return $columns;
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
      
      if ($column->increment) {
        if ($column->value === null || $this->model->isSelected()) continue;
        $message = "don't set a value in '{$column->name}'(sequence column).";
        throw new Sabel_DB_Exception($message);
      }
      
      $value = $column->value;
      
      if (!$this->nullable($column)) {
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
      
      if ($column->isNumeric() && $column->value !== null) {
        if (!$this->maximum($column)) {
          $message = $this->errorMessage($name, $value, "maximum");
          $this->errors[] = str_replace("%MAX%", $column->max, $message);
        } elseif (!$this->minimum($column)) {
          $message = $this->errorMessage($name, $value, "minimum");
          $this->errors[] = str_replace("%MIN%", $column->min, $message);
        }
      }
    }
    
    if ($uniques = $model->getSchema()->getUniques()) {
      $this->unique($model, $uniques);
    }
    
    if ($customs = Sabel_DB_Validate_Config::getValidators()) {
      $this->customs($customs, $columns);
    }
    
    return $this->errors;
  }
  
  protected function errorMessage($colName, $value, $msgKey)
  {
    $search  = array("%VALUE%", "%NAME%");
    $replace = array($value, $this->getLocalizedName($colName));
    return str_replace($search, $replace, $this->messages[$msgKey]);
  }
  
  protected function nullable($column)
  {
    if ($column->nullable) {
      return true;
    } else {
      return isset($column->value);
    }
  }
  
  protected function type($column)
  {
    $value = $column->value;
    if ($value === null) return true;
    
    /**
     *  don't care if value of integer column is too large.
     *  because the problem of the datatype occurs. (too large integer is float.)
     */
    if ($column->isInt(true) || $column->isSmallint()) {
      return ($value > INT_MAX || is_int($value));
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
  
  protected function customs($customs, $columns)
  {
    if (isset($customs[$this->mdlName])) {
      $this->customValidate($customs[$this->mdlName], $columns);
    }
    
    if (($parent = get_parent_class($this->model)) !== "Sabel_DB_Abstract_Model") {
      if (isset($customs[$parent])) {
        $this->customValidate($customs[$parent], $columns);
      }
    }
    
    if (isset($customs["all"])) {
      $this->customValidate($customs["all"], $columns);
    }
  }
  
  protected function customValidate($validations, $schemas)
  {
    foreach ($validations as $colName => $functions) {
      if (strpos($colName, "*") !== false) {
        $regex = str_replace("*", ".*", $colName);
        $cols  = array();
        foreach (array_keys($schemas) as $name) {
          if (preg_match("/{$regex}/", $name)) $cols[] = $name;
        }
        
        if (empty($cols)) continue;
        
        foreach ($cols as $name) {
          $value = $schemas[$name]->value;
          $this->execCustomValidation($functions, $name);
        }
      } elseif (isset($schemas[$colName])) {
        $value = $schemas[$colName]->value;
        $this->execCustomValidation($functions, $colName);
      }
    }
  }
  
  protected function execCustomValidation($functions, $name)
  {
    $lName = $this->getLocalizedName($name);
    
    foreach ($functions as $function) {
      $code = '$function($this->model, $name, $lName';
      
      if (is_array($function)) {
        list ($function, $args) = $function;
        if (!is_array($args)) $args = (array)$args;
        $code .= ", " . $this->createEvalString($args);
      }
      
      eval ('$result = ' . $code . ');');
      if ($result) $this->errors[] = $result;
    }
  }
  
  protected function getLocalizedName($colName)
  {
    $lNames = $this->localizedNames;
    return (isset($lNames[$colName])) ? $lNames[$colName] : $colName;
  }
  
  protected function createEvalString(&$arguments)
  {
    for ($i = 0; $i < count($arguments); $i++) {
      $args[] = '$args[' . $i . ']';
    }
    
    return implode(",", $args);
  }
  
  protected function unique($model, $uniques)
  {
    Sabel::using("Manipulator");
    if (class_exists("Manipulator", false)) {
      $manip = new Manipulator($model->getName());
    } else {
      $manip = new Sabel_DB_Manipulator($model->getName());
    }
    
    $lNames = array();
    $pkey = $model->getPrimaryKey();
    if (is_string($pkey)) $pkey = array($pkey);
    
    foreach ($uniques as $unique) {
      $values = array();
      foreach ($unique as $uni) {
        $lNames[] = $this->getLocalizedName($uni);
        $val = $model->$uni;
        $manip->setCondition($uni, $val);
        $values[] = $val;
      }
      
      $result = $manip->selectOne();
      if (!$result->isSelected()) continue;
      
      if ($model->isSelected()) {
        $duplicate = false;
        foreach ($pkey as $key) {
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
