<?php

/**
 * Form_Addon
 *
 * @version   1.0
 * @category  Addon
 * @package   addon.form
 * @author    Mori Reo <mori.reo@gmail.com>
 * @author    Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright 2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Form_Addon extends Sabel_Object
{
  const VERSION = 1;
  
  public function version()
  {
    return self::VERSION;
  }
  
  public function load()
  {
    return true;
  }
  
  public function loadProcessor($bus)
  {
    $form = new Form_Processor("form");
    $bus->getProcessorList()->insertNext("controller", "form", $form);
  }
}
