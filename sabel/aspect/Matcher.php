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
    
    foreach ($pointcuts as $p) {
      switch ($p) {
        case ($p->hasMethod() && $p->getMethod() === $method):
          $matches->add($p->getName(), $p->getAspect());
          break;
        case ($p->hasMethodRegex() && preg_match('/'.$p->getMethodRegex().'/', $method)):
          $matches->add($p->getName(), $p->getAspect());
          break;
      }
    }
    
    return $matches;
  }
}

class Sabel_Aspect_Matches implements Iterator
{
  protected $size = 0;
  protected $position = 0;
  protected $matches = array();
  
  public function add($name, $aspect)
  {
    $this->matches[$name] = $aspect;
  }
  
  public function matched($name)
  {
    return (isset($this->matches[$name]));
  }
  
  public function current()
  {
    $matches = array_values($this->matches);
    return $matches[$this->position];
  }
  
  public function rewind()
  {
    $this->position = 0;
    $this->size = count($this->position);
  }
  
  public function valid()
  {
    return ($this->position < $this->size);
  }
  
  public function next()
  {
    $this->position++;
  }
  
  public function key()
  {
    return $this->position;
  }
}