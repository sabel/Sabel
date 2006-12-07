<?php

/**
 * Sabel_Request_Uri
 *
 * @category   Request
 * @package    org.sabel.request
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Request_Uri
{
  /**
   *
   * @var string such as /module/controller/action/something
   */
  protected $rawUriString = '';
  
  /**
   * @var Array parts of uri. separate by slash (/)
   */
  protected $parts = array();
  
  /**
   * @var type of last element e.g. if requested as /test/test.html type is html
   */
  protected $type  = '';
  
  /**
   * constructer
   *
   * @param string $requestUri this is raw requestUri(query string without query parameter)
   * @return void
   */
  public function __construct($requestUri)
  {
    $this->rawUriString = $requestUri;
    
    $elements    = explode('/', $requestUri);
    $lastElement = array_pop($elements);
    
    if (strpos($lastElement, '.') !== false)
      list($lastElement, $this->type) = explode('.', $lastElement);
      
    array_push($elements, $lastElement);
    
    $this->parts = $elements;
  }
  
  public function __get($key)
  {
  }
  
  public function count()
  {
    return count($this->parts);
  }
  
  public function get($pos)
  {
    return ($this->has($pos)) ? $this->parts[$pos] : null;
  }
  
  public function has($pos)
  {
    return isset($this->parts[$pos]);
  }
  
  public function getModule()
  {
    return $this->getByName('module');
  }
  
  public function getController()
  {
    return $this->getByName('controller');
  }
  
  public function getAction()
  {
    return $this->getByName('action');
  }
  
  public function getByName($name)
  {
  }
  
  public function getType()
  {
    return $this->type;
  }
  
  public function __toString()
  {
    return $this->rawUriString;
  }
}
