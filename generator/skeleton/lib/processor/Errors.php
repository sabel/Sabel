<?php

/**
 * Processor_Errors
 *
 * @category   Processor
 * @package    processor
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Errors extends Sabel_Bus_Processor
{
  const MAX_STACK_SIZE = 5;
  
  const ERROR_KEY = "errors";
  const STACK_KEY = "stack";
  
  private $ignoreUrls = array();
  
  private $storage = null;
  private $request = null;
  
  public function resetErrors()
  {
    $this->storage->delete(self::ERROR_KEY);
    $this->storage->delete(self::STACK_KEY);
  }
  
  public function execute($bus)
  {
    $this->storage    = $storage    = $bus->get("storage");
    $this->request    = $request    = $bus->get("request");
    $this->controller = $controller = $bus->get("controller");
    
    $current = $request->getUri()->__toString();
    $errors  = $storage->read(self::ERROR_KEY);
    
    $ignores = array();
    $ignores[] = (!in_array($current, $this->ignoreUrls));
    $ignores[] = (!$request->isTypeOf("css"));
    $ignores[] = (!$request->isTypeOf("js"));
    
    if (is_array($errors)) {
      if ($current === $errors["submitUri"]) {
        $this->controller->hasErrors = true;
        $this->controller->errorValues = $errors["values"];
        Sabel_View::assign(self::ERROR_KEY, $errors["messages"]);
        Sabel_View::assignByArray($errors["values"]);
      } else {
        if (!in_array(false, $ignores)) {
          $storage->delete(self::ERROR_KEY);
        }
      }
    }
    
    if (!in_array(false, $ignores)) {
      $this->pushStack($current);
    }
    
    return new Sabel_Bus_ProcessorCallback($this, "onRedirect", "redirecter");
  }
  
  public function onRedirect()
  {
    if (($messages = $this->controller->errors) === null) return;
    
    $stack  = $this->storage->read(self::STACK_KEY);
    $index  = count($stack) - 2;
    $values = $this->request->fetchPostValues();
    
    $this->storage->write(self::ERROR_KEY, array("submitUri" => $stack[$index],
                                                 "messages"  => $messages,
                                                 "values"    => $values));
  }
  
  private function pushStack($uri)
  {
    $stack = $this->storage->read(self::STACK_KEY);
    
    if (is_array($stack)) {
      $stack[] = $uri;
      if (count($stack) > self::MAX_STACK_SIZE) array_shift($stack);
    } else {
      $stack = array($uri);
    }
    
    $this->storage->write(self::STACK_KEY, $stack);
  }
}
