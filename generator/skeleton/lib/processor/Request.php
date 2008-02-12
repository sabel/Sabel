<?php

/**
 * Processor_Request
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2002-2006 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Request extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    if (!$bus->has("request")) {
      $builder = new Sabel_Request_Builder();
      $request = new Sabel_Request_Object();
      $builder->build($request);
      $bus->set("request", $request);
    }
    
    if (!$bus->has("session")) {
      $bus->set("session", Sabel_Session_PHP::create());
    }
    
    $bus->get("session")->start();
  }
}
