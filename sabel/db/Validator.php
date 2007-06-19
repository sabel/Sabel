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
    $localizedName = array(),
    $datetimeRegex = array();

  protected
    $errors  = array(),
    $ignores = array();

  public function __construct($model, $configs = null)
  {
    $this->model   = $model;
    $this->mdlName = $mdlName = $model->getModelName();

    if ($configs === null) {
      $configs = Sabel_DB_Validate_Config::getConfigs();
    }

    $this->messages      = $configs["messages"];
    $this->datetimeRegex = $configs["datetimeRegex"];

    if (isset($configs["localizedName"][$mdlName])) {
      $this->localizedName = $configs["localizedName"][$mdlName];
    }
  }

  public function getErrors()
  {
    return $this->errors;
  }

  public function hasError()
  {
    return !empty($this->errors);
  }

  // @todo localized(beta3) && refactoring
  public function validate($ignores = array())
  {
    $this->ignores = $ignores;

    $errors    = array();
    $messages  = $this->messages;
    $model     = $this->model;
    $columns   = $model->toSchema();
    $localized = $this->localizedName;

    foreach ($columns as $name => $column) {
      if (in_array($name, $ignores)) continue;

      if ($column->increment) {
        if ($column->value === null || $this->model->isSelected()) continue;
        $message = "don't set the value in the '{$column->name}'(auto increment column).";
        Sabel_DB_Exception_Validate::error("validate", $message);
      }

      $msgName = (isset($localized[$name])) ? $localized[$name] : $name;

      if (!$this->nullable($column)) {
        $errors[] = sprintf($this->messages["nullable"], $msgName);
        continue;
      }

      if (!$this->type($column)) {
        $errors[] = sprintf($this->messages["type"], $msgName);
        continue;
      }

      if ($column->isString()) {
        if (!$this->length($column)) {
          $errors[] = sprintf($this->messages["length"], $msgName);
          continue;
        }
      }

      if ($column->isNumeric()) {
        if (!$this->maximum($column)) {
          $errors[] = sprintf($this->messages["maximum"], $msgName);
          continue;
        }
      }
    }

    // @todo refactoring
    if ($uniques = $model->getSchema()->getUniques()) {
      $cloned = clone $model;
      foreach ($uniques as $unique) {
        if (count($unique) !== 1) continue;

        $name = $unique[0];
        if (in_array($name, $this->ignores)) continue;

        $value = $columns[$name]->value;
        if ($cloned->getCount($name, $value) > 0) {
          $msgName = (isset($localized[$name])) ? $localized[$name] : $name;
          $errors[] = sprintf($this->messages["unique"], $msgName, $value);
        }
      }
    }

    $customs = Sabel_DB_Validate_Config::getCustomValidations();
    if (isset($customs[$this->mdlName])) {
      $this->customValidation($customs[$this->mdlName], $columns, $errors);
    }

    $processes = Sabel_DB_Validate_Config::getPostProcesses();
    if ($processes) $this->postProcess($processes, $errors);

    return $this->errors = $errors;
  }

  protected function customValidation($validations, $schemas, &$errors)
  {
    foreach ($validations as $colName => $functions) {
      if (strpos($colName, "*") !== false) {
        $regex = str_replace("*", ".*", $colName);
        $cols  = array();
        foreach (array_keys($schemas) as $name) {
          if (preg_match("/{$regex}/", $name)) $cols[] = $name;
        }

        foreach ($cols as $name) {
          $value = $schemas[$name]->value;
          $this->execCustomValidation($functions, $value, $errors);
        }
      } elseif (isset($schemas[$colName])) {
        $value = $schemas[$colName]->value;
        $this->execCustomValidation($functions, $value, $errors);
      }
    }
  }

  protected function execCustomValidation($functions, $value, &$errors)
  {
    foreach ($functions as $function) {
      if (is_array($function)) {
        list ($function, $args) = $function;
        if (!is_array($args)) $args = (array)$args;

        $argString = $this->createEvalString($args);
        eval ('$result = $function($value, ' . $argString . ');');
      } else {
        $result = $function($value);
      }

      if ($result) $errors[] = $result;
    }
  }

  protected function createEvalString(&$arguments)
  {
    for ($i = 0; $i < count($arguments); $i++) {
      $args[] = '$args[' . $i . ']';
    }

    return str_replace(";", "", implode(",", $args));
  }

  protected function postProcess($processes, &$errors)
  {
    foreach ($processes as $process) {
      $class = new $process["class"]();

      foreach ($process["methods"] as $method) {
        $class->$method($errors, $this->model);
      }
    }
  }

  protected function nullable($column)
  {
    if ($column->nullable) return true;
    return isset($column->value);
  }

  protected function type($column)
  {
    $value = $column->value;
    if ($value === null) return true;

    if ($column->isNumeric()) {
      return is_numeric($value);
    } elseif ($column->isBool()) {
      return is_bool($value);
    } elseif ($column->isDatetime()) {
      return (preg_match($this->datetimeRegex, $value) === 1);
    } else {
      return true;
    }

    // @todo
    // if Sabel_DB_Type::TIME
    // if Sabel_DB_Type::DATE
  }

  protected function length($column)
  {
    $method = (extension_loaded("mbstring")) ? "mb_strlen" : "strlen";
    return ($method($column->value) < $column->max);
  }

  protected function maximum($column)
  {
    return ($column->value < $column->max);
  }
}
