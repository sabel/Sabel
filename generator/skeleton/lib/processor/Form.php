<?php

/**
 * Processor_Form
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Form extends Sabel_Bus_Processor
{
  const SESSION_KEY = "form";
  
  private
    $forms  = array(),
    $formId = null;
    
  private
    $controller = null,
    $response   = null,
    $storage    = null;
  
  public function execute($bus)
  {
    $this->storage = $bus->get("storage");
    $this->request = $bus->get("request");
    
    $this->forms = $this->storage->read(self::SESSION_KEY);
    
    $action = $bus->get("destination")->getAction();
    $controller = $this->controller = $bus->get("controller");
    $this->response = $controller->getResponse();
    
    $controller->setAttribute("form", $this);
    
    if (!$controller->hasMethod($action)) {
      return $this->delete();
    }
    
    $annot = $controller->getReflection()->getMethodAnnotation($action, "unity");
    
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
      
      foreach (array_keys($this->forms) as $key) {
        if ($formId !== $key) $this->delete($key);
      }
    }
  }
  
  public function create($model)
  {
    if ($model instanceof Sabel_DB_Abstract_Model) {
      $mdlName = $model->getName();
    } else {
      $mdlName = $model;
    }
    
    $string = new Sabel_Util_String($mdlName);
    $name   = $string->lcfirst($mdlName) . "Form";
    $formId = $this->formId;
    
    if ($formId === null) {
      $form = new Processor_Form_Object($model);
    } elseif (isset($this->forms[$formId][$name])) {
      $form = $this->forms[$formId][$name];
    } else {
      $form = new Processor_Form_Object($model);
      $this->forms[$formId][$name] = $form;
    }
    
    $this->response->setResponse($name, $form);
    return $form;
  }
  
  public function getManipulator($formName)
  {
    $forms  = $this->forms;
    $formId = $this->formId;
    
    if (isset($forms[$formId][$formName])) {
      $form = $forms[$formId][$formName];
      return new Manipulator($form->getModel());
    } else {
      return null;
    }
  }
  
  public function delete($formId = null)
  {
    if ($formId === null) {
      $this->forms = array();
    } else {
      unset($this->forms[$formId]);
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
      $model = $this->createModel($form->getModel());
      $forms[$name] = new Processor_Form_Object($model);
    }
    
    $this->forms[$formId] = $forms;
  }
  
  private function createModel($model)
  {
    $values  = $this->request->fetchPostValues();
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
      if ($column->isBool() && !isset($values["{$mdlName}::{$colName}"])) {
        $model->$colName = false;
      }
    }
    
    return $model;
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
