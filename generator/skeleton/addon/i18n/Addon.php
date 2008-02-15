<?php

/**
 * I18n_Addon
 *
 * @version    1.0
 * @category   Addon
 * @package    addon.i18n
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class I18n_Addon extends Sabel_Object
{
  public function execute($bus)
  {
    Sabel_I18n_Gettext::getInstance()->init();
  }
}

function _($msgid)
{
  return Sabel_I18n_Sabel_Gettext::_($msgid);
}
