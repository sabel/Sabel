<?php

/**
 * Processor_Form_Object
 *
 * @category  Processor
 * @package   lib.processor
 * @author    Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright 2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Form_Object extends Sabel_Object
{
  protected $model   = null;
  protected $mdlName = "";
  protected $columns = null;
  protected $errors  = array();
  
  public function __construct($model)
  {
    if (is_string($model)) {
      $model = MODEL($model);
    }
    
    $this->model   = $model;
    $this->mdlName = $model->getName();
    $this->columns = $model->getSchema()->getColumns();
  }
  
  public function getModel()
  {
    return $this->model;
  }
  
  public function set($key, $val)
  {
    $this->model->__set($key, $val);
  }
  
  public function get($key)
  {
    return $this->model->__get($key);
  }
  
  public function setErrors($errors)
  {
    $this->errors = $errors;
  }
  
  public function getErrors()
  {
    return $this->errors;
  }
  
  public function hasError()
  {
    return !empty($this->errors);
  }
  
  public function unsetErrors()
  {
    $errors = $this->errors;
    $this->errors = array();
    
    return $errors;
  }
  
  public function validate($ignores = array())
  {
    $model = $this->model;
    $validator = new Sabel_DB_Validator($model);
    $annot = $model->getReflection()->getAnnotation("validate_ignores");
    
    if ($annot !== null) {
      $ignores = array_merge($annot[0], $ignores);
    }
    
    if ($errors = $validator->validate($ignores)) {
      $this->errors = $errors;
      return false;
    } else {
      return true;
    }
  }
  
  public function mark($colName, $mark = "※", $tag = "em")
  {
    static $names = array();
    $mdlName = $this->mdlName;
    
    if (empty($names[$mdlName])) {
      $names[$mdlName] = Sabel_DB_Model_Localize::getColumnNames($mdlName);
    }
    
    $name = (isset($names[$mdlName][$colName])) ? $names[$mdlName][$colName] : $colName;
    
    if (isset($this->columns[$colName]) && !$this->columns[$colName]->nullable) {
      $name .= " <{$tag}>{$mark}</{$tag}>";
    }
    
    return $name;
  }
  
  public function create($uri, $id = null, $class = null, $submitText = "")
  {
    $html    = array();
    $columns = $this->columns;
    $schema  = $this->model->getSchema();
    $mdlName = $this->mdlName;
    $names   = Sabel_DB_Model_Localize::getColumnNames($mdlName);
    
    foreach ($columns as $colName => $column) {
      if ($column->increment || $column->primary) continue;
      
      if ($schema->isForeignKey($colName)) {
        $html[] = $this->hidden($colName);
        continue;
      }
      
      if (isset($names[$colName])) {
        $html[] = $names[$colName];
      } else {
        $html[] = $colName;
      }
      
      if ($column->isBool()) {
        $html[] = $this->checkbox($colName);
      } elseif ($column->isText()) {
        $html[] = $this->textarea($colName);
      } elseif ($column->isDatetime()) {
        $html[] = $this->datetime($colName);
      } elseif ($column->isDate()) {
        $html[] = $this->date($colName);
      } else {
        $html[] = $this->text($colName);
      }
    }
    
    $html[] = "<br/>" . $this->submit($submitText);
    $html[] = $this->end();
    $start  = $this->start($uri, $id, $class, "POST");
    
    return $start . "\n" . implode("<br/>\n", $html) . "\n";
  }
  
  public function start($uri, $class = null, $id = null, $method = "post", $name = null)
  {
    $html = '<form action="' . uri($uri) . '" method="' . $method . '" ';
    $this->addIdAndClass($html, $id, $class);
    if ($name !== null) $html .= 'name="' . $name . '" ';
    
    return $html . ">\n<fieldset class=\"formField\">\n";
  }
  
  public function end()
  {
    return "</fieldset>\n</form>\n";
  }
  
  public function text($name, $class = null, $id = null)
  {
    $value = $this->getValue($name);
    $name  = $this->createName($name);
    $html  = '<input type="text" ';
    $this->addIdAndClass($html, $id, $class);
    $html .= 'name="' . $name . '" value="' . $value . '" />';
    
    return $html;
  }
  
  public function password($name, $class = null, $id = null)
  {
    $value = $this->getValue($name);
    $name  = $this->createName($name);
    $html  = '<input type="password" ';
    $this->addIdAndClass($html, $id, $class);
    $html .= 'name="' . $name . '" value="' . $value . '" />';
    
    return $html;
  }
  
  public function textarea($name, $class = null, $id = null)
  {
    $value = $this->getValue($name);
    $name  = $this->createName($name);
    $html  = '<textarea ';
    $this->addIdAndClass($html, $id, $class);
    $html .= 'name="' . $name . '">' . $value . '</textarea>';
    
    return $html;
  }
  
  public function checkbox($name, $class = null, $id = null)
  {
    $value = $this->getValue($name);
    $name  = $this->createName($name);
    $html  = '<input type="checkbox" ';
    $this->addIdAndClass($html, $id, $class);
    $html .= 'name="' . $name . '" value="1"';
    
    if ($value === true) $html .= ' checked="checked"';
    
    return $html . ' />';
  }
  
  public function select($name, $values, $class = null, $id = null, $useKey = true)
  {
    $value  = $this->getValue($name);
    $name   = $this->createName($name);
    $select = new Processor_Form_Select();
    
    $contents = $select->getContents($values, $value, $useKey);
    $html = '<select name="' . $name . '" ';
    $this->addIdAndClass($html, $id, $class);
    return $html . ">" . $contents . "\n</select>";
  }
  
  public function datetime($name, $yearRange = null,
                           $withSecond = false, $defaultNull = false)
  {
    $value = $this->getValue($name);
    $name  = $this->createName("datetime") . "[{$name}]";
    $dtime = new Processor_Form_Datetime($name, $value);
    return $dtime->datetime($yearRange, $withSecond, $defaultNull);
  }
  
  public function date($name, $yearRange = null, $defaultNull = false)
  {
    $value = $this->getValue($name);
    $name  = $this->createName("date") . "[{$name}]";
    $dtime = new Processor_Form_Datetime($name, $value);
    return $dtime->date($yearRange, $defaultNull);
  }
  
  public function radio($name, $values, $class = null, $id = null)
  {
    $value = $this->getValue($name);
    if ($this->columns[$name]->isBool()) {
      $value = ($value) ? 1 : 0;
    }
    
    $name   = $this->createName($name);
    $radios = array();
    $count  = 0;
    
    foreach ($values as $v => $text) {
      $radio = '<input type="radio" ';
      $this->addIdAndClass($radio, $id, $class);
      $radio .= 'name="' . $name . '" value="' . $v . '"';
      if ($count === 0 && $value === null || $v === $value) {
        $radio .= ' checked="checked"';
      }

      $radios[] = $radio . " />{$text}\n";
      $count++;
    }
    
    return implode("&nbsp;", $radios);
  }
  
  public function hidden($name, $class = null, $id = null)
  {
    $value = $this->getValue($name);
    $name  = $this->createName($name);
    $html  = '<input type="hidden" ';
    $this->addIdAndClass($html, $id, $class);
    $html .= 'name="' . $name . '" value="' . $value . '" />';
    
    return $html;
  }
  
  public function submit($value = "", $class = null, $id = null)
  {
    $html = '<input type="submit" ';
    $this->addIdAndClass($html, $id, $class);
    if ($value !== "") $value = 'value="' . $value. '" ';
    $html .= $value . '/>';
    
    return $html;
  }
  
  protected function createName($name)
  {
    return $this->mdlName . "::" . $name;
  }
  
  protected function getValue($name)
  {
    $value = $this->model->$name;
    return (is_string($value)) ? htmlspecialchars($value) : $value;
  }
  
  protected function addIdAndClass(&$html, $id, $class)
  {
    if ($id !== null)    $html .= 'id="' . $id . '" ';
    if ($class !== null) $html .= 'class="' . $class . '" ';
  }
}

class Processor_Form_Select
{
  public function getContents($values, $selectedValue = null, $useKey = true)
  {
    $html = array();
    foreach ($values as $key => $value) {
      $k = ($useKey) ? $key : $value;
      if ($selectedValue === $k) {
        $html[] = '<option value="' . $k . '" selected="selected">';
      } else {
        $html[] = '<option value="' . $k . '">';
      }
      $html[] = $value . '</option>';
    }
    
    return implode("\n", $html);
  }
}

class Processor_Form_Datetime
{
  protected
    $name      = "",
    $timestamp = null;
  
  public function __construct($name, $datetime = null)
  {
    $this->name = $name;
    
    if ($datetime !== null) {
      $this->timestamp = strtotime($datetime);
    }
  }
  
  public function datetime($yearRange = null, $withSecond = false, $defaultNull = false)
  {
    $name = $this->name;
    list ($first, $last) = $this->getYearRange($yearRange);
    
    $html   = array();
    $html[] = $this->numSelect("year",   $name, $first, $last, $defaultNull);
    $html[] = $this->numSelect("month",  $name, 1, 12, $defaultNull);
    $html[] = $this->numSelect("day",    $name, 1, 31, $defaultNull);
    $html[] = $this->numSelect("hour",   $name, 0, 23, $defaultNull);
    $html[] = $this->numSelect("minute", $name, 0, 59, $defaultNull);
    
    if ($withSecond) {
      $html[] = $this->numSelect("second", $name, 0, 59);
    }
    
    return implode("&nbsp;", $html);
  }
  
  public function date($yearRange = null, $defaultNull = false)
  {
    $name = $this->name;
    list ($first, $last) = $this->getYearRange($yearRange);
    
    $html   = array();
    $html[] = $this->numSelect("year",  $name, $first, $last, $defaultNull);
    $html[] = $this->numSelect("month", $name, 1, 12, $defaultNull);
    $html[] = $this->numSelect("day",   $name, 1, 31, $defaultNull);
    
    return implode("&nbsp;", $html);
  }
  
  protected function numSelect($type, $name, $start, $end, $defaultNull)
  {
    $html = array('<select name="' . $name . '[' . $type . ']">');
    
    if ($defaultNull) {
      $html[] = '<option></option>';
    }
    
    $val  = (int)$this->selectedValue($type);
    
    for ($i = $start; $i <= $end; $i++) {
      if ($i === $val) {
        $html[] = '<option value="' . $i . '" selected="selected">' . $i . '</option>';
      } else {
        $html[] = '<option value="' . $i . '">' . $i . '</option>';
      }
    }
    
    return implode("\n", $html) . "\n</select>";
  }
  
  protected function selectedValue($type)
  {
    if ($this->timestamp === null) {
      return null;
    }
    
    switch ($type) {
      case "year":
        return date("Y", $this->timestamp);

      case "month":
        return date("n", $this->timestamp);

      case "day":
        return date("j", $this->timestamp);

      case "hour":
        return date("G", $this->timestamp);

      case "minute":
        return date("i", $this->timestamp);

      case "second":
        return date("s", $this->timestamp);
    }
  }
  
  protected function getYearRange($yearRange)
  {
    return ($yearRange === null) ? array(1980, 2035) : $yearRange;
  }
}
