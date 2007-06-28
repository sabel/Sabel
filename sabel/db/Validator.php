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

  // @todo i18n(beta3)
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
        $message = "don't set a value in '{$column->name}'(auto increment column).";
        $e = new Sabel_DB_Exception_Validate();
        throw $e->exception("validate", $message);
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

    if ($uniques = $model->getSchema()->getUniques()) {
      $this->unique($model, $columns, $uniques, $errors);
    }

    if ($customs = Sabel_DB_Validate_Config::getCustomValidators()) {
      $this->customs($customs, $columns, $errors);
    }

    return $this->errors = $errors;
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

  protected function customs($customs, $columns, &$errors)
  {
    if (isset($customs[$this->mdlName])) {
      $this->customValidation($customs[$this->mdlName], $columns, $errors);
    }

    if (($parent = get_parent_class($this->model)) !== "Sabel_DB_Model") {
      if (isset($customs[$parent])) {
        $this->customValidation($customs[$parent], $columns, $errors);
      }
    }

    if (isset($customs["all"])) {
      $this->customValidation($customs["all"], $columns, $errors);
    }
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

        if (empty($cols)) continue;

        foreach ($cols as $name) {
          $value = $schemas[$name]->value;
          $this->execCustomValidation($functions, $value, $name, $errors);
        }
      } elseif (isset($schemas[$colName])) {
        $value = $schemas[$colName]->value;
        $this->execCustomValidation($functions, $value, $colName, $errors);
      }
    }
  }

  protected function execCustomValidation($functions, $value, $name, &$errors)
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

  protected function unique($model, $columns, $uniques, &$errors)
  {
    $copy = MODEL($model->getModelName());
    $pkey = $model->getPrimaryKey();

    if (is_string($pkey)) $pkey = (array)$pkey;

    foreach ($uniques as $unique) {
      $values = array();
      foreach ($unique as $uni) {
        $val = $columns[$uni]->value;
        $copy->setCondition($uni, $val);
        $values[] = $val;
      }

      $result = $copy->selectOne();
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
        // @todo i18n

        if (count($unique) > 1) {
          $name = implode(", ", $unique);
        } else {
          $name = $unique[0];
        }

        $errors[] = sprintf($this->messages["unique"], $name, implode(", ", $values));
      }
    }
  }
}
