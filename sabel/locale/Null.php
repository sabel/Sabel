<?php

/**
 * Sabel_Locale_Null
 *
 * @category   locale
 * @package    org.sabel.locale
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Locale_Null implements Sabel_Locale
{
  public function getServer()
  {
    return null;
  }

  public function getBrowser()
  {
    return null;
  }
}