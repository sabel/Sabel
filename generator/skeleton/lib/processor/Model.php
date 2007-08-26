<?php

/**
 * Processor_Model
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Model extends Sabel_Bus_Processor
{
  protected
    $bus     = null,
    $request = null;
  
  public function execute($bus)
  {
    $this->bus     = $bus;
    $this->request = $bus->get("request");
    $controller    = $bus->get("controller");
    
    $controller->setAttribute("model", $this);
  }
  
  public function validate($model, $ignores = null)
  {
    $values = $this->request->fetchPostValues();
    $model  = $this->createModel($model, $values);
    
    if ($ignores === null) {
      $ignores = array();
    } elseif (is_string($ignores)) {
      $ignores = array($ignores);
    }
    
    $validator = new Sabel_DB_Validator($model);
    $errors = $validator->validate($ignores);
    
    if ($errors) {
      $this->bus->get("controller")->errors = $errors;
      return false;
    } else {
      return new Manipulator($model);
    }
  }
  
  public function createModel($model, $values)
  {
    if (is_string($model)) {
      $model = MODEL($model);
    }
    
    $mdlName = $model->getModelName();
    
    foreach ($values as $key => $value) {
      if (strpos($key, "::") !== false) {
        list ($name, $colName) = explode("::", $key);
        if ($name !== $mdlName) continue;
        
        if ($colName === "datetime") {
          foreach ($value as $key => $date) {
            if (!isset($date["second"])) {
              $date["second"] = "00";
            }
            
            $datetime = $date["year"]   . "-"
                      . $date["month"]  . "-"
                      . $date["day"]    . " "
                      . $date["hour"]   . ":"
                      . $date["minute"] . ":"
                      . $date["second"];
                      
            $model->$key = $datetime;
          }
        } elseif ($colName === "date") {
          foreach ($value as $key => $date) {
            $date = "{$date["year"]}-{$date["month"]}-{$date["day"]}";
            $model->$key = $date;
          }
        } else {
          $model->$colName = $value;
        }
      }
    }
    
    return $model;
  }
}
