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
  
  private $storage = null;
  
  public function resetErrors()
  {
    $storage = $this->storage = Sabel_Context::getStorage();
    $storage->delete(self::ERROR_KEY);
    $storage->delete(self::STACK_KEY);
  }
  
  public function onBeforeAction()
  {
    $storage = $this->storage = Sabel_Context::getStorage();
    $current = $this->controller->getRequest()->__toString();
    $errors  = $storage->read(self::ERROR_KEY);
    
    if (is_array($errors)) {
      if ($this->isErrorPage($current, $errors)) {
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
    
    $storage = $this->storage;
    $stack   = $storage->read(self::STACK_KEY);
    $index   = count($stack) - 2;
    $values  = $this->controller->getRequest()->fetchPostValues();
    
    $storage->write(self::ERROR_KEY, array("submitUrl" => $stack[$index],
                                           "messages"  => $messages,
                                           "values"    => $values));
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
