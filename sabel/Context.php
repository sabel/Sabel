<?php

/**
 * Sabel Context
 *
 * @category   Core
 * @package    org.sabel.core
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Context extends Sabel_Object
{
  protected static $context = null;
  
  protected $bus        = null;
  protected $candidate  = null;
  protected $redirector = null;
  protected $exception  = null;
  
  public static function setContext($context)
  {
    self::$context = $context;
  }
  
  public static function getContext()
  {
    if (self::$context === null) {
      self::$context = new self();
    }
    
    return self::$context;
  }
  
  public function setBus($bus)
  {
    $this->bus = $bus;
  }
  
  public function getBus()
  {
    return $this->bus;
  }
  
  public function setCandidate($candidate)
  {
    $this->candidate = $candidate;
  }
  
  public function getCandidate()
  {
    return $this->candidate;
  }
  
  public function setRedirector($redirector)
  {
    $this->redirector = $redirector;
  }
  
  public function getRedirector()
  {
    return $this->redirector;
  }
  
  public function setException($exception)
  {
    $this->exception = $exception;
  }
  
  public function getException()
  {
    return $this->exception;
  }
}
