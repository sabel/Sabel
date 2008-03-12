<?php

/**
 * Renderer_Sabel_Element
 *
 * @category   Addon
 * @package    addon.renderer
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Renderer_Sabel_Element
{
  private $tag  = "";
  private $name = null;
  private $attributes = null;
  
  public function __construct($tag)
  {
    $this->tag = $tag;
  }
  
  public function __toString()
  {
    return $this->tag();
  }
  
  public function tag()
  {
    return $this->tag;
  }
  
  public function name()
  {
    if ($this->name !== null) return $this->name;
    
    preg_match('/<([^ \/>]*)/', $this->tag, $matches);
    return $this->name = $matches[1];
  }
  
  public function __get($key)
  {
    if ($this->attributes === null) {
      $attributes = $this->getAttributes();
    } else {
      $attributes = $this->attributes;
    }
    
    if (isset($attributes[$key])) {
      return $attributes[$key];
    } else {
      return null;
    }
  }
  
  public function getAttributes()
  {
    if ($this->attributes !== null) return $this->attributes;
    
    $keys   = array();
    $values = array();
    $attributes = array();
    
    if (preg_match_all('/([^ ]*?)=("([^"]*)"|\'([^\']*)\')/', $this->tag, $matches)) {
      $keys = $matches[1];
      
      if (!empty($matches[3])) {
        $values = $matches[3];
      }
      
      if (!empty($matches[4])) {
        foreach ($matches[4] as $k => $v) {
          if ($v !== "") $values[$k] = $v;
        }
      }
      
      return $this->attributes = $attributes = array_combine($keys, $values);
    } else {
      return $this->attributes = array(); 
    }
  }
}
