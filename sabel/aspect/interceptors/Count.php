<?php

class Sabel_Aspect_Interceptors_Count
{
  public function before($joinpoint)
  {
    Sabel_View_Pager::setCount($joinpoint->getTarget()->getCount());
    
    $joinpoint->getTarget()->sconst('limit',  Sabel_View_Pager::getLimit());
    $joinpoint->getTarget()->sconst('offset', Sabel_view_Pager::getOffset());
  }
}

Sabel_Aspect_Aspects::singleton()->addPointcut(
  Sabel_Aspect_Pointcut::create('Sabel_Aspect_Interceptors_Count')
  ->setMethodRegex('select.*'));
