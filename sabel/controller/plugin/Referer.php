<?php

/**
 * referer
 *
 * @category   Plugin
 * @package    org.sabel.controller.plugin
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Controller_Plugin_Referer extends Sabel_Controller_Page_Plugin
{
  /**
   * check refer
   *
   * @param string $message message for log
   */
  public function checkReferer($validURIs)
  {
    $host = Sabel_Environment::get("http_host");
    $ref  = Sabel_Environment::get("http_referer");
    
    $patternAbsoluteURI = '%http://' . $host . $validURIs[0]. '%';
    return (bool) preg_match($patternAbsoluteURI, $ref);
  }
}
