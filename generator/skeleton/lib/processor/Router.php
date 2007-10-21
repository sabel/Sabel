<?php

/**
 * Processor_Router
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Router extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $this->router = new Sabel_Router_Map();
    $this->destination = $this->router->route($this->request);
    
    $candidate = $this->router->getCandidate();
    
    foreach ($candidate->getElements() as $element) {
      $this->request->setParameterValue($element->name, $element->variable);
    }
  }
}
