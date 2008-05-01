<#php

/**
 * @flow continuation <?php echo $formName ?> 
 */
class <?php echo $controllerName ?> extends Sabel_Controller_Page
{
  public function index()
  {
    $this->lists();
    $this->view->setName("lists");
  }
  
  public function lists()
  {
    $this->paginate = new Paginate("<?php echo $mdlName ?>");
    $this->paginate->setOrderColumn(<?php echo $orderColumns ?>);
    $this->paginate->build(10, $this->request->fetchGetValues());
  }
  
  /**
   * @flow start
   * @flow next confirmCreate
   */
  public function prepareCreate()
  {
    $this-><?php echo $formName ?> = new Form_Object("<?= $mdlName ?>");
  }
  
  /**
   * @flow next confirmCreate
   */
  public function correctCreate()
  {
    $this->view->setName("prepareCreate");
  }
  
  /**
   * @httpMethod post
   * @flow next correctCreate create
   */
  public function confirmCreate()
  {
    $this->_confirm("prepareCreate");
  }
  
  /**
   * @flow end
   */
  public function create()
  {
    $this->_save();
  }
  
  /**
   * @flow start
   * @flow next confirmEdit
   */
  public function prepareEdit()
  {
    $model = MODEL("<?= $mdlName ?>", $this->request->fetchGetValue("id"));
    if ($model->isSelected()) {
      $this-><?php echo $formName ?> = new Form_Object($model);
    } else {
      $this->response->notFound();
    }
  }
  
  /**
   * @flow next confirmEdit
   */
  public function correctEdit()
  {
    $this->view->setName("prepareEdit");
  }
  
  /**
   * @httpMethod post
   * @flow next correctEdit edit
   */
  public function confirmEdit()
  {
    $this->_confirm("prepareEdit");
  }
  
  /**
   * @flow end
   */
  public function edit()
  {
    $this->_save();
  }
  
  /**
   * @httpMethod post
   */
  public function confirmDelete()
  {
    $this->ids = $ids = $this->request->fetchPostValue("ids");
    
    if (!is_array($ids) || empty($ids)) {
      $this->redirect->to("a: lists");
    } else {
      $<?php echo lcfirst($mdlName) ?> = MODEL("<?php echo $mdlName ?>");
      $inCondition = Sabel_DB_Condition::create(Sabel_DB_Condition::IN, "id", $ids);
      $this->deleteItems = $<?php echo lcfirst($mdlName) ?>->select($inCondition);
    }
  }
  
  /**
   * @httpMethod post
   */
  public function doDelete()
  {
    $ids = $this->request->fetchPostValue("ids");
    
    if (is_array($ids) && !empty($ids)) {
      $<?php echo lcfirst($mdlName) ?> = MODEL("<?php echo $mdlName ?>");
      $inCondition = Sabel_DB_Condition::create(Sabel_DB_Condition::IN, "id", $ids);
      $<?php echo lcfirst($mdlName) ?>->delete($inCondition);
    }
    
    $this->redirect->to("a: lists");
  }
  
  protected function _confirm($tplName)
  {
    $this->form->applyPostValues($this-><?php echo $formName ?>);
    if (!$this-><?php echo $formName ?>->validate()) {
      $this->view->setName($tplName);
    }
  }
  
  protected function _save()
  {
    $this-><?php echo $formName ?>->getModel()->save();
    $this->request->setGetValue("token", null);
    $this->redirect->to("a: lists");
  }
}
