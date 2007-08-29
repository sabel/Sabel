<?php

/**
 * Processor_Renderer
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Renderer extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $request     = $bus->get("request");
    $response    = $bus->get("response");
    $destination = $bus->get("destination");
    
    $response->outputHeader();
    
    // @todo use instance of Sabel_View. don't use static.
    $result = Sabel_View::renderDefault($request, $response, $destination);
    $bus->set("result", $result);

    return true;
  }
}
