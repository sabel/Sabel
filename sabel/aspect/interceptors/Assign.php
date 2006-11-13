<?php

/**
 * primitive interceptor of SabelAspect
 *
 * @category   Aspect
 * @package    org.sabel.aspect.interceptors
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Aspect_Interceptors_Assign
{
  public function after($joinpoint)
  {
    $reflection = new ReflectionClass($joinpoint->getTarget());
    
    $anonr = new Sabel_Annotation_Reader();
    $anonr->annotation($reflection->getName());
    $assigns = $anonr->getAnnotationsByName($reflection->getName(), 'assign');
    
    $assignFromAnnotation = false;
    foreach ($assigns as $annot) {
      $assign = $annot->getContents();
      
      if ($joinpoint->getMethod() === $assign[0]) {
        $assignFromAnnotation = true;
        Sabel_Template_Engine::setAttribute($assign[2], $joinpoint->getResult());
      }
    }
    
    if (!$assignFromAnnotation) {
      Sabel_Template_Engine::setAttribute($joinpoint->getMethod(), $joinpoint->getResult());
    }
  }
}

Sabel_Aspect_Aspects::singleton()->addPointcut(
  Sabel_Aspect_Pointcut::create('Sabel_Aspect_Interceptors_Assign')
  ->toAll());