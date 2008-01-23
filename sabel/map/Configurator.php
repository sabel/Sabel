<?php

/**
 * Map Configurator
 * useful interface of Sabel_Map_Candidate
 *
 * @category   Map
 * @package    org.sabel.map
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Map_Configurator
{
  protected static $candidates = array();
  
  public static function addCandidate($name, $uri, $options = array())
  {
    $candidate = new Sabel_Map_Candidate($name);
    self::$candidates[$name] = $candidate;
    $candidate->route($uri)->setOptions($options);
  }
  
  public static function getCandidate($name)
  {
    if (isset(self::$candidates[$name])) {
      return self::$candidates[$name];
    } else {
      return false;
    }
  }
  
  public static function getCandidates()
  {
    return self::$candidates;
  }
  
  public static function setCandidates($candidates)
  {
    self::$candidates = $candidates;
  }
}
