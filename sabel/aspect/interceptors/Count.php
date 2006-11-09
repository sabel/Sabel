<?php

class Sabel_Aspect_Interceptors_Count
{
  public function before($joinpoint)
  {
    $pager = Sabel_View_Pager::create();
    $pager->setCount($joinpoint->getTarget()->getCount());
    
    $joinpoint->getTarget()->sconst('limit',  $pager->getLimit());
    $joinpoint->getTarget()->sconst('offset', $pager->getOffset());
  }
}

Sabel_Aspect_Aspects::singleton()->addPointcut(
  Sabel_Aspect_Pointcut::create('Sabel_Aspect_Interceptors_Count')
  ->setMethodRegex('select.*'));
