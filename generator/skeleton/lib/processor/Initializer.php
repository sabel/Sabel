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
    $libDb = RUN_BASE . DS . "lib" . DS . "db" . DS;
    
    Sabel::fileUsing($libDb . "utility.php", true);
    Sabel::fileUsing($libDb . "validators.php", true);
    
    Sabel_DB_Config::initialize();
    $this->storage->start();
    $this->controller->response->pageTitle = "Sabel";
  }

  /**
   * strip whitespace from post values.
   *
   */
  private function trim($request)
  {
    if ($values = $request->fetchPostValues()) {
      foreach ($values as &$value) {
        if ($value === null || is_array($value)) continue;
        // for multibyte.
        // $result = mb_trim($value);
        $result = trim($value);
        $value  = ($result === "") ? null : $result;
      }
      $request->setPostValues($values);
    }
  }
}
