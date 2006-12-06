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
  const VARIABLE_MARK = ":";
  protected static $candidates = array();
  
  public static function addCandidate($name, $uri, $options = null)
  {
    $c = Sabel::load('Sabel_Map_Candidate', $name);
    self::$candidates[$name] = $c;
    $elements = explode('/', $uri);
    
    foreach ($elements as $element) {
      if (stripos($element, self::VARIABLE_MARK) === 0) {
        $variableName = ltrim($element, self::VARIABLE_MARK);
        if ($variableName === 'module') {
          $c->addElement($variableName, Sabel_Map_Candidate::MODULE);
        } elseif ($variableName === 'controller') {
          $c->addElement($variableName, Sabel_Map_Candidate::CONTROLLER);
        } elseif ($variableName === 'action') {
          $c->addElement($variableName, Sabel_Map_Candidate::ACTION);
        } else {
          $c->addElement($variableName);
        }
      } else {
        $c->addElement($element, Sabel_Map_Candidate::CONSTANT);
      }
    }
    
    if (isset($options['default'])) {
      foreach ($options['default'] as $key => $default) {
        $key = ltrim($key, ':');
        if ($default === null) {
          $c->setOmittable($key);
        } else {
          $c->setOmittable($key);
          $c->setDefaultValue($key, $default);
        }
      }
    }
    
    if (isset($options['module'])) {
      $c->setModule($options['module']);
    }
    
    if (isset($options['controller'])) {
      $c->setController($options['controller']);
    }
    
    if (isset($options['action'])) {
      $c->setAction($options['action']);
    }
  }
  
  public static function getCandidate($name)
  {
    if (isset(self::$candidates[$name]))
      return self::$candidates[$name];
  }
  
  public static function getCandidates()
  {
    return self::$candidates;
  }
}