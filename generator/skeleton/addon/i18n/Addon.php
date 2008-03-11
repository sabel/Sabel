<?php

/**
 * I18n_Addon
 *
 * @category   Addon
 * @package    addon.i18n
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class I18n_Addon extends Sabel_Object
{
  public function execute($bus)
  {
    $languages = $bus->get("request")->getHttpHeader("accept-language");
    Sabel_I18n_Gettext::getInstance()->init($languages);
  }
}

function _($msgid)
{
  return Sabel_I18n_Sabel_Gettext::_($msgid);
}
