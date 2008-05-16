<#php

/**
 * @flow continuation <?php echo $formName ?> deleteIds
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
    $<?php echo lcfirst($mdlName) ?> = MODEL("<?= $mdlName ?>", $this->request->fetchGetValue("<?php echo $primaryColumn ?>"));
    if ($<?php echo lcfirst($mdlName) ?>->isSelected()) {
      $this-><?php echo $formName ?> = new Form_Object($<?php echo lcfirst($mdlName) ?>);
    } else {
      $this->response->getStatus()->setCode(Sabel_Response::NOT_FOUND);
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
   *
   * @flow start
   * @flow next doDelete
   */
  public function confirmDelete()
  {
    $ids = $this->request->fetchPostValue("ids");
    
    if (!is_array($ids) || empty($ids)) {
      $this->redirect->to("a: lists");
    } else {
      $this->deleteIds = $ids;
      $<?php echo lcfirst($mdlName) ?> = MODEL("<?php echo $mdlName ?>");
      $inCondition = Sabel_DB_Condition::create(Sabel_DB_Condition::IN, "<?php echo $primaryColumn ?>", $ids);
      $this->deleteItems = $<?php echo lcfirst($mdlName) ?>->select($inCondition);
    }
  }
  
  /**
   * @httpMethod post
   *
   * @flow end
   */
  public function doDelete()
  {
    $<?php echo lcfirst($mdlName) ?> = MODEL("<?php echo $mdlName ?>");
    $inCondition = Sabel_DB_Condition::create(Sabel_DB_Condition::IN, "<?php echo $primaryColumn ?>", $this->deleteIds);
    $<?php echo lcfirst($mdlName) ?>->delete($inCondition);
    
    $this->request->setPostValue("token", null);
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
