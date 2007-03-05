<?php

/**
 * Sabel_Aspect_Matcher
 *
 * @category   Aspect
 * @package    org.sabel.aspect
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Aspect_Matcher
{
  protected $pointcuts = array();
  
  public function add($pointcut)
  {
    $this->pointcuts[] = $pointcut;
  }
  
  public function findMatch($conditions)
  {
    $pointcuts = $this->pointcuts;
    $matches = new Sabel_Aspect_Matches();
    
    $method = $conditions['method'];
    dump($method);
    foreach ($pointcuts as $p) {
      switch ($p) {
        case ($p->hasMethod() && $p->getMethod() === $method):
          $matches->add($p->getName(), $p->getAspect());
          break;
        case ($p->hasMethods()):
          foreach ($p->getMethods() as $pcMethod) {
            
            if ($pcMethod === $method) {
              $matches->add($p->getName(), $p->getAspect());
            }
          }
          break;
        case ($p->hasMethodRegex() && preg_match('/'.$p->getMethodRegex().'/', $method)):
          $matches->add($p->getName(), $p->getAspect());
          break;
      }
    }
    
    return $matches;
  }
}