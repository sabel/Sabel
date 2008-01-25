<?php

/**
 * Sabel_DB_Schema_ForeignKey
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_DB_Schema_ForeignKey extends Sabel_Object
{
  private
    $fkeys   = array(),
    $objects = array();
    
  public function __construct($fkeys)
  {
    if (is_array($fkeys)) {
      $this->fkeys = $fkeys;
    } else {
      throw new Sabel_Exception_InvalidArgument("argument must be an array.");
    }
  }
  
  public function has($key)
  {
    return isset($this->fkeys[$key]);
  }
  
  public function __get($key)
  {
    if ($this->has($key)) {
      if (isset($this->objects[$key])) {
        return $this->objects[$key];
      } else {
        $fkey = $this->fkeys[$key];
        $stdClass = new stdClass();
        $stdClass->table     = $fkey["referenced_table"];
        $stdClass->column    = $fkey["referenced_column"];
        $stdClass->onDelete  = $fkey["on_delete"];
        $stdClass->onUpdate  = $fkey["on_update"];
        
        return $this->objects[$key] = $stdClass;
      }
    } else {
      return null;
    }
  }
  
  public function toArray()
  {
    $fkeys = array();
    foreach (array_keys($this->fkeys) as $key) {
      $fkeys[$key] = $this->__get($key);
    }
    
    return $fkeys;
  }
}
