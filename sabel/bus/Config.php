<?php

/**
 * Sakle
 *
 * @abstract
 * @category   Bus
 * @package    org.sabel.bus
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Bus_Config extends Sabel_Object
{
  protected $processors = array();
  protected $configs    = array();
  
  public function getProcessors()
  {
    return $this->processors;
  }
  
  public function getConfigs()
  {
    return $this->configs;
  }
}
