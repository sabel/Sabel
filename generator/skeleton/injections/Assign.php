<?php

class Injection_Assign
{
  public function when()
  {
    return true;
  }
  
  public function after($method, $result, $reflection)
  {
    $assignName = $reflection->getName() . '_' . $method->getName();
    Re::set($assignName, $result);
  }
}

Sabel_Injection_Calls::add(new Injection_Assign());