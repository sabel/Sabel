<?php

class Sabel_Plugin_Errors extends Sabel_Plugin_Base
{
  const MAX_STACK_SIZE = 5;
  
  private $storage = null;
  
  public function onBeforeAction()
  {
    $storage = $this->storage = Sabel_Context::getStorage();
    $current = $this->controller->getRequest()->__toString();
    $errors  = $storage->read("errors");
    
    if (is_array($errors)) {
      if ($this->isErrorPage($current, $errors)) {
        Sabel_View::assign("errors", $errors["messages"]);
      } else {
        $storage->delete("errors");
      }
    }
    
    $this->pushStack($current);
  }
  
  public function onRedirect()
  {
    if (($messages = $this->controller->errors) === null) return;
    
    $storage = $this->storage;
    $stack   = $storage->read("stack");
    $index   = count($stack) - 2;
    
    $storage->write("errors", array("submitUrl" => $stack[$index],
                                    "messages"  => $messages));
  }
  
  private function isErrorPage($url, $errors)
  {
    return (isset($errors["submitUrl"]) && $errors["submitUrl"] === $url);
  }
  
  private function pushStack($url)
  {
    $storage = $this->storage;
    $stack   = $storage->read("stack");
    
    if (is_array($stack)) {
      $stack[] = $url;
      if (count($stack) > self::MAX_STACK_SIZE) array_shift($stack);
    } else {
      $stack = array();
      $stack[] = $url;
    }
    
    $storage->write("stack", $stack);
  }
}

