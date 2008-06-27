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
    $this->defineEnvironmentByOption("e", TEST);
    
    if (ENVIRONMENT === PRODUCTION) {
      error_reporting(0);
    } else {
      error_reporting(E_ALL|E_STRICT);
    }
  }
}
