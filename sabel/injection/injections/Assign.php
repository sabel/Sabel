<?php

class Sabel_Injection_Injections_Assign
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
    
    $assignFromAnnotation = false;
    foreach ($assigns as $annot) {
      $assign = $annot->getContents();
      if ($method->getName() === $assign[0]) {
        $assignFromAnnotation = true;
        Re::set($assign[2], $result);
      }
    }
    
    if (!$assignFromAnnotation) Re::set($method->getName(), $result);
  }
}

Sabel_Injection_Calls::add(new Sabel_Injection_Injections_Assign());