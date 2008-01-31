<?php

/**
 * Sabel_Router_Map
 *
 * @category   Router
 * @package    org.sabel.router
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Router_Map extends Sabel_Object
{
  private $candidate = null;
  
  public function route(Sabel_Request $request, Sabel_Map_Configurator $config)
  {
    $config->configure();
    
    foreach ($config->build() as $candidate) {
      if ($candidate->evaluate($request)) {
        Sabel_Context::getContext()->setCandidate($candidate);
        $this->candidate = $candidate;
        return new Sabel_Router_Destination($candidate->getDestination());
      }
    }
    
    // @todo use specific exception
    throw new Sabel_Exception_Runtime("");
  }
  
  public function getCandidate()
  {
    return $this->candidate;
  }
}
