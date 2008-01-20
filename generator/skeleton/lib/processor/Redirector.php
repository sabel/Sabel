<?php

/**
 * Processor_Redirector
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Redirector extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $redirect = new Processor_Redirector_Redirect();
    $this->controller->setAttribute("redirect", $redirect);
  }
  
  public function shutdown($bus)
  {
    $redirect = $this->controller->getAttribute("redirect");
    
    if ($redirect->isRedirected()) {
      if (defined("URI_IGNORE")) {
        $ignored = ltrim($_SERVER["SCRIPT_NAME"], "/") . "/";
      } else {
        $ignored = "";
      }
      
      $token = $this->request->getToken()->getValue();
      
      if (realempty($token)) {
        $to = $redirect->getUrl();
      } elseif ($redirect->hasParameters()) {
        $to = $redirect->getUrl() . "&token={$token}";
      } else {
        $to = $redirect->getUrl() . "?token={$token}";
      }
      
      $serverName = Sabel_Environment::get("SERVER_NAME");
      $this->response->location($serverName, $ignored . $to);
    }
  }
}
