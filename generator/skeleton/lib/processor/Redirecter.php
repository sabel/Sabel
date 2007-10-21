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
class Processor_Redirecter extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $redirect = new Processor_Redirecter_Redirect($bus);
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
      
      $to = $redirect->getUrl();
      $this->response->location($host, $ignored . $to);
    }
  }
}