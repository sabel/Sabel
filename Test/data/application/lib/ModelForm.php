<?php

/**
 * ModelForm
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class ModelForm
{
  protected
    $request    = null,
    $controller = null;
  
  public function __construct($bus)
  {
    $this->request    = $bus->get("request");
    $this->controller = $bus->get("controller");
  }
  
  public function validate($model, $ignores = null)
  {
    $values = $this->request->fetchPostValues();
    $model  = $this->createModel($model, $values);
    $manip  = new Manipulator($model);
    $errors = $manip->validate($ignores);
    
    if (empty($errors)) {
      return $manip;
    } else {
      if ($this->controller->hasAttribute("errors")) {
        $e = $this->controller->errors;
        $this->controller->errors = array_merge($e, $errors);
      } else {
        $this->controller->errors = $errors;
      }
      
      return false;
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
