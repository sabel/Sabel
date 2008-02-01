<?php

/**
 * Sabel Context
 *
 * @category   Core
 * @package    org.sabel.core
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2002-2006 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Context extends Sabel_Object
{
  private static $context = null;
  
  private
    $bus        = null,
    $candidate  = null,
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
