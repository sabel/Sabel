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
   * @var string such as /module/controller/action/something
   */
  private $rawUriString = "";
  
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
  public function __construct($rawRequestUri)
  {
    $this->parse($rawRequestUri);
  }
  
  public function parse($rawUriString)
  {
    static $filter = null;
    if ($filter === null) {
      $filter = create_function('$value',
                                'return ($value !== "") ? $value : null;');
    }
    
    $this->rawUriString = $rawUriString;
    $elements = explode("/", $rawUriString);

    $elements = array_values(array_filter($elements, $filter));
    $lastElement = array_pop($elements);
    
    if (strpos($lastElement, ".") !== false) {
      list($lastElement, $this->type) = explode(".", $lastElement);
    }
    
    array_push($elements, $lastElement);
    $this->parts = $elements;
  }
  
  public function getType()
  {
    return $this->type;
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
  
  public function set($pos, $value)
  {
    $this->parts[$pos] = $value;
  }
  
  public function __toString()
  {
    return join("/", $this->parts);
  }
}
