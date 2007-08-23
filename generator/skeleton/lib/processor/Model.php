<?php

/**
 * Processor_Model
 *
 * @category   Processor
 * @package    processor
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Model extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $request    = $bus->get("request");
    $controller = $bus->get("controller");
    
    if ($request->isPost()) {
      $models = array();
      $values = $request->fetchPostValues();
      foreach ($values as $key => $value) {
        if (strpos($key, "::") !== false) {
          list ($mdlName, $colName) = explode("::", $key);
          if (!isset($models[$mdlName])) {
            $models[$mdlName] = MODEL($mdlName);
          }
          
          $models[$mdlName]->$colName = $value;
        }
      }
      
      foreach ($models as $mdlName => $model) {
        $controller->setAttribute($mdlName, $model);
      }
    }
  }
}
