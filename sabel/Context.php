<?php

/**
 * Sabel Context
 *
 * @category   Core
 * @package    org.sabel.core
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Context extends Sabel_Object
{
  private static $context = null;
  
  private
    $bus        = null,
    $candidate  = null,
    $candidates = array(),
    $exception  = null;
    
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
  
  public function setCandidate($candidate)
  {
    self::$context->candidate = $candidate;
  }
  
  public function getCandidate()
  {
    return self::$context->candidate;
  }
  
  public function setCandidates($candidates)
  {
    self::$context->candidates = $candidates;
  }
  
  public function getCandidates()
  {
    return self::$context->candidates;
  }
  
  public function getCandidateByName($name)
  {
    if (isset(self::$context->candidates[$name])) {
      return self::$context->candidates[$name];
    } else {
      return null;
    }
  }
  
  public function setBus($bus)
  {
    self::$context->bus = $bus;
  }
  
  public function getBus()
  {
    return self::$context->bus;
  }
  
  public function setException($exception)
  {
    self::$context->exception = $exception;
  }
  
  public function getException()
  {
    return self::$context->exception;
  }
}
