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
    $errors = array();

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

  public function validate($ignores = array())
  {
    $errors   = array();
    $messages = $this->messages;
    $schemas  = $this->model->toSchema();
    $names    = $this->localizedName;

    foreach ($schemas as $name => $schema) {
      if (in_array($name, $ignores)) continue;
      $msgName = (isset($names[$name])) ? $names[$name] : $name;

      if (!$this->nullable($name, $schema)) {
        $errors[] = sprintf($this->messages["nullable"], $msgName);
        continue;
      }

      if (!$this->type($name, $schema)) {
        $errors[] = sprintf($this->messages["type"], $msgName);
        continue;
      }

      if ($schema->isString()) {
        if (!$this->length($name, $schema)) {
          $errors[] = sprintf($this->messages["length"], $msgName);
          continue;
        }
      }

      if ($schema->isInt() || $schema->isFloat() || $schema->isDouble()) {
        if (!$this->maximum($name, $schema)) {
          $errors[] = sprintf($this->messages["maximum"], $msgName);
          continue;
        }
      }
    }

    $customs = Sabel_DB_Validate_Config::getCustomValidations();
    if (isset($customs[$this->mdlName])) {
      $this->customValidation($customs[$this->mdlName], $schemas, $errors);
    }

    $processes = Sabel_DB_Validate_Config::getPostProcesses();
    if ($processes) $this->postProcess($processes, $errors);

    $this->errors = $errors;
    return (empty($errors)) ? null : $errors;
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

  protected function nullable($name, $schema)
  {
    if ($schema->nullable) return true;
    return isset($schema->value);
  }

  protected function type($name, $schema)
  {
    $value = $schema->value;
    if ($value === null) return true;

    switch ($schema->type) {
      case Sabel_DB_Type::INT:
      case Sabel_DB_Type::FLOAT:
      case Sabel_DB_Type::DOUBLE:
        return is_numeric($value);

      case Sabel_DB_Type::BOOL:
        return is_bool($value);

      case Sabel_DB_Type::DATETIME:
        return (preg_match($this->datetimeRegex, $value) === 1);

      case Sabel_DB_Type::TIME:
      case Sabel_DB_Type::DATE:

      default:
        return true;
    }
  }

  protected function length($name, $schema)
  {
    $method = (extension_loaded("mbstring")) ? "mb_strlen" : "strlen";
    return ($method($schema->value) < $schema->max);
  }

  protected function maximum($name, $schema)
  {
    return ($schema->value < $schema->max);
  }
}
