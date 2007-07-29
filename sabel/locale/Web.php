<?php

/**
 * Sabel_Locale_Web
 *
 * @category   locale
 * @package    org.sabel.locale
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Locale_Web implements Sabel_Locale
{
  protected $server  = null;
  protected $browser = null;
  
  public function __construct()
  {
    $this->server  = new Sabel_Locale_Server();
    $this->browser = new Sabel_Locale_Browser();
  }
  
  public function getServer()
  {
    return $this->server;
  }
  
  public function getBrowser()
  {
    return $this->browser;
  }
}
