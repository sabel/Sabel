<?php

/**
 * Map Configurator
 * useful interface of Sabel_Map_Candidate
 *
 * @abstract
 * @category   Map
 * @package    org.sabel.map
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Map_Configurator implements Sabel_Config
{
  protected static $candidates = array();
  
  protected $routes = array();
  
  public function route($name)
  {
    return $this->routes[] = new Sabel_Map_Config_Route($name);
  }
  
  public function build()
  {
    $candidates = array();
    
    foreach ($this->routes as $route) {
      $name = $route->getName();
      $candidate = new Sabel_Map_Candidate($name);
      $candidate->route($route);
      $candidates[$name] = $candidate;
    }
    
    return self::$candidates = $candidates;
  }
  
  public static function getCandidateByName($name)
  {
    if (isset(self::$candidates[$name])) {
      return self::$candidates[$name];
    } else {
      return null;
    }
  }
  
  public function clearCandidates()
  {
    $candidates = self::$candidates;
    self::$candidates = array();
    
    return $candidates;
  }
}
