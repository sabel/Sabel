<?php

/**
 * primitive interceptor of SabelAspect
 *
 * @category   Aspect
 * @package    org.sabel.aspect.interceptors
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2002-2006 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Aspect_Interceptors_Assign
{
  public function after($joinpoint)
  {
    $reflection = new ReflectionClass($joinpoint->getTarget());
    
    $anonr = Sabel_Annotation_Reader::create();
    $anonr->annotation($reflection->getName());
    $assigns = $anonr->getAnnotationsByName($reflection->getName(), 'assign');
    
    $view = Sabel_Context::getView();
    
    $assignFromAnnotation = false;
    foreach ($assigns as $annot) {
      $assign = $annot->getContents();
      
      if ($joinpoint->getMethod() === $assign[0]) {
        $assignFromAnnotation = true;
        $view->assign($assign[2], $joinpoint->getResult());
      }
    }
    
    if (!$assignFromAnnotation) {
      $view->assign($joinpoint->getMethod(), $joinpoint->getResult());
    }
  }
}

Sabel_Aspect_Aspects::singleton()->addPointcut(
  Sabel_Aspect_Pointcut::create('Sabel_Aspect_Interceptors_Assign')
  ->toAll());