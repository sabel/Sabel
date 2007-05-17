<?php

class Sabel_Controller_Plugin_Flow extends Sabel_Controller_Page_Plugin
{
  public function flow()
  {
    $storage = $this->controller->getStorage();
    return $storage->read("flow");
  }
  
  /*
  public function onBeforeAction()
  {
    $storage = $this->controller->getStorage();
    $action  = $this->controller->getAction();
    
    if (!($flow = $storage->read("flow"))) {
      $flow = new FlowConfig();
      $flow->configure();
    }
    
    if ($flow->isInFlow()) {
      if ($flow->canTransitTo($action)) {
        // アクション実行
        // 結果がtrueか遷移先が正しければ遷移し、リダイレクト発生
        $flow->transit($action);
      } elseif ($flow->isCurrent($action)) {
        echo " no transit ";
      } else {
        echo "許可されていないリクエストです";
        exit;
      }
      $storage->write("flow", $flow);
    } else {
      if ($flow->isEntryActivity($action)) {
        $flow->start($action);
        $storage->write("flow", $flow);
      } elseif ($flow->isEndActivity($action)) {
        $storage->delete("flow");
        echo "end";
      } else {
        echo "許可されていないリクエストです";
        exit;
      }
    }
    
    $this->flow = $flow;
    
    $GLOBALS["flow"] = $flow;
  }
  
  */
}
