<?php

class Parameters
{
  protected $parameter;
  protected $parameters;
  protected $parsedParameters;
  
  public function __construct($parameters)
  {
    $this->parameters = $parameters;
    if (!empty($parameters)) $this->parse();
  }
  
  /**
   * Parsing URL request
   *
   * @param void
   * @return void
   */
  protected function parse()
  {
    $parameters = split("\?", $this->parameters);
    
    $this->parameter = (empty($parameters[0])) ? null : $parameters[0];
    $separate = split("&", $parameters[1]);
    $sets = array();
    foreach ($separate as $key => $val) {
      $tmp = split("=", $val);
      if (empty($tmp[1])) throw new Exception('value is empty');
      $sets[$tmp[0]] = $tmp[1];
    }
    
    $this->parsedParameters =& $sets;
  }
  
  public function getParameter()
  {
    return $this->parameter;
  }
  
  public function __get($key)
  {
    return $this->parsedParameters[$key];
  }
  
  public function get($key)
  {
    return $this->parsedParameters[$key];
  }
}

?>