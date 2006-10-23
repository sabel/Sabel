<?php

function is_not_null($value)
{
  return (!is_null($value));
}

function is_not_object($object)
{
  return (!is_object($object));
}

function uri($params, $withDomain = true)
{
  $aCreator = new UriCreator();
  return $aCreator->uri($params, $withDomain);
}

function hyperlink($params, $anchor = null, $id = null, $class = null)
{
  $aCreator = new UriCreator();
  return $aCreator->hyperlink($params, $anchor, $id, $class);
}

function a($param, $anchor)
{
  $aCreator = new UriCreator();
  return $aCreator->aTag($param, $anchor);
}

function request($uri)
{
  $container = Container::create();
  $front     = $container->load('sabel.controller.Front');
  $response  = $front->ignition($uri);
  return $response['html'];
}

/**
 * 
 *
 * @category   
 * @package    org.sabel.
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class UriCreator
{
  protected $map = null;
  
  public function __construct()
  {
    $this->map = Sabel_Map_Facade::create();
  }
  
  public function hyperlink($params, $anchor = null, $id = null, $class = null)
  {
    $uriPrefix = 'http://' . $_SERVER['HTTP_HOST'];
    
    $entry = $this->getEntry($params);
    
    if (is_object($anchor)) $anchor = $anchor->__toString();
    
    $fmtUri = '<a id="%s" class="%s" href="%s/%s">%s</a>';
    return sprintf($fmtUri, $id, $class, $uriPrefix, $entry->uri($params), $anchor);
  }
  
  public function aTag($param, $anchor)
  {
    return $this->hyperlink($this->convert($param), $anchor);
  }
  
  public function uri($params, $withDomain)
  {
    $entry     = $this->getEntry($params);
    $uriPrefix = ($withDomain) ? 'http://' . $_SERVER['HTTP_HOST'] . '/' : '';
    return $uriPrefix . $entry->uri($params);
  }
  
  protected function convert($param)
  {
    $buf = array();
    foreach (explode(',', $param) as $part) {
      $line = array_map('trim', explode(':', $part));
      $buf[($line[0] === 'e') ? 'entry' : $line[0]] = $line[1];
    }
    return $buf;
  }

  protected function getEntry(&$params)
  {
    if (isset($params['entry'])) {
      $entry = $this->map->getEntry($params['entry']);
      unset($params['entry']);
      if (!is_object($entry)) throw new Sabel_Exception_Runtime("entry is not object");
    } else {
      $entry = $this->map->getCurrentEntry();
    }
    return $entry;
  }
}

function prevd($mixed)
{
  echo '<pre>';
  var_dump($mixed);
  echo '</pre>';
}
