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
class Sabel_DB_Validator
{
  protected
    $model   = null,
    $mdlName = null;

  protected
    $messages      = array(),
    $displayNames  = array(),
    $datetimeRegex = array();

  protected
    $errors  = array(),
    $ignores = array();

  public function __construct(Sabel_DB_Model $model)
  {
    $this->model         = $model;
    $this->mdlName       = $model->getModelName();
    $this->messages      = Sabel_DB_Validate_Config::getMessages();
    $this->datetimeRegex = Sabel_DB_Validate_Config::getDatetimeRegex();
    $this->displayNames  = Sabel_DB_Model_Localize::getColumnNames($this->mdlName);
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

    $messages  = $this->messages;
    $model     = $this->model;
    $columns   = $this->getColumns();
    $localized = $this->displayNames;

    foreach ($columns as $name => $column) {
      if (in_array($name, $ignores)) continue;

      if ($column->increment) {
        if ($column->value === null || $this->model->isSelected()) continue;
        $message = "don't set a value in '{$column->name}'(sequence column).";
        throw new Sabel_DB_Exception($message);
      }

      $msgName = (isset($localized[$name])) ? $localized[$name] : $name;

      if (!$this->nullable($column)) {
        $this->errors[] = sprintf($this->messages["nullable"], $msgName);
        continue;
      }

      if (!$this->type($column)) {
        $this->errors[] = sprintf($this->messages["type"], $msgName);
        continue;
      }

      if ($column->isString() && !$this->length($column)) {
        $this->errors[] = sprintf($this->messages["length"], $msgName);
        continue;
      }

      if ($column->isNumeric() && !$this->maximum($column)) {
        $this->errors[] = sprintf($this->messages["maximum"], $msgName);
        continue;
      }
    }

    if ($uniques = $model->getSchema()->getUniques()) {
      $this->unique($model, $uniques);
    }

    if ($customs = Sabel_DB_Validate_Config::getCustomValidators()) {
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

    return ($method($column->value) < $column->max);
  }

  protected function maximum($column)
  {
    return ($column->value < $column->max);
  }

  protected function customs($customs, $columns)
  {
    if (isset($customs[$this->mdlName])) {
      $this->customValidate($customs[$this->mdlName], $columns);
    }

    if (($parent = get_parent_class($this->model)) !== "Sabel_DB_Model") {
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
          $this->execCustomValidation($functions, $value, $name);
        }
      } elseif (isset($schemas[$colName])) {
        $value = $schemas[$colName]->value;
        $this->execCustomValidation($functions, $value, $colName);
      }
    }
  }

  protected function execCustomValidation($functions, $value, $name)
  {
    foreach ($functions as $function) {
      if (is_array($function)) {
        list ($function, $args) = $function;
        if (!is_array($args)) $args = (array)$args;

        $argString = $this->createEvalString($args);
        eval ('$result = $function($value, $name, ' . $argString . ');');
      } else {
        $result = $function($value, $name);
      }

      if ($result) $this->errors[] = $result;
    }
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
    $executer = new Sabel_DB_Model_Executer($model->getModelName());
    $pkey = $model->getPrimaryKey();

    if (is_string($pkey)) $pkey = (array)$pkey;

    foreach ($uniques as $unique) {
      $values = array();
      foreach ($unique as $uni) {
        $val = $model->$uni;
        $executer->setCondition($uni, $val);
        $values[] = $val;
      }

      $result = $executer->selectOne();
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
