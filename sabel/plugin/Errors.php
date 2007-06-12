<?php

class Sabel_Plugin_Errors extends Sabel_Plugin_Base
{
  const MAX_STACK_SIZE = 5;
  
  const ERROR_KEY = "errors";
  const STACK_KEY = "stack";
  
  private $storage = null;
  
  public function onBeforeAction()
  {
    $storage = $this->storage = Sabel_Context::getStorage();
    $current = $this->controller->getRequest()->__toString();
    $errors  = $storage->read(self::ERROR_KEY);
    
    if (is_array($errors)) {
      if ($this->isErrorPage($current, $errors)) {
        Sabel_View::assign(self::ERROR_KEY, $errors["messages"]);
      } else {
        $storage->delete(self::ERROR_KEY);
      }
    }
    
    $this->pushStack($current);
  }
  
  public function onRedirect()
  {
    if (($messages = $this->controller->errors) === null) return;
    
    $storage = $this->storage;
    $stack   = $storage->read(self::STACK_KEY);
    $index   = count($stack) - 2;
    
    $storage->write(self::ERROR_KEY, array("submitUrl" => $stack[$index],
                                           "messages"  => $messages));
  }
  
  private function isErrorPage($url, $errors)
  {
    return (isset($errors["submitUrl"]) && $errors["submitUrl"] === $url);
  }
  
  private function pushStack($url)
  {
    $storage = $this->storage;
    $stack   = $storage->read(self::STACK_KEY);
    
    if (is_array($stack)) {
      $stack[] = $url;
      if (count($stack) > self::MAX_STACK_SIZE) array_shift($stack);
    } else {
      $stack = array();
      $stack[] = $url;
    }
    
    $storage->write(self::STACK_KEY, $stack);
  }
}
