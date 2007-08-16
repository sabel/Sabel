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
class Sabel_Context
{
  private static $includePath = array();
  
  private static $context = null;
  
  private $candidate   = null;
  
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
  
  public static function log($message)
  {
    static $log;
    if (!isset($log)) $log = Sabel_Logger_Factory::create();
    $log->log($message);
  }
  
  public static function getLogger()
  {
    return Sabel_Logger_File::singleton();
  }
  
  public static function getCache()
  {
    $config = CachedConfigImpl::create()->get('Memcache');
    return MemCacheImpl::create($config['server']);
  }
  
  public static function addIncludePath($path)
  {
    if (!in_array($path, self::$includePath)) {
      self::$includePath[] = $path;
    }
  }
  
  public static function getIncludePath()
  {
    return self::$includePath;
  }
}
