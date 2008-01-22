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
class Sabel_Util_HashList extends Sabel_Object
{
  private
    $index    = 0,
    $hashList = array();
    
  private
    $pointer = 0,
    $names   = array(),
    $values  = array();
    
  public function add($name, $value)
  {
    if ($this->has($name)) {
      throw new Sabel_Exception_Runtime("'{$name}' already set.");
    } else {
      $this->names[$name] = $this->pointer;
      $this->values[$this->pointer] = $value;
      $this->pointer++;
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
      throw new Sabel_Exception_Runtime("'{$name}' already set.");
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
    } else {
      throw new Sabel_Exception_Runtime("'{$name}' already set.");
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
    } else {
      throw new Sabel_Exception_Runtime("'{$name}' already set.");
    }
  }
  
  public function delete($target)
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
    } else {
      throw new Sabel_Exception_Runtime();
    }
  }
  
  public function unlink($target)
  {
    $this->delete($target);
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
  
  public function next()
  {
    if (isset($this->values[$this->index])) {
      return $this->values[$this->index++];
    } else {
      return null;
    }
  }
  
  public function rewind()
  {
    $this->index = 0;
  }
}
