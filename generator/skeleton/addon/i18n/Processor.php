<?php

/**
 * I18n_Processor
 *
 * @version    1.0
 * @category   Addon
 * @package    addon.i18n
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class I18n_Processor extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $type = Sabel_I18n_Gettext::SABEL;
    // $type = Sabel_I18n_Gettext::GETTEXT;
    // $type = Sabel_I18n_Gettext::PHP_GETTEXT;
    
    Sabel_I18n_Gettext::getInstance()->init($type);
  }
}
