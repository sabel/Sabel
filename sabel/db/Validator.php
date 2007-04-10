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

  public function __construct($model)
  {
    $this->model   = $model;
    $this->mdlName = $mdlName = convert_to_modelname($model->getTableName());

    $configs = Sabel_DB_Validate_Config::getConfigs();

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
    return !(empty($this->errors));
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
      if ($schema->type === Sabel_DB_Type::STRING) {
        if (!$this->length($name, $schema)) {
          $errors[] = sprintf($this->messages["length"], $msgName);
          continue;
        }
      }
      if ($schema->type === Sabel_DB_Type::INT   ||
          $schema->type === Sabel_DB_Type::FLOAT ||
          $schema->type === Sabel_DB_Type::DOUBLE) {
        if (!$this->maximum($name, $schema)) {
          $errors[] = sprintf($this->messages["maximum"], $msgName);
          continue;
        }
      }
    }

    $customs = Sabel_DB_Validate_Config::getCustomValidations();
    if ($customs) $this->customValidation($customs, $schemas, $errors);

    $processes = Sabel_DB_Validate_Config::getPostProcesses();
    if ($processes) $this->postProcess($processes, $errors);

    $this->errors = $errors;
    return (empty($errors)) ? null : $errors;
  }

  protected function customValidation($customs, $schemas, &$errors)
  {
    foreach ($customs as $custom) {
      $func  = $custom["function"];
      $name  = $custom["column"];
      $value = $schemas[$name]->value;

      if (isset($custom["arguments"])) {
        $args = array();
        for ($i = 0; $i < count($custom["arguments"]); $i++) {
          $args[] = '$custom["arguments"][' . $i . ']';
        }

        $args = str_replace(";", "", implode(",", $args));
        eval ('$result = $func($value, ' . $args . ');');
      } else {
        $result = $func($value);
      }

      if ($result) $errors[] = $result;
    }
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
