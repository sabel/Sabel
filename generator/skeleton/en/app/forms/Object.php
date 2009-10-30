<?php

class Forms_Object extends Sabel_ValueObject
{
  /**
   * @var string
   */
  protected $modelName = "";
  
  /**
   * @var string
   */
  protected $nameSpace = "";
  
  /**
   * @var array
   */
  protected $displayNames = array();
  
  /**
   * @var array
   */
  protected $inputNames = array();
  
  /**
   * @var array
   */
  protected $validators = array();
  
  /**
   * @var array
   */
  protected $errors = array();
  
  public function __construct($nameSpace = null)
  {
    $this->nameSpace = $nameSpace;
  }
  
  /**
   * @param string $nameSpace
   *
   * @return self
   */
  public function setNameSpace($nameSpace)
  {
    $this->nameSpace = $nameSpace;
    
    return $this;
  }
  
  public function getNameSpace()
  {
    return $this->nameSpace;
  }
  
  /**
   * @param array $names
   *
   * @return self
   */
  public function setDisplayNames(array $displayNames)
  {
    $this->displayNames = $displayNames;
    
    return $this;
  }
  
  /**
   * @param string $inputName
   *
   * @return string
   */
  public function getDisplayName($inputName)
  {
    if (isset($this->displayNames[$inputName])) {
      return $this->displayNames[$inputName];
    } else {
      return $inputName;
    }
  }
  
  public function n($inputName)
  {
    return $this->getDisplayName($inputName);
  }
  
  /**
   * @param array $inputNames
   *
   * return self
   */
  public function setInputNames(array $inputNames)
  {
    $this->inputNames = $inputNames;
    
    return $this;
  }
  
  /**
   * @return array
   */
  public function getInputNames()
  {
    return $this->inputNames;
  }
  
  /**
   * @param array $errors
   *
   * @return void
   */
  public function setErrors($errors)
  {
    $this->errors = $errors;
  }
  
  /**
   * @return array
   */
  public function getErrors()
  {
    return $this->errors;
  }
  
  /**
   * @return boolean
   */
  public function hasError()
  {
    return !empty($this->errors);
  }
  
  public function text($name, $attrs = "")
  {
    return $this->getHtmlWriter($name, $this->createInputName($name), $attrs)->text();
  }
  
  public function password($name, $attrs = "")
  {
    return $this->getHtmlWriter($name, $this->createInputName($name), $attrs)->password();
  }
  
  public function textarea($name, $attrs = "")
  {
    return $this->getHtmlWriter($name, $this->createInputName($name), $attrs)->textarea();
  }
  
  public function hidden($name, $attrs = "")
  {
    return $this->getHtmlWriter($name, $this->createInputName($name), $attrs)->hidden();
  }
  
  public function select($name, $values, $attrs = "")
  {
    return $this->getHtmlWriter($name, $this->createInputName($name), $attrs)->select($values);
  }
  
  public function radio($name, $values, $attrs = "")
  {
    return $this->getHtmlWriter($name, $this->createInputName($name), $attrs)->radio($values);
  }
  
  public function checkbox($name, $values, $attrs = "")
  {
    return $this->getHtmlWriter($name, $this->createInputName($name), $attrs)->checkbox($values);
  }
  
  public function datetime($name, $yearRange = null, $withSecond = false, $includeBlank = false)
  {
    $writer = $this->getHtmlWriter($name, $this->createInputName("_datetime") . "[{$name}]");
    return $writer->datetime($yearRange, $withSecond, $includeBlank);
  }
  
  public function date($name, $yearRange = null, $includeBlank = false)
  {
    $writer = $this->getHtmlWriter($name, $this->createInputName("_date") . "[{$name}]");
    return $writer->date($yearRange, $includeBlank);
  }
  
