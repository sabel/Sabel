<#php

class <?php echo $controllerName ?> extends Sabel_Controller_Page
{
  /**
   * @httpMethod post
   *
   * @check <?php echo $emailColumn ?> required
   * @check password required
   */
  public function doLogin()
  {
    $this-><?php echo $emailColumn ?> = $this->request->fetchPostValue("<?php echo $emailColumn ?>");
    $this->password = $this->request->fetchPostValue("password");

    if ($this->validator->hasError()) {
      $this->view->setName("prepare");
    } else {
      $model = MODEL("<?php echo $mdlName ?>");
      $model->setCondition("<?php echo $emailColumn ?>", $this-><?php echo $emailColumn ?>);
      $model->setCondition("password", $this->password);
      $<?php echo lcfirst($mdlName) ?> = $model->selectOne();
      
      if ($<?php echo lcfirst($mdlName) ?>->isSelected()) {
        $this->aclUser->authenticate("user");
        $this->aclUser-><?php echo $primaryColumn ?> = $<?php echo lcfirst($mdlName) ?>-><?php echo $primaryColumn ?>;
        // $this->redirect->to("c: toController, a: toAction");
      } else {
        $this->errors = array("Invalid Mail-Address or Password.");
        $this->view->setName("prepare");
      }
    }
  }
}
