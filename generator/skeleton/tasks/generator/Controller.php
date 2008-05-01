<#php

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
  
  public function prepareCreate()
  {
    $this-><?php echo $formName ?> = new Form_Object("<?= $mdlName ?>");
    $this->token = $this->session->getClientId();
  }
  
  public function create()
  {
    $this->_save("prepareCreate", true);
  }
  
  public function prepareEdit()
  {
    $this-><?= $primaryColumn ?> = $this->request->fetchGetValue("<?php echo $primaryColumn ?>");
    $<?php echo lcfirst($mdlName) ?> = MODEL("<?= $mdlName ?>", $this-><?php echo $primaryColumn ?>);
    if ($<?php echo lcfirst($mdlName) ?>->isSelected()) {
      $this-><?php echo $formName ?> = new Form_Object($<?php echo lcfirst($mdlName) ?>);
      $this->token = $this->session->getClientId();
    } else {
      $this->response->notFound();
    }
  }
  
  public function edit()
  {
    $this->_save("prepareEdit", false);
  }
  
  public function confirmDelete()
  {
    $this->ids = $ids = $this->request->fetchPostValue("ids");
    
    if (!is_array($ids) || empty($ids)) {
      $this->redirect->to("a: lists");
    } else {
      $<?php echo lcfirst($mdlName) ?> = MODEL("<?php echo $mdlName ?>");
      $inCondition = Sabel_DB_Condition::create(Sabel_DB_Condition::IN, "<?php echo $primaryColumn ?>", $ids);
      $this->deleteItems = $<?php echo lcfirst($mdlName) ?>->select($inCondition);
    }
  }
  
  public function doDelete()
  {
    $ids = $this->request->fetchPostValue("ids");
    
    if (is_array($ids) && !empty($ids)) {
      $<?php echo lcfirst($mdlName) ?> = MODEL("<?php echo $mdlName ?>");
      $inCondition = Sabel_DB_Condition::create(Sabel_DB_Condition::IN, "<?php echo $primaryColumn ?>", $ids);
      $<?php echo lcfirst($mdlName) ?>->delete($inCondition);
    }
    
    $this->request->setPostValue("token", null);
    $this->redirect->to("a: lists");
  }
  
  protected function _save($tplName, $isCreate = true)
  {
    $this->token = $this->session->getClientId();
    if ($this->request->fetchPostValue("token") !== $this->token) {
      return $this->response->badRequest();
    }
    
    if ($isCreate) {
      $<?php echo $formName ?> = new Form_Object("<?php echo $mdlName ?>");
    } else {
      $this-><?php echo $primaryColumn ?> = $this->request->fetchPostValue("<?php echo $primaryColumn ?>");
      $<?php echo lcfirst($mdlName) ?> = MODEL("<?php echo $mdlName ?>", $this-><?php echo $primaryColumn ?>);
      if ($<?php echo lcfirst($mdlName) ?>->isSelected()) {
        $<?php echo $formName ?> = new Form_Object($<?php echo lcfirst($mdlName) ?>);
      } else {
        return $this->response->notFound();
      }
    }
    
    $this->form->applyPostValues($<?php echo $formName ?>, array(<?php echo $allowColumns ?>));
    
    if ($<?php echo $formName ?>->validate()) {
      $<?php echo $formName ?>->getModel()->save();
      $this->request->setPostValue("token", null);
      $this->redirect->to("a: lists");
    } else {
      $this-><?php echo $formName ?> = $<?php echo $formName ?>;
      $this->view->setName($tplName);
    }
  }
}
