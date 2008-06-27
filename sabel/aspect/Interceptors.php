<?php

/**
 * Interceptors
 *
 * @category   aspect
 * @package    org.sabel.aspect
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2008-2011 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */

class Sabel_Aspect_MethodBeforeAdviceInterceptor implements Sabel_Aspect_MethodInterceptor
{
  private $interceptor = null;
  
  public function __construct(Sabel_Aspect_MethodBeforeAdvice $interceptor)
  {
    $this->interceptor = $interceptor;
  }
  
  /**
   * implements Sabel_Aspect_MethodInterceptor
   */
  public function invoke(Sabel_Aspect_MethodInvocation $i)
  {
    $this->interceptor->before($i->getMethod(), $i->getArguments(), $i->getThis());
    return $i->proceed();
  }
}

class Sabel_Aspect_MethodAfterReturningAdviceInterceptor implements Sabel_Aspect_MethodInterceptor
{
  private $interceptor = null;
  
  public function __construct(Sabel_Aspect_MethodAfterReturningAdvice $interceptor)
  {
    $this->interceptor = $interceptor;
  }
  
  /**
   * implements Sabel_Aspect_MethodInterceptor
   */
  public function invoke(Sabel_Aspect_MethodInvocation $i)
  {
    $return = $i->proceed();
    
    $this->interceptor->after($i->getMethod(), $i->getArguments(), $i->getThis(), $return);
    
    return $return;
  }
}

class Sabel_Aspect_MethodThrowsAdviceInterceptor implements Sabel_Aspect_MethodInterceptor
{
  private $interceptor = null;
  
  public function __construct(Sabel_Aspect_MethodThrowsAdvice $interceptor)
  {
    $this->interceptor = $interceptor;
  }
  
  /**
   * implements Sabel_Aspect_MethodInterceptor
   */
  public function invoke(Sabel_Aspect_MethodInvocation $i)
  {
    try {
      return $i->proceed();
    } catch (Exception $e) {
      $this->interceptor->throws($i->getMethod(), $i->getArguments(), $i->getThis(), $e);
    }
  }
}


class Logger
{
  public function trace($msg)
  {
    // dump($msg);
  }
}

abstract class Sabel_Aspect_AbstractTraceInterceptor implements Sabel_Aspect_MethodInterceptor
{
  public function invoke(Sabel_Aspect_MethodInvocation $invocation)
  {
    $logger = new Logger();
    return $this->invokeUnderTrace($invocation, $logger);
  }
  
  abstract protected function invokeUnderTrace(Sabel_Aspect_MethodInvocation $invocation, $logger);
}

class Sabel_Aspect_SimpleTraceInterceptor extends Sabel_Aspect_AbstractTraceInterceptor
{
  protected function invokeUnderTrace(Sabel_Aspect_MethodInvocation $invocation, $logger) {
		$invocationDescription = $this->getInvocationDescription($invocation);
		$logger->trace("Entering: " . $invocationDescription);
		
		try {
			$rval = $invocation->proceed();
			$logger->trace("Exiting: " . $invocationDescription . " with " . var_export($rval, 1));
			return $rval;
		}	catch (Exception $ex) {
			$logger->trace("Exception thrown in " . $invocationDescription, $ex);
			throw $ex;
		}
	}
	
	protected function getInvocationDescription($invocation)
	{
	  $fmt = "method '%s' of class[%s]";
	  return sprintf($fmt, $invocation->getMethod()->getName(),
	                       $invocation->getThis()->getName());
	}
}

class Sabel_Aspect_DebugInterceptor extends Sabel_Aspect_SimpleTraceInterceptor
{
  public function invoke(Sabel_Aspect_MethodInvocation $invocation)
  {
    return parent::invoke($invocation);
  }
  
  protected function invokeUnderTrace(Sabel_Aspect_MethodInvocation $invocation, $logger) {
		$invocationDescription = $this->getInvocationDescription($invocation);
	  
		$logger->trace("debug Entering: " . $invocationDescription);
		
		try {
		  $s = microtime();		  
			$rval = $invocation->proceed();
			$end = (microtime() - $s) * 1000;
			$logger->trace("debug Exiting: " . $invocationDescription . " with " . var_export($rval, 1));
			$logger->trace("taking time: " . $end . "ms");
			return $rval;
		}	catch (Exception $ex) {
			$logger->trace("Exception thrown in " . $invocationDescription, $ex);
			throw $ex;
		}
	}
}