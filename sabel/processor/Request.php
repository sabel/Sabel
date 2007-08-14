<?php

/**
 * Sabel_Processor_Request
 *
 * @category   Processor
 * @package    org.sabel.processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Processor_Request extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $request = $bus->get("request");
    
    if ($request === null) {
      $builder = new Sabel_Request_Builder();
      $request = new Sabel_Request_Object();
      $builder->build($request);
      $bus->set("request", $request);
    }
    
    $storage = $bus->get("storage");
    
    if ($storage === null) {
      $storage = Sabel_Storage_Session::create();
    } else {
      $storage = $storage;
    }
    
    $bus->set("storage", $storage);
  }
}
