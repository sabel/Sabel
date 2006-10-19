<?php

/**
 * form
 *
 * Example :
 *
 * <? $form = new Sabel_Template_Form($model->schema(), (isset($errors)) ? $errors : null) ?>
 * <? $form->hidden(array('shop_id', 'users_id')) ?>
 * <?= $form->startTag(uri(array('action' => 'save', 'id' => $model->id)), 'POST') ?>
 * <? foreach ($form as $f) : ?>
 *   <?= $f->write("{$f->name()}<br />", "<br /><br />") ?>
 * <? endforeach ?>
 * <?= $form->submitTag('save') ?>
 * <?= $form->endTag() ?>
 *
 * @category   Template
 * @package    org.sabel.template
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Template_Form implements Iterator
{
  protected $position = 0;
  protected $size     = 0;
  
  protected $hidden   = array();
  protected $hiddenPattern = '';
  
  protected $columns  = array();
  protected $currentColumn = null;
  
  protected $errors = null;
  
  public function __construct($columns, $errors)
  {
    $this->columns = $columns;
    $this->size    = count($columns);
    $this->errors  = $errors;
  }
  
  public function startTag($action, $method = 'GET')
  {
    return sprintf('<form action="%s" method="%s">'."\n", $action, $method);
  }
  
  public function endTag()
  {
    return "</form>\n";
  }
  
  public function submitTag($value)
  {
    return '<input type="submit" value="'.$value.'" />';
  }
  
  public function isStart()
  {
    return ($this->position === 0);
  }
  
  public function isEnd()
  {
    return ($this->position === $this->size);
  }
  
  public function hidden($hiddens)
  {
    $this->hidden = $hiddens;
  }
  
  public function hiddenPattern($regex)
  {
    $this->hiddenPattern = $regex;
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
  
  public function name($showHidden = false)
  {
    $column = $this->currentColumn;
    $name   = $column->name;
    
    if ($showHidden && $this->isHidden()) {
      return $name;
    } else if (!$this->isHidden()) {
      return $name;
    }
  }
  
  public function value()
  {
    if (!$this->isHidden()) return $this->currentColumn->value;
  }
  
  public function write($prefix = null, $postfix = null, $format = null)
  {
    $column = $this->currentColumn;
    $fmt = (is_null($format)) ? '<input type="%s" name="%s" value="%s" />'."\n" : $format;
    
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
        return $prefix . sprintf("\n".$fmt."\n", 'text', $column->name, $column->value) . $postfix;
      }
    }
  }
  
  public function isError()
  {
    if (is_object($this->errors)) {
      return $this->errors->errored($this->currentColumn->name);
    } else {
      return false;
    }
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