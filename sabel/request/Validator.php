<?php

/**
 * Sabel_Request_Validator
 *
 * @category   Request
 * @package    org.sabel.request
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Request_Validator extends Sabel_Object
{
  protected $validators = array();
  protected $displayErrors = array();
  protected $suites = array();
  protected $values = array();
  protected $errors = array();
  
  public function set($name, $checker)
  {
    $this->validators[$name] = $checker;
  }
  
  public function hasError()
  {
    return !empty($this->errors);
  }
  
  public function getErrors()
  {
    return $this->errors;
  }
  
  public function getSuites()
  {
    return $this->suites;
  }
  
  public function validate($values)
  {
    $this->values = $values;
    $validators = $this->validators;
    
    $errors = array();
    $suites = $this->getSuites();
    
    foreach ($validators as $inputName => $checkers) {
      if (strpos($inputName, ":") !== false) {
        $exp = explode(":", $inputName, 2);
        $validators[$exp[0]] = $checkers;
        $this->displayNames[$exp[0]] = $exp[1];
        unset($validators[$inputName]);
      }
    }
    
    foreach (array_keys($validators) as $inputName) {
      if (!isset($values[$inputName])) $values[$inputName] = null;
    }
    
    foreach ($values as $name => $value) {
      if (!isset($validators[$name])) continue;
      
      $checker = $validators[$name];
      if (is_string($checker)) $checker = array($checker);
      
      foreach ($checker as $method) {
        if (isset($suites[$method])) {
          foreach ($suites[$method] as $check) {
            $message = $this->$check($name, $value);
            if ($message !== null) $errors[] = $message;
          }
        } elseif (strpos($method, "(") !== false) {
          preg_match('/\((.+)\)/', $method, $matches);
          $args = array_map("trim", explode(",", $matches[1]));
          array_unshift($args, $name, $value);
          $method = substr($method, 0, strlen($matches[0]));
          $message = call_user_func_array(array($this, $method), $args);
          if ($message !== null) $errors[] = $message;
        } else {
          $message = $this->$method($name, $value);
          if ($message !== null) $errors[] = $message;
        }
      }
    }
    
    $this->errors = $errors;
    
    return empty($errors);
  }
}
