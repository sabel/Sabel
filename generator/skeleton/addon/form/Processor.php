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
  const SESSION_KEY = "forms";
  
  private
    $forms  = array(),
    $formId = null;
  
  public function execute($bus)
  {
    $this->forms = $this->storage->read(self::SESSION_KEY);
    $action = $this->destination->getAction();
    $controller = $this->controller;
    $this->response = $controller->getResponse();
    $controller->setAttribute("form", $this);
    
    if (!$controller->hasMethod($action)) {
      return $this->delete();
    }
    
    $reflection = $controller->getReflection();
    $annot = $reflection->getMethodAnnotation($action, "unity");
    
    if ($annot === null) {
      $this->delete();
    } else {
      $formId = $annot[0][0];
      $this->formId = $formId;
      if (isset($this->forms[$formId])) {
        if ($this->request->isPost()) {
          $this->postProcess();
        }
        $this->restoreForms();
      }
      
      if (isset($this->forms)) {
        foreach (array_keys($this->forms) as $key) {
          if ($formId !== $key) $this->delete($key);
        }
      }
    }
  }
  
  public function create($model, $as = null)
  {
    if ($as !== null) {
      $mdlName = $as;
    } elseif ($model instanceof Sabel_DB_Abstract_Model) {
      $mdlName = $model->getName();
    } elseif (is_string($model)) {
      $mdlName = $model;
    } else {
      $message = "invalid argument(1) type. "
               . "must be a string or instance of Sabel_DB_Abstract_Model.";
               
      throw new Sabel_Exception_InvalidArgument($message);
    }
    
    $name = lcfirst($mdlName) . "Form";
    $formId = $this->formId;
    
    if ($formId === null) {
      $form = new Form_Object($model);
    } elseif (isset($this->forms[$formId][$name])) {
      $form = $this->forms[$formId][$name];
    } else {
      $form = new Form_Object($model);
      $this->forms[$formId][$name] = $form;
    }
    
    $this->response->setResponse($name, $form);
    $this->controller->setAttribute($name, $form);
    
    return $form;
  }
  
  public function getForms()
  {
    $formId = $this->formId;
    
    if ($formId !== null && isset($this->forms[$formId])) {
      return $this->forms[$formId];
    } else {
      return null;
    }
  }
  
  public function delete($formId = null)
  {
    if ($formId !== null) {
      unset($this->forms[$formId]);
    } elseif ($this->formId !== null) {
      unset($this->forms[$this->formId]);
    } else {
      $this->forms = array();
    }
  }
  
  public function shutdown($bus)
  {
    $this->storage->write(self::SESSION_KEY, $this->forms);
  }
  
  private function postProcess()
  {
    $formId = $this->formId;
    $forms  = $this->forms[$formId];
    
    foreach ($forms as $name => $form) {
      $form = $this->setPostValues($form);
      $form->unsetErrors();
      $forms[$name] = $form;
    }
    
    $this->forms[$formId] = $forms;
  }
  
  public function setPostValues($form)
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
  
  private function restoreForms()
  {
    $forms = $this->forms[$this->formId];
    foreach ($forms as $name => $form) {
      $this->controller->setAttribute($name, $form);
      $this->setResponse($form, $name);
    }
  }
  
  private function setResponse($form, $name)
  {
    $this->response->setResponse($name, $form);
  }
}
