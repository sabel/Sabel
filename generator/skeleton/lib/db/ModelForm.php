<?php

/**
 * ModelForm
 *
 * @category  DB
 * @package   org.sabel.db
 * @author    Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright 2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class ModelForm
{
  protected $model   = null;
  protected $mdlName = "";
  protected $columns = null;
  
  public function __construct($model)
  {
    $this->model   = $model;
    $this->mdlName = $model->getModelName();
    $this->columns = $model->getSchema()->getColumns();
  }
  
  public function create($uri, $id = null, $class = null, $name = null)
  {
    $columns = $this->columns;
    $schema  = $this->model->getSchema();
    $mdlName = $this->mdlName;
    $html    = array($this->start($uri, $id, $class, "POST", $name));
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
    
    $html[] = "<br/>" . $this->submit();
    $html[] = $this->end();
    
    return implode("<br/>\n", $html) . "\n";
  }
  
  public function start($uri, $class = null, $id = null, $method = "POST", $name = null)
  {
    $html = '<form action="' . uri($uri) . '" method="' . $method . '" ';
    $this->addIdAndClass($html, $id, $class);
    if ($name !== null) $html .= 'name="' . $name . '" ';
    
    return $html . " />";
  }
  
  public function end()
  {
    return "</form>";
  }
  
  public function text($name, $class = null, $id = null)
  {
    $value = $this->getValue($name);
    $name  = $this->getName($name);
    $html  = '<input type="text" ';
    $this->addIdAndClass($html, $id, $class);
    $html .= 'name="' . $name . '" value="' . $value . '">';
    
    return $html;
  }
  
  public function password($name, $class = null, $id = null)
  {
    $value = $this->getValue($name);
    $name  = $this->getName($name);
    $html  = '<input type="password" ';
    $this->addIdAndClass($html, $id, $class);
    $html .= 'name="' . $name . '" value="' . $value . '">';
    
    return $html;
  }
  
  public function textarea($name, $class = null, $id = null)
  {
    $value = $this->getValue($name);
    $name  = $this->getName($name);
    $html  = '<textarea ';
    $this->addIdAndClass($html, $id, $class);
    $html .= 'name="' . $name . '">' . $value . '</textarea>';
    
    return $html;
  }
  
  public function checkbox($name, $class = null, $id = null)
  {
    $value = $this->getValue($name);
    $name  = $this->getName($name);
    $html  = '<input type="checkbox" ';
    $this->addIdAndClass($html, $id, $class);
    $html .= 'name="' . $name . '" value="1"';
    
    if ($value === true) $html .= ' checked="checked"';
    
    return $html . ' />';
  }
  
  public function datetime($name, $yearRange = null, $withSecond = false)
  {
    $value = $this->getValue($name);
    $name  = $this->getName("datetime") . "[{$name}]";
    $dtime = new ModelFormDatetime($name, $value);
    return $dtime->datetime($yearRange, $withSecond);
  }
  
  public function date($name, $yearRange = null)
  {
    $value = $this->getValue($name);
    $name  = $this->getName("date") . "[{$name}]";
    $dtime = new ModelFormDatetime($name, $value);
    return $dtime->date($yearRange);
  }
  
  public function hidden($name, $class = null, $id = null)
  {
    $value = $this->getValue($name);
    $name  = $this->getName($name);
    $html  = '<input type="hidden" ';
    $this->addIdAndClass($html, $id, $class);
    $html .= 'name="' . $name . '" value="' . $value . '">';
    
    return $html;
  }
  
  public function submit($value = "")
  {
    if ($value !== "") $value = 'value="' . $value. '" ';
    return '<input type="submit" ' . $value . '/>';
  }

  protected function getName($name)
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

class ModelFormDatetime
{
  protected
    $name      = "",
    $timestamp = null;
  
  public function __construct($name, $datetime = null)
  {
    $this->name = $name;
    
    if ($datetime === null) {
      $this->timestamp = time();
    } else {
      $this->timestamp = strtotime($datetime);
    }
  }
  
  public function datetime($yearRange = null, $withSecond = false)
  {
    $name = $this->name;
    list ($first, $last) = $this->getYearRange($yearRange);
    
    $html   = array();
    $html[] = $this->numSelect("year",   $name, "year", $first, $last);
    $html[] = $this->numSelect("month",  $name, "month",  1, 12);
    $html[] = $this->numSelect("day",    $name, "day",    1, 31);
    $html[] = $this->numSelect("hour",   $name, "hour",   0, 23);
    $html[] = $this->numSelect("minute", $name, "minute", 0, 59);
    
    if ($withSecond) {
      $html[] = $this->numSelect("second", $name . "_second", 0, 59);
    }
    
    return implode("&nbsp;", $html);
  }
  
  public function date($yearRange = null)
  {
    $name = $this->name;
    list ($first, $last) = $this->getYearRange($yearRange);
    
    $html   = array();
    $html[] = $this->numSelect("year",  $name, "year",  $first, $last);
    $html[] = $this->numSelect("month", $name, "month", 1, 12);
    $html[] = $this->numSelect("day",   $name, "day",   1, 31);
    
    return implode("&nbsp;", $html);
  }
  
  protected function numSelect($type, $name, $type, $start, $end)
  {
    $html = array('<select name="' . $name . '[' . $type . ']">');
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
