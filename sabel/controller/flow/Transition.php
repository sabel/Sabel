<?php

/**
 * Sabel_Controller_Flow_Transition
 *
 * @category   Flow
 * @package    org.sabel.controller
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Controller_Flow_Transition
{
  private $event = "";
  
  // transit to
  private $activity = null;
  
  public function __construct($event, $activity)
  {
    $this->event = $event;
    $this->activity = $activity;
  }
  
  public function getEventName()
  {
    return $this->event;
  }
  
  public function getActivity()
  {
    return $this->activity;
  }
}
