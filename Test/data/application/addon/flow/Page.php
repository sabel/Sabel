<?php

/**
 * Flow_Page
 *
 * @category   Addon
 * @package    addon.flow
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Flow_Page extends Sabel_Controller_Page
{
  public function __get($name)
  {
    if (isset($this->attributes["flow"])) {
      $value = $this->attributes["flow"]->read($name);
      return ($value === null) ? parent::getAttribute($name) : $value;
    } else {
      return parent::getAttribute($name);
    }
  }
  
  public function __set($name, $value)
  {
    if (isset($this->attributes["flow"])) {
      $this->attributes["flow"]->write($name, $value);
    }
    
    parent::setAttribute($name, $value);
  }
  
  public function setAttribute($name, $value)
  {
    $this->__set($name, $value);
  }
  
  public function setAttributes($attributes)
  {
    if (isset($this->attributes["flow"])) {
      $flow = $this->attributes["flow"];
      foreach ($attributes as $name => $value) {
        $flow->write($name, $value);
      }
    }
    
    parent::setAttributes($attributes);
  }
  
  public function getAttribute($name)
  {
    return $this->__get($name);
  }
  
  public function hasAttribute($name)
  {
    if (isset($this->attributes["flow"])) {
      $bool = $this->attributes["flow"]->has($name);
      return ($bool) ? true : parent::hasAttribute($name);
    } else {
      return parent::hasAttribute($name);
    }
  }
}
