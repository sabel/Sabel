<?php

/**
 * Form_Addon
 *
 * @version   1.0
 * @category  Addon
 * @package   addon.form
 * @author    Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright 2002-2006 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Form_Addon extends Sabel_Object
{
  public function execute($bus)
  {
    $form = new Form_Processor("form");
    $bus->getProcessorList()->insertNext("controller", "form", $form);
  }
}
