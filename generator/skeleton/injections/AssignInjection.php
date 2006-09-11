<?php

class Injection_Assign
{
  public function when()
  {
    return true;
  }
  
  public function after($method, $result, $reflection)
  {
    $anonr = Container::create()->instanciate('sabel.annotation.Reader');
    $anonr->annotation($reflection->getName());
    $assigns = $anonr->getAnnotationsByName($reflection->getName(), 'assign');
    
    foreach ($assigns as $annot) {
      $assign = $annot->getContents();
      if ($method->getName() === $assign[0]) {
        Re::set($assign[2], $result);
      }
    }
  }
}

Sabel_Injection_Calls::add(new Injection_Assign());