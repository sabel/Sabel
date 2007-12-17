<?php

/**
 * Processor_Initializer
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Initializer extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $libdb = RUN_BASE . DS . LIB_DIR_NAME . DS . "db";
    $files = array("utility", "validators", "maxmin");
    
    foreach ($files as $file) {
      Sabel::fileUsing($libdb . DS . $file . PHP_SUFFIX, true);
    }
    
    Sabel_DB_Config::initialize();
    
    // creates or resumes session.
    $this->storage->start();
    
    // default page title.
    $this->controller->getResponse()->setResponse("pageTitle", "Sabel");
    
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
