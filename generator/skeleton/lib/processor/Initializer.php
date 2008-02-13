<?php

/**
 * Processor_Initializer
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Initializer extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    Sabel_DB_Config::initialize($bus->getConfig("database"));
    //Sabel::fileUsing(RUN_BASE . DS . LIB_DIR_NAME . DS . "db" . DS . "utility" . PHP_SUFFIX, true);
    
    // start session.
    $bus->get("session")->start();
    
    // default page title.
    $bus->get("response")->setResponse("pageTitle", "Sabel");
    
    // $this->trim();
  }

  /**
   * strip whitespace from post values.
   */
  private function trim()
  {
    if (!$this->request->isPost()) return;
    
    $func = (extension_loaded("mbstring")) ? "mb_trim" : "trim";
    
    if ($values = $this->request->fetchPostValues()) {
      foreach ($values as &$value) {
        if ($value === null || is_array($value)) continue;
        $result = $func($value);
        $value  = ($result === "") ? null : $result;
      }
      
      $this->request->setPostValues($values);
    }
  }
}
