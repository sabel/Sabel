<?php

/**
 * Processor_Form
 *
 * @category   Addon
 * @package    addon.form
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Form_Processor extends Sabel_Bus_Processor
{
  const SESSION_KEY = "sbl_forms";
  const SES_TIMEOUT = 300;
  
  private
    $forms   = array(),
    $token   = null,
    $unityId = null;
    
  public function execute($bus)
  {
    $action = $bus->get("destination")->getAction();
    $storage = $bus->get("storage");
    $controller = $bus->get("controller");
    $controller->setAttribute("form", $controller); // @todo
    
    $forms = $storage->read(self::SESSION_KEY);
    if ($forms === null) $forms = array();
    $this->forms = $forms;
    
    $controller->setAttribute("form", $this);
    if (!$controller->hasMethod($action)) return;
    
    $reflection = $controller->getReflection();
    $annot = $reflection->getMethodAnnotation($action, "unity");
    
    if ($annot === null || !isset($annot[0][0])) return;
    
    $this->unityId = $unityId = $annot[0][0];
    $this->token = $token = $this->request->getToken()->getValue();
    $timeouts = $storage->getTimeouts();
    
    if (!realempty($token)) {
      $seskey = $unityId . "_" . $token;
      if (isset($forms[$seskey])) {
        $form = $forms[$seskey];
        $storage->write($seskey, "", self::SES_TIMEOUT);
        if ($this->request->isPost()) {
          $this->applyPostValues($form)->unsetErrors();
        }
        
        $controller->setAttribute($form->getFormName(), $form);
      }
      
      unset($timeouts[$seskey]);
    }
    
    foreach ($timeouts as $k => $v) {
      unset($forms[$k]);
    }
    
    $this->forms = $forms;
  }
  
  public function create($model, $as = null)
  {
    if ($as !== null) {
      $name = $as;
    } elseif (is_model($model)) {
      $name = $model->getName();
    } elseif (is_string($model)) {
      $name = $model;
    } else {
      $message = "invalid argument(1) type. "
               . "must be a string or instance of model.";
               
      throw new Sabel_Exception_InvalidArgument($message);
    }
    
    $name = lcfirst($name) . "Form";
    
    if ($this->unityId === null) {
      $form = new Form_Object($model, $name);
      $this->controller->setAttribute($name, $form);
    } else {
      $token = $this->request->getToken()->createValue();
      $form = new Form_Object($model, $name, $token);
      $seskey = $this->unityId . "_" . $token;
      $this->forms[$seskey] = $form;
      $this->storage->write($seskey, "", self::SES_TIMEOUT);
      $this->controller->setAttribute($name, $form);
    }
    
    return $form;
  }
  
  public function clear()
  {
    $unityId = $this->unityId;
    $token = $this->request->getToken()->getValue();
    
    if ($unityId !== null || !realempty($token)) {
      unset($this->forms[$unityId . "_" . $token]);
    }
  }
  
  public function shutdown($bus)
  {
    $bus->get("storage")->write(self::SESSION_KEY, $this->forms);
  }
  
  public function applyPostValues($form)
  {
    $values = $this->request->fetchPostValues();
    if (empty($values)) return $form;
    
    $model     = $form->getModel();
    $mdlName   = $model->getName();
    $allowCols = $form->getAllowColumns();
    
    foreach ($values as $key => $value) {
      if (strpos($key, "::") === false) continue;
      list ($name, $colName) = explode("::", $key);
      if ($name !== $mdlName || !in_array($colName, $allowCols)) continue;
      
      if ($colName === "datetime") {
        foreach ($value as $key => $date) {
          if ($this->isCompleteDateValues($date)) {
            if (!isset($date["second"])) {
              $date["second"] = "00";
            }
            
            $model->$key = $date["year"]   . "-"
                         . $date["month"]  . "-"
                         . $date["day"]    . " "
                         . $date["hour"]   . ":"
                         . $date["minute"] . ":"
                         . $date["second"];
          } else {
            $model->$key = null;
          }
        }
      } elseif ($colName === "date") {
        foreach ($value as $key => $date) {
          if ($this->isCompleteDateValues($date, false)) {
            $date = "{$date["year"]}-{$date["month"]}-{$date["day"]}";
            $model->$key = $date;
          } else {
            $model->$key = null;
          }
        }
      } else {
        $model->$colName = $value;
      }
    }
    
    foreach ($model->getColumns() as $colName => $column) {
      if (!$column->isBool()) continue;
      $key = "{$mdlName}::{$colName}";
      if (isset($values[$key])) {
        $model->$colName = ($values[$key] === "1");
      }
    }
    
    return $form;
  }
  
  private function isCompleteDateValues($values, $isDatetime = true)
  {
    $keys = array("year", "month", "day");
    
    if ($isDatetime) {
      $keys = array_merge($keys, array("hour", "minute", "second"));
    }
    
    foreach ($keys as $key) {
      if ($values[$key] === "") return false;
    }
    
    return true;
  }
}
