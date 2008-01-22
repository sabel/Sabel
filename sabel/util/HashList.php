<?php

/**
 * Sabel_Util_HashList
 *
 * @category   Util
 * @package    org.sabel.util
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Mori Reo <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Util_HashList extends Sabel_Object implements Countable
{
  const FIRST = "INDEX_FIRST";
  const LAST  = "INDEX_LAST";
  
  private
    $index     = self::FIRST,
    $hashList  = array(),
    $iterating = false;
    
  private
    $names  = array(),
    $values = array();
    
  public function add($name, $value)
  {
    if ($this->has($name)) {
      throw new Sabel_Exception_Runtime("'{$name}' already set.");
    } else {
      $index = $this->count();
      $this->names[$name]   = $index;
      $this->values[$index] = $value;
    }
  }
  
  public function replace($target, $name, $value)
  {
    if ($this->has($target)) {
      $p = $this->names[$target];
      unset($this->names[$target]);
      $this->values[$p] = $value;
      $this->names[$name] = $p;
    } else {
      throw new Sabel_Exception_Runtime("'{$target}' not found.");
    }
  }
  
  public function get($name)
  {
    if (isset($this->names[$name])) {
      return $this->values[$this->names[$name]];
    } else {
      return null;
    }
  }
  
  public function has($name)
  {
    return isset($this->names[$name]);
  }
  
  public function insertPrevious($target, $name, $insertValue)
  {
    if ($this->has($target)) {
      $p = $this->names[$target];
      foreach ($this->names as &$pointer) {
        if ($pointer >= $p) $pointer++;
      }
      
      $this->names[$name] = $p;
      $values = array();
      
      foreach ($this->values as $k => $value) {
        if ($k >= $p) {
          $values[$k + 1] = $value;
        } else {
          $values[$k] = $value;
        }
      }
      
      $values[$p] = $insertValue;
      $this->values = $values;
      
      if ($this->iterating && $p <= $this->index) {
        $this->index++;
      }
    } else {
      throw new Sabel_Exception_Runtime("'{$target}' not found.");
    }
  }
  
  public function insertNext($target, $name, $insertValue)
  {
    if ($this->has($target)) {
      $p = $this->names[$target];
      foreach ($this->names as &$pointer) {
        if ($pointer > $p) $pointer++;
      }
      
      $this->names[$name] = $p + 1;
      $values = array();
      
      foreach ($this->values as $k => $value) {
        if ($k > $p) {
          $values[$k + 1] = $value;
        } else {
          $values[$k] = $value;
        }
      }
      
      $values[$p + 1] = $insertValue;
      $this->values = $values;
      
      if ($this->iterating && ($p + 1) < $this->index) {
        $this->index++;
      }
    } else {
      throw new Sabel_Exception_Runtime("'{$target}' not found.");
    }
  }
  
  public function remove($target)
  {
    if ($this->has($target)) {
      $p = $this->names[$target];
      unset($this->names[$target]);
      unset($this->values[$p]);
      foreach ($this->names as &$pointer) {
        if ($pointer > $p) $pointer--;
      }
      
      ksort($this->values);
      $this->values = array_values($this->values);
      
      if ($this->iterating && $p <= $this->index) {
        $this->index--;
      }
      if ($this->iterating) $this->removedIndex = $p;
    } else {
      throw new Sabel_Exception_Runtime("'{$target}' not found.");
    }
  }
  
  public function first()
  {
    $this->index = self::FIRST;
  }
  
  public function last()
  {
    $this->index = self::LAST;
  }
  
  public function count()
  {
    return count($this->names);
  }
  
  public function next()
  {
    $i = ($this->index === self::FIRST) ? 0 : $this->index + 1;
    
    if (isset($this->values[$i])) {
      $this->iterating = true;
      $this->index = $i;
      return $this->values[$i];
    } else {
      return null;
    }
  }
  
  public function previous()
  {
    if ($this->index === null) return null;
    $i = ($this->index === self::LAST) ? $this->count() - 1 : $this->index - 1;
    
    if (isset($this->values[$i])) {
      $this->iterating = true;
      $this->index = $i;
      return $this->values[$i];
    } else {
      return null;
    }
  }
  
  public function toArray()
  {
    $names  = $this->names;
    $values = $this->values;
    
    asort($names);
    
    $retValue = array();
    foreach ($names as $name => $pointer) {
      $retValue[$name] = $values[$pointer];
    }
    
    return $retValue;
  }
}
