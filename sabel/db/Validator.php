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
    $schemas = $model->getSchema()->getColumns();

    if ($model->isSelected()) {
      $values = $model->getUpdateValues();
      foreach ($values as $name => $val) {
        if (isset($schemas[$name])) {
          $column = clone $schemas[$name];
          $column->value = $column->cast($val);
          $columns[$name] = $column;
        }
      }
    } else {
      $values = $model->toArray();
      foreach ($schemas as $name => $schema) {
        $column = clone $schema;
        if (isset($values[$name])) {
          $column->value = $column->cast($values[$name]);
        } else {
          $column->value = null;
        }

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

      $lName = $this->getLocalizedName($name);

      if (!$this->nullable($column)) {
        $this->errors[] = sprintf($this->messages["nullable"], $lName);
        continue;
      }

      if (!$this->type($column)) {
        $this->errors[] = sprintf($this->messages["type"], $lName);
        continue;
      }

      if ($column->isString() && !$this->length($column)) {
        $this->errors[] = sprintf($this->messages["length"], $lName);
        continue;
      }

      if ($column->isNumeric() && $column->value !== null) {
        if (!$this->maximum($column)) {
          $this->errors[] = sprintf($this->messages["maximum"], $lName);
        } elseif (!$this->minimum($column)) {
          $this->errors[] = sprintf($this->messages["minimum"], $lName);
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

  protected function length($column)
  {
    static $method = "";

    if ($method === "") {
      $method = (extension_loaded("mbstring")) ? "mb_strlen" : "strlen";
    }

    return ($method($column->value) <= $column->max);
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

    return str_replace(";", "", implode(",", $args));
  }

  protected function unique($model, $uniques)
  {
    $manip = new Manipulator($model->getName());
    $pkey  = $model->getPrimaryKey();

    if (is_string($pkey)) $pkey = (array)$pkey;

    foreach ($uniques as $unique) {
      $values = array();
      foreach ($unique as $uni) {
        $val = $model->$uni;
        $manip->setCondition($uni, $val);
        $values[] = $val;
      }

      $result = $manip->selectOne();
      if (!$result->isSelected()) continue;

      if ($model->isSelected()) {
        $invalid = false;
        foreach ($pkey as $key) {
          if ($model->$key !== $result->$key) {
            $invalid = true;
            break;
          }
        }
      } else {
        $invalid = true;
      }

      if ($invalid) {
        $values = implode(", ", $values);
        $this->errors[] = sprintf($this->messages["unique"], $values);
      }
    }
  }
}
