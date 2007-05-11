<?php

/**
 * Sabel_Controller_Flow_Activity
 *
 * @category   Flow
 * @package    org.sabel.controller
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Controller_Flow_Activity
{
  private $transitions = array();
  private $name = "";
  
  public function __construct($name)
  {
    $this->name = $name;
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  public function addTransition($transition, $activity)
  {
    $transition = new Sabel_Controller_Flow_Transition($transition, $activity);
    $this->transitions[$transition->getEventName()] = $transition;
    return $this;
  }
  
  public function hasEvent($event)
  {
    return (isset($this->transitions[$event]));
  }
  
  public function getNextActivity($event)
  {
    return $this->transitions[$event]->getActivity();
  }
}
