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
  const TIMEOUT_KEY = "sbl_forms_timeout";
  const TIMEOUT_CNT = 15;
  
  private
    $forms   = array(),
    $token   = null,
    $unityId = null,
    $counts  = array();
    
  public function execute($bus)
  {
    $this->forms = $this->storage->read(self::SESSION_KEY);
    if ($this->form === null) $this->form = array();
    $this->counts = $this->storage->read(self::TIMEOUT_KEY);
    if ($this->counts === null) $this->counts = array();
    
    $action = $this->destination->getAction();
    $controller = $this->controller;
    $controller->setAttribute("form", $this);
    
    if (!$controller->hasMethod($action)) return;
    
    $reflection = $controller->getReflection();
    $annot = $reflection->getMethodAnnotation($action, "unity");
    
    if ($annot === null || !isset($annot[0][0])) return;
    
    $this->unityId = $unityId = $annot[0][0];
    $this->token = $token = $this->request->getToken()->getValue();
    
    $this->countUp();
    if (realempty($token)) return;
    
    $sesKey = $unityId . "_" . $token;
    if (isset($this->forms[$sesKey])) {
      $form = $this->forms[$sesKey];
      $this->counts[$sesKey] = 0;
      if ($this->request->isPost()) {
        $this->applyPostValues($form)->unsetErrors();
      }
      $controller->setAttribute($form->getFormName(), $form);
    }
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
      $this->forms[$this->unityId . "_" . $token]  = $form;
      $this->counts[$this->unityId . "_" . $token] = 0;
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
      unset($this->counts[$unityId . "_" . $token]);
    }
  }
  
  private function countUp()
  {
    if (!empty($this->counts)) {
      array_walk($this->counts, create_function('&$val', '++$val;'));
    }
    
    return $this->counts;
  }
  
  private function countDown()
  {
    if (!empty($this->counts)) {
      $func = create_function('&$val', 'if ($val > 0) --$val;');
      array_walk($this->counts, $func);
    }
    
    return $this->counts;
  }
  
  public function shutdown($bus)
  {
    $forms  = $this->forms;
    $counts = $this->counts;
    
    if ($bus->get("controller")->redirect->isRedirected()) {
      $counts = $this->countDown();
    } else {
      foreach ($counts as $k => $v) {
        if ($v >= self::TIMEOUT_CNT) {
          unset($forms[$k]);
          unset($counts[$k]);
        }
      }
    }
    
    $this->storage->write(self::SESSION_KEY, $forms);
    $this->storage->write(self::TIMEOUT_KEY, $counts);
  }
  
  public function applyPostValues($form)
  {
    $values = $this->request->fetchPostValues();
    if (empty($values)) return $form;
    
    $model   = $form->getModel();
    $mdlName = $model->getName();
    
    foreach ($values as $key => $value) {
      if (strpos($key, "::") === false) continue;
      list ($name, $colName) = explode("::", $key);
      if ($name !== $mdlName) continue;
      
      if ($colName === "datetime") {
        foreach ($value as $key => $date) {
          if ($this->isEmptyDateValues($date)) {
            $model->$key = null;
          } else {
            if (!isset($date["second"])) {
              $date["second"] = "00";
            }
            
            $model->$key = $date["year"]   . "-"
                         . $date["month"]  . "-"
                         . $date["day"]    . " "
                         . $date["hour"]   . ":"
                         . $date["minute"] . ":"
                         . $date["second"];
          }
        }
      } elseif ($colName === "date") {
        foreach ($value as $key => $date) {
          if ($this->isEmptyDateValues($date, false)) {
            $model->$key = null;
          } else {
            $date = "{$date["year"]}-{$date["month"]}-{$date["day"]}";
            $model->$key = $date;
          }
        }
      } else {
        $model->$colName = $value;
      }
    }
    
    foreach ($model->getSchema()->getColumns() as $colName => $column) {
      if (!$column->isBool()) continue;
      $key = "{$mdlName}::{$colName}";
      if (isset($values[$key])) {
        $model->$colName = ($values[$key] === "1");
      }
    }
    
    return $form;
  }
  
  public function setPostValues($form)
  {
    return $this->applyPostValues($form);
  }
  
  private function isEmptyDateValues($values, $isDatetime = true)
  {
    $keys = array("year", "month", "day");
    
    if ($isDatetime) {
      $keys = array_merge($keys, array("hour", "minute", "second"));
    }
    
    foreach ($keys as $key) {
      if ($values[$key] !== "") return false;
    }
    
    return true;
  }
}
