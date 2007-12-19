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
    $bus       = null,
    $candidate = null,
    $exception = null;
  
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
    $this->candidate = $candidate;
  }
  
  public function getCandidate()
  {
    return $this->candidate;
  }
  
  public function setBus($bus)
  {
    $this->bus = $bus;
  }
  
  public function getBus()
  {
    return $this->bus;
  }
  
  public function setException($exception)
  {
    $this->exception = $exception;
  }
  
  public function getException()
  {
    return $this->exception;
  }
  
  public static function log($message, $level = LOG_INFO, $fileName)
  {
    static $log = null;
    
    if ($log === null) $log = self::getLogger();
    $log->log($message, $level, $fileName);
  }
  
  public static function getLogger()
  {
    return Sabel_Logger_File::singleton();
  }
}
