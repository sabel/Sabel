<?php

/**
 * Processor_I18n
 *
 * @category   Plugin
 * @package    org.sabel.controller.executer
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_I18n extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $type = Sabel_I18n_Gettext::SABEL;

    // $type = Sabel_I18n_Gettext::GETTEXT;
    // $type = Sabel_I18n_Gettext::PHP_GETTEXT;

    Sabel_I18n_Gettext::getInstance()->init($type);
  }
}
