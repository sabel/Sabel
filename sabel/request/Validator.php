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
    $validator = new Validator();
    
    $errors = array();
    $suites = $validator->getSuites();
    
    foreach ($values as $name => $value) {
      if (isset($validators[$name])) {
        $checker = $validators[$name];
        if (isset($suites[$checker])) {
          foreach ($suites[$checker] as $check) {
            $message = $validator->$check($name, $value);
            if ($message !== null) $errors[] = $message;
          }
        } else {
          $message = $validator->$checker($name, $value);
          if ($message !== null) $errors[] = $message;
        }
      }
    }
    
    return $this->errors = $errors;
  }
}
