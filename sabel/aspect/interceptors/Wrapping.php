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
class Sabel_Aspect_Interceptors_Wrapping
{
  public function after($joinpoint)
  {
    $result = $joinpoint->getResult();
    
    if (is_object($result))
      return new Sabel_Aspect_Proxy($result);
  }
}

Sabel_Aspect_Aspects::singleton()->addPointcut(
  Sabel_Aspect_Pointcut::create('Sabel_Aspect_Interceptors_Wrapping')
  ->setMethod('selectOne'));
