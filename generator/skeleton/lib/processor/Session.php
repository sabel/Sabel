<?php

/**
 * Processor_Session
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Session extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    if (!$bus->has("session")) {
      $bus->set("session", Sabel_Session_PHP::create());
    }
  }
  
  public function shutdown($bus)
  {
    $session = $bus->get("session");
    
    if (!$session->isCookieEnabled() && !$session instanceof Sabel_Session_PHP) {
      output_add_rewrite_var($session->getName(), $session->getId());
    }
  }
}
