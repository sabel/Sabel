<?php

/**
 * MethodInvocation
 *
 * @category   aspect
 * @package    org.sabel.aspect
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2008-2011 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Aspect_DefaultMethodInvocation implements Sabel_Aspect_MethodInvocation
{
  private $proxy = null;
  
  private $class  = null;
  private $reflection = null;
  
  private $method   = null;
  private $argument = null;
  
  private $advices = array();
  
  private $currentAdviceIndex = -1;
  private $lastAdviceIndex    = -1;
  
  public function __construct($proxy, $class, $method = null, $argument = null)
  {
    $this->proxy = $proxy;
    $this->class = $class;
    $this->reflection = new Sabel_Reflection_Class($class);
    
    if ($method   !== null) $this->method   = $method;
    if ($argument !== null) $this->argument = $argument;
  }
  
  public function setMethod($method)
  {
    $this->method = $method;
  }
  
  public function setArgument($argument)
  {
    $this->argument = $argument;
  }
  
  public function reset($method, $argument)
  {
    $this->method   = $method;
    $this->argument = $argument;
    
    $this->currentAdviceIndex = -1;
  }
  
  public function setAdvices($advices)
  {
    $this->advices = $advices;
    $this->lastAdviceIndex = count($advices);
  }
  
  /**
   * implements Sabel_Aspect_Invocation
   */
  public function getArguments()
  {
    return $this->argument;
  }
  
  /**
   * implements Sabel_Aspect_MethodInvocation
   */
  public function getMethod()
  {
    return $this->reflection->getMethod($this->method);
  }
  
  /**
   * implements Sabel_Aspect_Joinpoint
   */
  public function getStaticPart()
  {
  }
  
  /**
   * implements Sabel_Aspect_Joinpoint
   */
  public function getThis()
  {
    return $this->reflection;
  }
  
  /**
   * implements Sabel_Aspect_Joinpoint
   */
  public function proceed()
  {
    if ($this->lastAdviceIndex === -1 || $this->currentAdviceIndex === $this->lastAdviceIndex - 1) {
      return $this->reflection
                  ->getMethod($this->method)
                  ->invokeArgs($this->class, $this->argument);
    }
    
    if (isset($this->advices[++$this->currentAdviceIndex])) {
      $advice = $this->advices[$this->currentAdviceIndex];
      
      if ($advice instanceof Sabel_Aspect_MethodInterceptor) {
        return $advice->invoke($this);
      } else {
        throw new Sabel_Exception_Runtime("advice must be Sabel_Aspect_MethodInterceptor");
      }
    }
  }
}