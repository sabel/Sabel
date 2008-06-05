<?php

class Sabel_Container_Lifecycle_Backend_Memcache implements Sabel_Container_Lifecycle_Backend
{
  const STORED_MARK = "__STORED__";
  
  private $storage = null;
  
  public function __construct()
  {
    $this->storage = new Sabel_Storage_Memcache();
  }
  
  public function store($className, Array $properties)
  {
    foreach ($properties as $name => $value) {
      $this->storage->store($className . "::" . $name, $value);
    }
    
    $this->storage->store($className . "::" . self::STORED_MARK, "true");
  }
  
  public function fetch($className, $instance, $reflection, Array $properties)
  {
    $exists = $this->storage->fetch($className . "::" . self::STORED_MARK);
    
    if ($exists === "true") {
      foreach ($properties as $property) {
        $pname = $property->getName();
        $setterMethod = "set" . ucfirst($pname);
        $key = $className . "::" . $pname;
        
        if ($reflection->hasMethod($setterMethod)) {
          $instance->$setterMethod($this->storage->fetch($key));
        }
      }
    }
  }
  
  public function isStored($className)
  {
    return ($this->storage->fetch($className . "::" . self::STORED_MARK) === "true");
  }
}
