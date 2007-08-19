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
class Sabel_Bus_ProcessorGroup extends Sabel_Bus_Processor
{
  private $processors = array();
  private $processor  = null;
  
  private $controller = null;
    
  /** 
   * implements interface from Sabel_Bus_Processor
   *
   * @param Sabel_Bus $bus
   */
  public function execute($bus)
  {
    $processorList = $this->processor->getFirst();
    
    if ($this->controller !== null) {
      while ($processorList !== null) {
        $result = $this->controller->execute($processorList->get(), $bus);
        
        switch ($result) {
          case true:
            $processorList->get()->execute($bus);
            $processorList = $processorList->next();
            break;
          case false:
            break 2;
          default:
            break 2;
        }
      }
    } else {
      while ($processorList !== null) {
        $processorList->get()->execute($bus);
        $processorList = $processorList->next();
      }
    }
  }
  
  public function add($processor)
  {
    $processor = new Sabel_Bus_ProcessorList($processor);
    $processor->addListener($this);
    
    $this->processor = $processor;
    $this->processors[$processor->name] = $processor;
    
    return $this;
  }
  
  public function addController($controller)
  {
    $this->controller = $controller;
  }
  
  public function get($name)
  {
    return $this->processors[$name];
  }
  
  public function getProcessorList()
  {
    return $this->processor;
  }
  
  public function update($processor)
  {
    $processor->addListener($this);
    $this->processors[$processor->name] = $processor;
  }
}
