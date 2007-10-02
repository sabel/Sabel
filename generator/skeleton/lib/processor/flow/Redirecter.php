<?php

/**
 * Processor_Redirecter
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Flow_Redirecter extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $controller = $bus->get("controller");
    
    $redirect = new Processor_Redirecter_Redirect($bus);
    $controller->setAttribute("redirect", $redirect);
    
    return new Sabel_Bus_ProcessorCallback($this, "onRedirect", "executer");
  }
  
  public function onRedirect($bus)
  {
    $controller = $bus->get("controller");
    $redirect = $controller->getAttribute("redirect");
    
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
      
      $token = $controller->getAttribute("token");
      if ($redirect->hasParameters()) {
        $to = $redirect->getUrl() . "&token={$token}";
      } else {
        $to = $redirect->getUrl() . "?token={$token}";
      }
      
      $bus->get("response")->location($host, $ignored . $to);
      
      return true;
    }
    
    return false; 
  }
}
