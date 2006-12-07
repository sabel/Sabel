<?php

Sabel::using('Sabel_Form_HtmlElement');
Sabel::using('Sabel_Form_Select');
Sabel::using('Sabel_Form_Option');
Sabel::using('Sabel_Form_OptionGroup');

/**
 * Sabel_Template_Form
 *
 * Example :
 * <code>
 *   <? $form = new Sabel_Template_Form($model->schema(), (isset($errors)) ? $errors : null) ?>
 *   <? $form->hidden(array('shop_id', 'users_id')) ?>
 *   <?= $form->startTag(uri(array('action' => 'save', 'id' => $model->id)), 'POST') ?>
 *   <? foreach ($form as $f) : ?>
 *     <?= $f->write("{$f->name()}<br />", "<br /><br />") ?>
 *   <? endforeach ?>
 *   <?= $form->submitTag('save') ?>
 *   <?= $form->endTag() ?>
 * </code>
 *
 * @category   Template
 * @package    org.sabel.template
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Form implements Iterator
{
  protected $position = 0;
  protected $size     = 0;
  
  protected $hidden   = array();
  protected $hiddenPattern = '';
  
  protected $model = null;
  protected $columns  = array();
  protected $currentColumn = null;
  
  protected $errors = null;
  
  protected $yearRange = array();
  
  protected $ignore = array();
  
  public function __construct($model, $errors)
  {
    $this->model   = $model;
    $this->columns = $model->schema();
    $this->size    = count($this->columns);
    $this->errors  = $errors;
  }
  
  public function startTag($action, $method = 'GET', $class = "form")
  {
    return sprintf('<form action="%s" method="%s" class="%s">'."\n", $action, $method, $class);
  }
  
  public function endTag()
  {
    return "</form>\n";
  }
  
  public function submitTag($value, $style = '')
  {
    return '<input type="submit" value="'.$value.'" style="'.$style.'" />';
  }
  
  public function isStart()
  {
    return ($this->position === 0);
  }
  
  public function isEnd()
  {
    return ($this->position === $this->size);
  }
  
  public function setValue($name, $value)
  {
    $this->columns[$name]->value = $value;
    return $this;
  }
  
  public function hidden($hiddens)
  {
    $this->hidden = $hiddens;
    return $this;
  }
  
  public function hiddenPattern($regex)
  {
    $this->hiddenPattern = $regex;
    return $this;
  }
  
  public function isHidden()
  {
    $name = $this->currentColumn->name;
    if (in_array($name, $this->hidden) ||
        (!empty($this->hiddenPattern) && preg_match('/'.$this->hiddenPattern.'/', $name))) {
      return true;
    } else {
      return false;
    }
  }
  
  public function ignore($mixed)
  {
    if (is_array($mixed)) {
      foreach ($mixed as $ignore) {
        if (isset($this->columns[$ignore])) {
          unset($this->columns[$ignore]);
        }
      }
      $this->size = count($this->columns);
      $this->ignore = array_merge($this->ignore, $mixed);
    } elseif (is_string($mixed)) {
      if (isset($this->columns[$mixed])) {
        unset($this->columns[$mixed]);
      }
      $this->size = count($this->columns);
      $this->ignore[] = $mixed;
    }
  }
  
  public function name($showHidden = false)
  {
    $column = $this->currentColumn;
    return _($column->name);
  }
  
  public function value()
  {
    if (!$this->isHidden()) return $this->currentColumn->value;
  }
  
  public function write($prefix = null, $postfix = null, $nessecary = null, $format = null)
  {
    $column = $this->currentColumn;
    
    $fmt = ($format === null) ? '<input type="%s" name="%s" value="%s" />'."\n" : $format;
    
    if ($this->isError()) {
      $error = $this->errors->get($column->name);
      if ($this->isHidden()) {
        return sprintf($fmt, 'hidden', $column->name, $error->getValue());
      } else {
        return $prefix . sprintf("\n".$fmt."\n", 'text', $column->name, $error->getValue()) . $postfix;
      }
    } else {
      if ($this->isHidden()) {
        return sprintf($fmt, 'hidden', $column->name, $column->value);
      } else {
        if ($this->isText()) {
          $result = $this->textarea($column->name, $column->value);
        } elseif ($this->isBool()) {
          $result = $this->checkbox($column->name, $column->value, $column->default);
        } elseif ($this->isDate()) {
          $result = $this->date($column->name, $column->value);
        } elseif ($this->isDatetime()) {
          $result = $this->datetime($column->name, $column->value);
        } elseif ($this->isTime()) {
          $result = $this->time($column->name, $column->value);
        } else {
          $result = $this->input('text', $column->name, $column->value);
        }
        
        if ($this->isNecessary()) {
          return $nessecary . $result . $postfix;
        } else {
          return $prefix . $result . $postfix;
        }
        
      }
    }
  }
  
  public function isString()
  {
    return ($this->currentColumn->type === Sabel_DB_Type_Const::STRING);
  }
  
  public function input($type, $name, $value, $id = '', $class = '', $style = '')
  {
    if (empty($id)) $id = $this->defaultID();
    $fmt = '<input type="%s" name="%s" value="%s" id="%s" class="%s" style="%s" />';
    return sprintf($fmt, $type, $name, $value, $id, $class, $style);
  }
  
  public function isText()
  {
    return ($this->currentColumn->type === Sabel_DB_Type_Const::TEXT);
  }
  
  public function textarea($name, $value = '', $id = '', $class = '', $style = '')
  {
    if (empty($id)) $id = $this->defaultID();
    $fmt = '<textarea name="%s" id="%s" class="%s" style="%s">%s</textarea>';
    return sprintf($fmt, $name, $id, $class, $style, $value);
  }
  
  public function isBool()
  {
    return ($this->currentColumn->type === Sabel_DB_Type_Const::BOOL);
  }
  
  public function checkbox($name, $value, $default, $id = '', $class = '', $style = '')
  {
    if (empty($id)) $id = $this->defaultID();
    $value = (isset($value)) ? $value : $default;
    // <input type="hidden" value="false" name="testbool"/>
    $fmt  = '<input type="checkbox" value="true" name="%s" id="%s" class="%s" style="%s"';
    if ($value) $fmt .= ' checked="checked"';
    $fmt .= ' />';
    return sprintf($fmt, $name, $id, $class, $style);
  }
  
  public function isDate()
  {
    return ($this->currentColumn->type === Sabel_DB_Type_Const::DATE);
  }
  
  public function isTime()
  {
    return ($this->currentColumn->type === Sabel_DB_Type_Const::TIME);
  }
  
  public function isDatetime()
  {
    return ($this->currentColumn->type === Sabel_DB_Type_Const::DATETIME);
  }
  
  public function date($name, $value, $default = '', $id = '', $class = '', $style = '')
  {
    $tsNow    = time();
    $defYear  = date('Y', $tsNow);
    $defMonth = date('n', $tsNow);
    $defDay   = date('d', $tsNow);
    
    $sYear  = new Sabel_Form_Select($name . '[year]');
    $sMonth = new Sabel_Form_Select($name . '[month]');
    $sDay   = new Sabel_Form_Select($name . '[day]');
    
    $listYears = (count($this->yearRange) === 0) ? array(2005, 2006, 2007): $listYears = $this->yearRange;
    
    foreach ($listYears as $year)
      $sYear->addOption(new Sabel_Form_Option($year, $year, ($year == $defYear)));
    
    for ($m = 1; $m <= 12; ++$m)
      $sMonth->addOption(new Sabel_Form_Option($m, $m, ($m == $defMonth)));
    
    for ($d = 1; $d <= 31; ++$d)
      $sDay->addOption(new Sabel_Form_Option($d, $d, ($d == $defDay)));
    
    return $sYear->toHtml() . $sMonth->toHtml() . $sDay->toHtml();
  }
  
  public function time($name, $value, $default = '', $id = '', $class = '', $style = '')
  {
    $tsNow    = time();
    $defHour  = date('G', $tsNow);
    $defMin   = date('i', $tsNow);
    
    $hour = new Sabel_Form_Select($name . '[hour]');
    $min  = new Sabel_Form_Select($name . '[min]');
    
    for ($h = 0; $h <= 23; ++$h)
      $hour->addOption(new Sabel_Form_Option($h, $h, ($h == $defHour)));
    
    for ($m = 0; $m <= 60; ++$m)
      $min->addOption(new Sabel_Form_Option($m, $m, ($m == $defMin)));
    
    return $hour->toHtml() . ' : ' . $min->toHtml();
  }
  
  public function datetime($name, $value, $default = '', $id = '', $class = '', $style = '')
  {
    return $this->date($name, $value, $default, $id, $class, $style) .' - '. $this->time($name, $value);
  }
  
  public function yearRange($range)
  {
    $this->yearRange = $range;
  }
  
  public function isError()
  {
    if (is_object($this->errors)) {
      return $this->errors->errored($this->currentColumn->name);
    } else {
      return false;
    }
  }
  
  public function defaultID()
  {
    return $this->model->table . '_' . $this->currentColumn->name;
  }
  
  public function isNecessary()
  {
    return ($this->currentColumn->nullable === false);
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function current() {
    $columns = array_values($this->columns);
    $this->currentColumn = $columns[$this->position];
    return $this;
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function key()
  {
    return $this->position;
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function next()
  {
    return $this->position++;
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function rewind()
  {
    $this->position = 0;
  }
  
  /**
   * implements for Iterator interface
   *
   */
  public function valid()
  {
    return ($this->position < $this->size);
  }
}
