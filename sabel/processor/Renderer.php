<?php

/**
 * Sabel_Processor_Renderer
 *
 * @category   Processor
 * @package    org.sabel.processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Processor_Renderer implements Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $response = $bus->get("response");
    $response->outputHeaderIfRedirectedThenExit();
    
    // @todo use instance of Sabel_View. don't use static.
    $result = Sabel_View::renderDefault($response);
    
    $bus->set("result", $result);
  }
}
