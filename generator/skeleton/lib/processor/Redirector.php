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
    $redirect = new Processor_Redirector_Redirect($bus);
    $this->controller->setAttribute("redirect", $redirect);
  }
  
  public function shutdown($bus)
  {
    $redirect = $this->controller->getAttribute("redirect");
    
    if ($redirect->isRedirected()) {
      if (isset($_SERVER["HTTP_HOST"])) {
        $host = $_SERVER["HTTP_HOST"];
      } else {
        $host = "localhost";
      }
      
      $ignored = "";
      
      if (defined("URI_IGNORE")) {
        $ignored = ltrim($_SERVER["SCRIPT_NAME"], "/") . "/";
      }
      
      $token = $this->request->getToken()->getValue();
      
      if (realempty($token)) {
        $to = $redirect->getUrl();
      } elseif ($redirect->hasParameters()) {
        $to = $redirect->getUrl() . "&token={$token}";
      } else {
        $to = $redirect->getUrl() . "?token={$token}";
      }
      
      $this->response->location($host, $ignored . $to);
    }
  }
}
