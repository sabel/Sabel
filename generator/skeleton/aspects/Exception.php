<?php

/**
 * primitive interceptor of SabelAspect
 *
 * @category   Aspect
 * @package    org.sabel.aspect.interceptors
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Aspects_Exception
{
  public function throwing($joinpoint)
  {
    echo request(uri(array('controller'=>'index', 'action' => 'notfound'), false));
    exit;
  }
}

Sabel_Aspect_Aspects::singleton()->addPointcut(
  Sabel_Aspect_Pointcut::create('Aspects_Exception')
  ->setMethodRegex('.*'));