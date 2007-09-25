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
class ModelForm extends Sabel_Object
{
  protected
    $request    = null,
    $controller = null;
  
  public function __construct($bus)
  {
    $this->request    = $bus->get("request");
    $this->controller = $bus->get("controller");
  }
  
  public function validate($model, $ignores = null, $id = null)
  {
    $values = $this->request->fetchPostValues();
    $model  = $this->createModel($model, $values, $id);
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
  
  public function createModel($model, $values, $id = null)
  {
    if (is_string($model)) {
      $model = MODEL($model);
    }
    
    if ($id !== null) {
      // @todo if join-key
      $manip = new Manipulator($model);
      $model = $manip->selectOne($id);
    }
    
    $mdlName = $model->getName();
    
    foreach ($values as $key => $value) {
      if (strpos($key, "::") === false) continue;
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
    
    foreach ($model->getSchema()->getColumns() as $colName => $column) {
      $key = $mdlName . "::" . $colName;
      if ($column->isBool() && !isset($values[$key])) {
        $model->$colName = false;
      }
    }
    
    return $model;
  }
}
