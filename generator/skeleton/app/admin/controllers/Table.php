<?php

class Admin_Controllers_Table extends Sabel_Controller_Page
{
  const LISTS_LIMIT = 5;
  
  public function show()
  {
    if (!$this->checkParameter()) return;
    
    $page = $this->request->fetchGetValue("page");
    $paginate = new Paginate(new Manipulator(convert_to_modelname($this->table)));
    $this->paginate = $paginate->build(self::LISTS_LIMIT, $page);
    $this->pagerUri = "a: show, param: {$this->table}";
    
    if ($this->paginate->count > 0) {
      $this->pkey = $this->paginate->results[0]->getPrimaryKey();
      $this->columns = $this->paginate->results[0]->getColumnNames();
    }
  }
  
  /**
   * @unity edit
   */
  public function edit()
  {
    if (!$this->checkParameter()) return;
    if (($id = $this->request->fetchGetValue("id")) === null) {
      $this->response->notFound();
    } elseif (!is_object($this->itemForm)) {
      $manip = new Manipulator(convert_to_modelname($this->table));
      $model = $manip->selectOne($id);
      if ($model->isSelected()) {
        $this->form->create($model, "item");
      } else {
        $this->response->notFound();
      }
    }
  }
  
  /**
   * @unity edit
   */
  public function doEdit()
  {
    if ($this->request->isPost()) {
      $model = $this->itemForm->getModel();
      $tblName = $model->getTableName();
      if ($this->itemForm->validate()) {
        $manip = new Manipulator($model);
        $manip->save();
        $this->redirect->to("a: show, param: $tblName");
      } else {
        $pkey  = $model->getPrimaryKey();
        $param = array("id" => $model->$pkey);
        $this->redirect->to("a: edit, param: $tblName", $param);
      }
    } else {
      $this->response->notFound();
    }
  }
  
  public function delete()
  {
    if (!$this->checkParameter()) return;
    if (($id = $this->request->fetchGetValue("id")) === null) {
      $this->response->notFound();
    } else {
      $manip = new Manipulator(convert_to_modelname($this->table));
      $manip->delete($id);
      $this->redirect->to("a: show, param: {$this->table}");
    }
  }
  
  private function checkParameter()
  {
    if (($table = $this->request->fetchParameterValue("param")) === null) {
      $this->response->notFound();
      return false;
    }
    
    $tables = Sabel_DB_Driver::createSchema()->getTableList();
    
    if (!in_array($table, $tables)) {
      $this->response->notFound();
      return false;
    }
    
    $this->table = $table;
    return true;
  }
}
