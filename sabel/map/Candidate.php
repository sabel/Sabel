<?php

/**
 * uri candidate
 *
 * @category   Map
 * @package    org.sabel.map
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Map_Candidate
{
  const MODULE     = "module";
  const CONTROLLER = "controller";
  const ACTION     = "action";
  
  /**
   * @var string
   */
  protected $name = "";
  
  /**
   * @var array
   */
  protected $uriParameters = "";
  
  /**
   * @var array
   */
  protected $destination = array("module" => "", "controller" => "", "action" => "");
  
  public function __construct($name, $uriParameters)
  {
    $this->name = $name;
    $this->uriParameters = $uriParameters;
    
    foreach ($uriParameters as $name => $value) {
      if (in_array($name, array("module", "controller", "action"), true)) {
        $this->destination[$name] = $value;
      }
    }
  }
  
  public function getUriParameters()
  {
    return $this->uriParameters;
  }
  
  public function getDestination()
  {
    return new Sabel_Map_Destination($this->destination);
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  public function uri($param = "")
  {
    if ($param === null) $param = "";
    
    if (!is_string($param)) {
      $message = "uri parameter must be a string.";
      throw new Sabel_Exception_InvalidArgument($message);
    }
    
    $parameters = array();
    if ($param !== "") {
      foreach (explode(",", $param) as $param) {
        list ($key, $val) = array_map("trim", explode(":", $param));
        if ($key === "n") $key = "name";
        $parameters[$key] = $val;
      }
    }
    
    $name = (isset($parameters["name"])) ? $parameters["name"] : $this->name;
    $route = Sabel_Map_Configurator::getRoute($name);
    return $route->createUrl($parameters, $this->uriParameters);
  }
}
