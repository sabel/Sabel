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
   * @var Array parts of uri. separate by slash (/)
   */
  protected $parts = array();
  protected $entry = null;
  
  public function __construct($requestUri, $entry = null)
  {
    $this->parts = explode('/', $requestUri);
    if (isset($entry)) $this->setEntry($entry);
  }
  
  public function setEntry($entry)
  {
    $this->entry = $entry;
  }
  
  public function __get($key)
  {
    $value = $this->getByName($key);
    if (is_numeric($value)) {
      return (is_float($value)) ? (float) $value : (int) $value;
    } else {
      return (string) $value;
    }
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
    $entry = $this->entry;
    if (is_null($entry)) throw new Sabel_Exception_Runtime('entry is null.');
    
    $uri = $entry->getUri();
    if (!is_object($uri)) throw new Sabel_Exception_Runtime("Map_Uri is not object.");
    
    $position = $uri->calcElementPositionByName($name);
    return $this->get($position);
  }
}
