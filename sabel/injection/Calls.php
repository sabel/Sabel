<?php

/**
 * Sabel_Injection_Calls
 *
 * @category   Injection
 * @package    org.sabel.injection
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
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
  public static function add($injection)
  {
    if (!$injection) return false;
    
    $reflection = new ReflectionClass($injection);
    foreach ($reflection->getMethods() as $method) {
      if ($method->getName() === 'before') {
        self::addBefore($injection);
      } else if ($method->getName() === 'after') {
        self::addAfter($injection);
      }
    }
  }
  
  public static function doBefore($method, $arg, $reflection, $target)
  {
    $execute = true;
    foreach (self::$before as $object) {
      if ($object->when($method)) {
        $execute = $object->before($method, $arg, $reflection, $target);
      }
    }
    if (is_null($execute)) $execute = true;
    return $execute;
  }
  
  public static function doAfter($method, &$result, $reflection)
  {
    foreach (self::$after as $object) {
      if ($object->when($method)) $object->after($method, $result, $reflection);
    }
  }
  
  public static function addBefore($injection)
  {
    self::$before[] = $injection;
  }
  
  public static function addAfter($injection)
  {
    self::$after[] = $injection;
  }
}