<?php

/**
 * Processor_Initializer
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Initializer extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    Sabel_Db_Config::initialize($bus->getConfig("database"));
    
    if (!defined("SBL_BATCH") && ($session = $bus->get("session")) !== null) {
      $session->start();
      l("START SESSION: " . $session->getName() . "=" . $session->getId());
    }
    
    // default page title.
    $bus->get("response")->setResponse("pageTitle", "Sabel");
    
    // $request = $bus->get("request");
    // if ($request->isPost()) $this->trim($request);
  }

  /**
   * strip whitespace from post values.
   */
  private function trim($request)
  {
    $func = (extension_loaded("mbstring")) ? "mb_trim" : "trim";
    
    if ($values = $request->fetchPostValues()) {
      foreach ($values as &$value) {
        if ($value === null || is_array($value)) continue;
        $result = $func($value);
        $value  = ($result === "") ? null : $result;
      }
      
      $request->setPostValues($values);
    }
  }
}
