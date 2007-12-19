<?php

/**
 * Renderer_Util_Element
 *
 * @category   Addon
 * @package    addon.renderer
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Renderer_Util_Element
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
    
    $tag  = $this->tag;
    $name = array();
    for ($i = 1, $size = strlen($tag); $i < $size; $i++) {
      $char = substr($tag, $i, 1);
      if ($char === ">" || $char === " " || $char === "/") break;
      $name[] = $char;
    }
    
    return $this->name = implode("", $name);
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
    
    $tag = trim(str_replace("<{$this->name()}", "", $this->tag));
    if (mb_strpos($tag, "=") === false) return null;
    
    $keys   = array();
    $values = array();
    $attributes = array();
    
    if (preg_match_all('/(.*?)=("(.*?)"|\'(.*?)\')/m', $tag, $matches)) {
      $keys = array_map("trim", $matches[1]);
      
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
