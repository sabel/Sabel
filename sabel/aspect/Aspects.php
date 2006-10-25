<?php

/**
 * Sabel_Aspect_Aspects
 *
 * @category   Aspect
 * @package    org.sabel.aspect
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Aspect_Aspects
{
  protected $aspects = array();
  
  protected static $matcher  = null;
  protected static $instance = null;
  
  protected function __construct()
  {
    self::$matcher = new Sabel_Aspect_Matcher();
  }
  
  public static function singleton()
  {
    if (!self::$instance) self::$instance = new self();
    return self::$instance;
  }
  
  public function addPointcut($pointcut)
  {
    self::$matcher->add($pointcut);
    $className = $pointcut->getName();
    $this->aspects[] = new $className();
  }
  
  public function add($aspect)
  {
    $this->aspects[] = $aspect;
    self::$matcher->add($aspect->pointcut());
  }
  
  public function findMatch($conditions)
  {
    return self::$matcher->findMatch($conditions);
  }
}