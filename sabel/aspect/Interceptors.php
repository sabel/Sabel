<?php

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