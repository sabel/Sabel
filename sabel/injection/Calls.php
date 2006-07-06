<?php

class Sabel_Injection_Calls
{
  private static $before = array();
  private static $after  = array();
  
  /**
   * add both before and after injection.
   *
   * @param InjectionCall object
   * @return void
   */
  public function add($injection)
  {
    if (!$injection) return false;  
    $reflection = new ReflectionClass($injection);
    foreach ($reflection->getMethods() as $method) {
      if ($method->getName() == 'before') {
        $this->addBefore($injection);
      } else if ($method->getName() == 'after') {
        $this->addAfter($injection);
      }
    }
  }
  
  public function doBefore($method, $arg)
  {
    foreach (self::$before as $bi => $object) {
      $object->before($method, $arg);
    }
  }
  
  public function doAfter($method, &$result)
  {
    foreach (self::$after as $ai => $object) {
      $object->after($method, $result);
    }
  }
  
  public function addBefore($injection)
  {
    self::$before[] = $injection;
  }
  
  public function addAfter($injection)
  {
    self::$after[] = $injection;
  }
}

?>