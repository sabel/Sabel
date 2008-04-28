<#php

/**
 * @flow continuation <?php echo $formName ?> 
 */
class <?= $controllerName ?> extends Sabel_Controller_Page
{
  public function index()
  {
    $this->lists();
    $this->view->setName("lists");
  }
  
  public function lists()
  {
    $this->paginate = new Paginate("<?= $mdlName ?>");
    $this->paginate->uri = "a: lists";
    $this->paginate->setOrderColumn(<?= $orderColumns ?>);
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
    $this-><?php echo $formName ?> = new Form_Object($model);
  }
  
  /**
   * @flow next confirmEdit
   */
  public function correctEdit()
  {
    $this->view->setName("prepareEdit");
  }
  
  /**
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
  
  protected function _confirm($tplName)
  {
    $this->form->applyPostValues($this-><?php echo $formName ?>);
    if (!$this-><?= $formName ?>->validate()) {
      $this->view->setName($tplName);
    }
  }
  
  protected function _save()
  {
    $this-><?= $formName ?>->getModel()->save();
    $this->request->setGetValue("token", null);
    $this->redirect->to("a: lists");
  }
}
