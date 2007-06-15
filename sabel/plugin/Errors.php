<?php

/**
 * Sabel_Plugin_Errors
 *
 * @category   Plugin
 * @package    org.sabel.plugin
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Plugin_Errors extends Sabel_Plugin_Base
{
  const MAX_STACK_SIZE = 5;
  
  const ERROR_KEY = "errors";
  const STACK_KEY = "stack";
  
  public function resetErrors()
  {
    $storage = $this->getSessionStorage();
    $storage->delete(self::ERROR_KEY);
    $storage->delete(self::STACK_KEY);
  }
  
  public function onBeforeAction()
  {
    $storage = $this->getSessionStorage();
    $current = $this->controller->getRequest()->__toString();
    $errors  = $storage->read(self::ERROR_KEY);
    
    if (is_array($errors)) {
      if ($current === $errors["submitUri"]) {
        $this->controller->hasErrors = true;
        $this->controller->errorValues = $errors["values"];
        Sabel_View::assign(self::ERROR_KEY, $errors["messages"]);
        Sabel_View::assignByArray($errors["values"]);
      } else {
        $storage->delete(self::ERROR_KEY);
      }
    }
    
    $this->pushStack($current);
  }
  
  public function onRedirect()
  {
    if (($messages = $this->controller->errors) === null) return;
    
    $storage = $this->getSessionStorage();
    $stack   = $storage->read(self::STACK_KEY);
    $index   = count($stack) - 2;
    $values  = $this->controller->getRequest()->fetchPostValues();
    
    $storage->write(self::ERROR_KEY, array("submitUri" => $stack[$index],
                                           "messages"  => $messages,
                                           "values"    => $values));
  }
  
  private function pushStack($uri)
  {
    $storage = $this->getSessionStorage();
    $stack   = $storage->read(self::STACK_KEY);
    
    if (is_array($stack)) {
      $stack[] = $uri;
      if (count($stack) > self::MAX_STACK_SIZE) array_shift($stack);
    } else {
      $stack = array($uri);
    }
    
    $storage->write(self::STACK_KEY, $stack);
  }
  
  private function getSessionStorage()
  {
    static $storage = null;
    
    if ($storage === null) {
      return $storage = Sabel_Context::getStorage();
    } else {
      return $storage;
    }
  }
}
