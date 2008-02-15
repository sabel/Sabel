<?php

/**
 * Sabel_Request_Uri
 *
 * @category   Request
 * @package    org.sabel.request
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Request_Uri extends Sabel_Object
{
  /**
   * @var string such as /module/controller/action/something
   */
  private $uri = "";
  
  /**
   * @var Array parts of uri. separate by slash (/)
   */
  private $parts = array();
  
  /**
   * @var type of last element e.g. if requested as /test/test.html type is html
   */
  private $type  = "";
  
  /**
   * constructer
   *
   * @param string $requestUri this is raw requestUri
   * @return void
   */
  public function __construct($uri)
  {
    $this->parse($uri);
  }
  
  public function parse($uri)
  {
    if ($uri === "" || $uri === null) return;
        
    $this->uri = $uri;
    $elements = explode("/", $uri);
    
    foreach ($elements as &$element) {
      if ($element === "") $element = null;
    }
    
    $lastElement = array_pop($elements);
    
    if (($pos = strpos($lastElement, ".")) !== false) {
      $this->type = substr($lastElement, $pos + 1);
    }
    
    $elements[] = $lastElement;
    return $this->parts = $elements;
  }
  
  public function type()
  {
    return $this->type;
  }
  
  public function size()
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
  
  public function set($pos, $value)
  {
    $this->parts[$pos] = $value;
  }
  
  public function __toString()
  {
    return join("/", $this->parts);
  }
  
  public function toArray()
  {
    return $this->parts;
  }
}
