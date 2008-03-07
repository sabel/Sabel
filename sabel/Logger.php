<?php

/**
 * Sabel_Logger
 *
 * @category   Logger
 * @package    org.sabel.logger
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Logger extends Sabel_Object
{
  private static $instance = null;
  
  protected $logger   = null;
  protected $messages = array();
  
  public static function create()
  {
    if (self::$instance === null) {
      self::$instance = new self();
      register_shutdown_function(array(self::$instance, "output"));
    }
    
    return self::$instance;
  }
  
  public function setLogger(Sabel_Logger_Interface $logger)
  {
    $this->logger = $logger;
  }
  
  public function write($text, $level = SBL_LOG_INFO, $identifier = "default")
  {
    if ((SBL_LOG_LEVEL & $level) === 0) return;
    
    $message = array("time" => now(), "level" => $level, "message" => $text);
    
    if (array_key_exists($identifier, $this->messages)) {
      $this->messages[$identifier][] = $message;
    } else {
      $this->messages[$identifier] = array($message);
    }
  }
  
  public function getMessages()
  {
    return $this->messages;
  }
  
  public function output()
  {
    $logger = ($this->logger === null) ? new Sabel_Logger_File() : $this->logger;
    $logger->output($this->messages);
  }
}