  public function apply(array $values, array $inputNames = array())
  {
    if (empty($values)) {
      return $this;
    }
    
    if (empty($inputNames)) {
      $inputNames = $this->inputNames;
    } else {
      $this->inputNames = $inputNames;
    }
    
    foreach ($values as $inputName => $value) {
      if ($inputName === "_datetime" || $inputName === "_date") {
        list ($k, ) = each($value);
        if (!in_array($k, $inputNames, true)) {
          continue;
        } elseif ($inputName === "_datetime") {
          foreach ($value as $key => $date) {
            if (!isset($date["s"])) {
              $date["s"] = "00";
            }
            
            if ($this->isValidDateValue($date, true)) {
              $this->set(
                $key,
                $date["y"] . "-" .
                $date["m"] . "-" .
                $date["d"] . " " .
                $date["h"] . ":" .
                $date["i"] . ":" .
                $date["s"]
              );
            } else {
              $this->set($key, null);
            }
          }
        } elseif ($inputName === "_date") {
          foreach ($value as $key => $date) {
            if ($this->isValidDateValue($date)) {
              $this->set($key, "{$date['y']}-{$date['m']}-{$date['d']}");
            } else {
              $this->set($key, null);
            }
          }
        }
      } elseif (!in_array($inputName, $inputNames, true)) {
        continue;
      } else {
        $this->set($inputName, $value);
      }
    }
    
    return $this;
  }
  
  /**
   * @param array $ignores
   *
   * @return boolean
   */
  public function validate($ignores = array())
  {
    if (is_string($ignores)) {
      $ignores = array($ignores);
    }
    
    $validator = new Validator();
    
    if (!is_empty($this->modelName)) {
      $this->setUpModelValidator($validator);
    }
    
    $validators = $this->validators;
    foreach ($this->inputNames as $inputName) {
      if (!isset($validators[$inputName])) continue;
      
      $v = $validators[$inputName];
      if (is_array($v)) {
        foreach ($v as $_v) {
          $validator->add($inputName, $_v);
        }
      } else {
        $validator->add($name, $v);
      }
    }
    
    $validator->register($this);
    $validator->setDisplayNames($this->displayNames);
    
    $result = $validator->validate($this->values);
    $this->errors = $validator->getErrors();
    
    return $result;
  }
  
  protected function isValidDateValue($values, $isDatetime = false)
  {
    $keys = array("y", "m", "d");
    
    if ($isDatetime) {
      $keys = array_merge($keys, array("h", "i", "s"));
    }
    
    foreach ($keys as $key) {
      if (!isset($values[$key]) || $values[$key] === "") {
        return false;
      }
    }
    
    return true;
  }
  
  protected function getHtmlWriter($name, $inputName, $attrs = "")
  {
    static $htmlWriter = null;
    
    if ($htmlWriter === null) {
      $htmlWriter = new Forms_Lib_Html();
    } else {
      $htmlWriter->clear();
    }
    
    $value = $this->get($name);
    
    if (is_string($value)) {
      $value = htmlescape($value);
    }
    
    return $htmlWriter->setName($inputName)->setValue($value)->setAttributes($attrs);
  }
  
  protected function createInputName($inputName)
  {
    if (is_empty($this->nameSpace)) {
      return $inputName;
    } else {
      return $this->nameSpace . "[{$inputName}]";
    }
  }
  
  protected function setUpModelValidator(Sabel_Validator $validator)
  {
    $columns = MODEL($this->modelName)->getMetadata()->getColumns();
    
    $validators = $this->validators;
    foreach ($this->inputNames as $inputName) {
      if (!isset($columns[$inputName])) continue;
      
      $column = $columns[$inputName];
      if ($column->increment) continue;
      
      if (!$column->nullable) {
        $validator->add($column->name, "required");
      }
      
      if ($column->isString()) {
        $validator->add($column->name, "strwidth({$column->max})");
      } elseif ($column->isNumeric()) {
        $validator->add($column->name, "max({$column->max})");
        $validator->add($column->name, "min({$column->min})");
        
        if ($column->isInt()) {
          $validator->add($column->name, "integer");
        } else {  // float, double
          $validator->add($column->name, "numeric");
        }
      } elseif ($column->isBoolean()) {
        $validator->add($column->name, "boolean");
      } elseif ($column->isDate()) {
        $validator->add($column->name, "date");
      } elseif ($column->isDatetime()) {
        $validator->add($column->name, "datetime");
      }
    }
  }
}
