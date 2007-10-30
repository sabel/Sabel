<?php

/**
 * Processor_Acl
 *
 * @version    1.0
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Acl_Processor extends Sabel_Bus_Processor
{
  const DENY_ACTION = "notFound";
  const RULE_DENY   = "deny";
  const RULE_ALLOW  = "allow";
  
  private $user = null;
  
  /**
   * execute an action.
   * overwrite parent executeAction method.
   */
  public function execute($bus)
  {
    list ($m, $c, $a) = $this->destination->toArray();
    $this->user = new Acl_User();
    
    if ($this->storage->has("acl_user")) {
      $this->user->restore($this->storage->read("acl_user"));
    }
    
    $this->controller->setAttribute("user", $this->user);
    if (defined("BATCH")) return true;
    
    if ($c !== "public" && !$this->user->isAuthenticated($m)) {
      $this->response->notFound();
      $bus->getList()->find("executer")->unlink();
    }
  }
  
  public function shutdown()
  {
    $this->storage->write("acl_user", $this->user->toArray());
  }
}
