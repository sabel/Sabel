<?php

/**
 * Sabel_Annotation_Context
 *
 * @category   Annotation
 * @package    org.sabel.annotation
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Annotation_Context
{
  protected $name     = '';
  protected $contents = '';
  
  public function __construct($name, $contents)
  {
    $this->name     = $name;
    $this->contents = $contents;
  }
  
  /**
   * get an annotation name
   *
   * @param void
   * @return string $this->name;
   */
  public function getName()
  {
    return $this->name;
  }
  
  /**
   * get an annotation contents
   *
   * @param void
   * @return string $this->contents;
   */
  public function getContents()
  {
    return $this->contents;
  }
  
  protected function isInjection()
  {
    return ($this->getName() === 'injection');
  }
  
  public function createInjection()
  {
    if ($this->isInjection()) {
      $className = $this->getContents();
      return new $className();
    }
  }
}
