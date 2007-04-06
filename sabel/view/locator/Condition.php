<?php

/**
 * Sabel_View_Locator_Condition
 *
 * @category   View
 * @package    org.sabel.view
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_View_Locator_Condition
{
  private $candidate = null;
  private $actionDefault = false;
  
  private $partial = false;
  
  private $path, $name;
  
  public function __construct($actionDefault)
  {
    $this->actionDefault = $actionDefault;
  }
  
  public function setCandidate($candidate)
  {
    $this->candidate = $candidate;
  }
  
  public function getCandidate()
  {
    return $this->candidate;
  }
  
  public function setPartial($cond)
  {
    $this->partial = $cond;
  }
  
  public function getPartial()
  {
    return $this->partial;
  }
  
  public function setPath($path)
  {
    $this->path = $path;
  }
  
  public function getPath()
  {
    return $this->path;
  }
  
  public function setName($name)
  {
    $this->name = $name;
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  public function isActionDefault()
  {
    return $this->actionDefault;
  }
}