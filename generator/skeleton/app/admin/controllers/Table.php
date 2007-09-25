<?php

class Admin_Controllers_Table extends Sabel_Controller_Page
{
  public function view()
  {
    $tblName = $this->param;
    $mdlName = convert_to_modelname($tblName);
    $manip = new Manipulator($mdlName);
    $this->schema = $manip->getModel()->getSchema();
    
    if ($models = $manip->select()) {
      foreach ($models as $model) {
        $pkey = $model->getPrimaryKey();
        if (is_array($pkey)) {
          $value = array();
          foreach ($pkey as $key) $value[] = $model->$key;
          $model->_key = implode(":", $value);
        } else {
          $model->_key = $model->$pkey;
        }
      }
      $this->columns = $models[0]->getColumnNames();
    }
    
    $this->models = $models;
    $this->assignForm($manip);
  }
  
  public function insert()
  {
    if ($manip = $this->modelForm->validate($this->param)) {
      try {
        $manip->save();
      } catch (Sabel_DB_Exception $e) {
        $this->errors = $e->getMessage();
      }
    }
    
    $tblName = convert_to_tablename($this->param);
    $this->redirect->to("a: view, param: $tblName");
  }
  
  public function delete()
  {
    $mdlName = $this->param;
    if ($this->delkey !== null) {
      if (strpos($this->delkey[0], ":") === false) {
        $manip = new Manipulator($mdlName);
        $pkey  = $manip->getModel()->getPrimaryKey();
        $manip->setCondition(new Condition($pkey, $this->delkey, IN));
        $manip->delete();
      } else {
        // @todo
      }
    }
    
    $tblName = convert_to_tablename($mdlName);
    $this->redirect->to("a: view, param: $tblName");
  }
  
  public function prepareEdit()
  {
    $mdlName = $this->param;
    if (strpos($this->key, ":") === false) {
      $manip = new Manipulator($mdlName);
      $model = $manip->selectOne($this->key);
      $this->schema = $model->getSchema();
      $this->storage->write("editKey", $this->key);
      $this->assignForm($manip);
    } else {
      // @todo
    }
  }
  
  public function edit()
  {
    $id = $this->storage->read("editKey");
    if ($target = $this->modelForm->validate($this->param, null, $id)) {
      try {
        $source = new Manipulator($this->param);
        // @todo if join-key
        $source = $source->selectOne($id);
        $this->unsetUniqueValues($source, $target->getModel());
        $target->save();
      } catch (Sabel_DB_Exception $e) {
        $this->errors = $e->getMessage();
      }
    }
    
    $tblName = convert_to_tablename($this->param);
    $this->redirect->to("a: view, param: $tblName");
  }
  
  private function assignForm($manip)
  {
    $model = $manip->getModel();
    $mdlName = $model->getName();
    $mdlName{0} = strtolower($mdlName{0});
    $formName = $mdlName . "Form";
    $this->$formName = new Form($model);
    $this->formName = $formName;
  }
  
  private function unsetUniqueValues($source, $target)
  {
    if ($uniques = $source->getSchema()->getUniques()) {
      foreach ($uniques as $unique) {
        if (count($unique) === 1) {
          $key = $unique[0];
          if ($target->$key === $source->$key) {
            $target->unsetValue($key);
          }
        } else {
          // @todo
        }
      }
    }
    
    return $target;
  }
}
