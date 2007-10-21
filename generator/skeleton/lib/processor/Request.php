<?php

/**
 * Processor_Request
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Request extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    if ($this->request === null) {
      $builder = new Sabel_Request_Builder();
      $request = new Sabel_Request_Object();
      $builder->build($request);
      $this->request = $request;
    }
    
    if ($this->storage === null) {
      $this->storage = Sabel_Storage_Session::create();
    }
  }
}
