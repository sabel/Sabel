<?php

/**
 * Processor_Form
 *
 * @category   Addon
 * @package    addon.form
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Form_Processor extends Sabel_Bus_Processor
{
  const SESSION_KEY = "sbl_forms";
  const SES_TIMEOUT = 300;
  
  private
    $forms   = array(),
    $unityId = "";
  
  public function execute($bus)
  {
    $this->extract("request", "session", "controller");
    
    $forms = $this->session->read(self::SESSION_KEY);
    if ($forms === null) $forms = array();
    
    $controller = $this->controller;
    $controller->setAttribute("form", $this);
    $action = $bus->get("destination")->getAction();
    if (!$controller->hasMethod($action)) return;
    
    $annot = $controller->getReflection()->getMethodAnnotation($action, "unity");
    if (!isset($annot[0][0])) return;
    
    $this->unityId = $annot[0][0];
    $token = $this->request->getToken()->getValue();
    $timeouts = $this->session->getTimeouts();
    
    if (!empty($token)) {
      $seskey = $this->unityId . "_" . $token;
      if (isset($forms[$seskey])) {
        $form = unserialize($forms[$seskey]);
        $this->session->write($seskey, "", self::SES_TIMEOUT);
        
        if ($this->request->isPost()) {
          $this->applyPostValues($form)->unsetErrors();
        }
        
        $forms[$seskey] = $form;
        $controller->setAttribute($form->getFormName(), $form);
      }
      
      unset($timeouts[$seskey]);
    }
    
    foreach (array_keys($timeouts) as $k) unset($forms[$k]);
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
    
    if ($this->unityId === "") {
      $form = new Form_Object($model, $name);
      $this->controller->setAttribute($name, $form);
    } else {
      $token = $this->request->getToken()->createValue();
      $form = new Form_Object($model, $name, $token);
      $seskey = $this->unityId . "_" . $token;
      $this->forms[$seskey] = $form;
      $this->session->write($seskey, "", self::SES_TIMEOUT);
      $this->controller->setAttribute($name, $form);
    }
    
    return $form;
  }
  
  public function clear()
  {
    $token = $this->request->getToken()->getValue();
    
    if (!empty($token)) {
      $seskey = $this->unityId . "_" . $token;
      $this->session->delete($seskey);
      unset($this->forms[$seskey]);
    }
  }
  
  public function shutdown($bus)
  {
    foreach ($this->forms as $seskey => &$form) {
      if ($form instanceof Form_Object) {
        $form = serialize($form);
      }
    }
    
    $this->session->write(self::SESSION_KEY, $this->forms);
  }
  
  public function applyPostValues($form)
  {
    $values = $this->request->fetchPostValues();
    if (empty($values)) return $form;
    
    $model     = $form->getModel();
    $allowCols = $form->getAllowColumns();
    $mdlName   = $model->getName();
    
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
