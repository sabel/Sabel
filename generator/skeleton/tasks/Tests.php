<?php

/**
 * Sakle TestCase
 *
 * @abstract
 * @category   Sakle
 * @package    org.sabel.sakle
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Tests extends Sabel_Sakle_Task
{
  public function initialize()
  {
    $environment = $this->getEnvironment();
    
    if ($environment === null) {
      throw new Exception("environment must be specified.");
    } else {
      define ("ENVIRONMENT", $environment);
    }
    
    if (ENVIRONMENT === PRODUCTION) {
      error_reporting(0);
    } else {
      error_reporting(E_ALL|E_STRICT);
    }
  }
  
  protected function getEnvironment()
  {
    if (Sabel_Command::hasOption("e", $this->arguments)) {
      $env = Sabel_Command::getOption("e", $this->arguments, true);
      return environment($env);
    } else {
      return TEST;
    }
  }
}
