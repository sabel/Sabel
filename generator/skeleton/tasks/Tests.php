<?php

/**
 * Sakle TestCase
 *
 * @abstract
 * @category   Sakle
 * @package    org.sabel.sakle
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Tests extends Sabel_Sakle_Task
{
  public function initialize()
  {
    define("ENVIRONMENT", $this->getEnvironment());
    
    if (ENVIRONMENT === PRODUCTION) {
      error_reporting(0);
    } else {
      error_reporting(E_ALL|E_STRICT);
    }
  }
  
  protected function getEnvironment()
  {
    if (Sabel_Console::hasOption("e", $this->arguments)) {
      $opts = Sabel_Console::getOption("e", $this->arguments);
      if (($env = environment($opts[0])) === null) {
        $this->error("invalid environment.");
        exit;
      } else {
        return $env;
      }
    } else {
      return TEST;
    }
  }
}
