<?php

/**
 * form
 *
 * Example :
 *
 * <? $form = new Sabel_Template_Form($bbs->schema()) ?>
 * <? $form->hidden(array('shop_id', 'users_id')) ?>
 * <?= $form->startTag(uri(array('action'=>'edit', 'id'=>$bbs->id), 'POST')) ?>
 * <? foreach ($form as $f) : ?>
 *   <? if ($f->isHidden()) : ?>
 *     <?= $f->write() ?>
 *   <? else : ?>
 *     <?= $f->name() ?><?= $f->write() ?> <br />
 *   <? endif ?>
 * <? endforeach ?>
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
  protected $hidden   = array();
  protected $columns  = array();
  protected $size     = 0;
  protected $currentColumn = null;
  
  public function __construct($columns)
  {
    $this->columns = $columns;
    $this->size    = count($columns);
  }
  
  public function startTag($action, $method = 'GET')
  {
    return sprintf('<form action="%s" method="%s">', $action, $method);
  }
  
  public function endTag()
  {
    return '</form>';
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
  
  public function isHidden()
  {
    return in_array($this->currentColumn->name, $this->hidden);
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
  
  public function write()
  {
    $column = $this->currentColumn;
    if (in_array($column->name, $this->hidden)) {
      $type = 'hidden';
    } else {
      $type = 'text';
    }
    
    return sprintf('<input type="%s" name="%s" value="%s">', $type, $column->name, $column->value);
  }
  
  public function isError()
  {
    
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