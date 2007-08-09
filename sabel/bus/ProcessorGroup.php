<?php

/**
 * Sabel_Bus_ProcessorGroup
 *
 * @category   Bus
 * @package    org.sabel.bus
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Bus_ProcessorGroup implements Sabel_Bus_Processor
{
  private $processors = array();
  private $processor  = null;
  
  /** 
   * implements interface from Sabel_Bus_Processor
   *
   * @param Sabel_Bus $bus
   */
  public function execute($bus)
  {
    $processorList = $this->processor->getFirst();
    
    while ($processorList !== null) {
      $processorList->get()->execute($bus);
      $processorList = $processorList->next();
    }
  }
  
  public function add($name, $processor)
  {
    $processor = new Sabel_Bus_ProcessorList($name, $processor);
    $processor->addListener($this);
    
    $this->processor         = $processor;
    $this->processors[$name] = $processor;
    
    return $this;
  }
  
  public function get($name)
  {
    return $this->processors[$name];
  }
  
  public function getProcessorList()
  {
    return $this->processor;
  }
  
  public function update($name, $processor)
  {
    $processor->addListener($this);
    $this->processors[$name] = $processor;
  }
}
