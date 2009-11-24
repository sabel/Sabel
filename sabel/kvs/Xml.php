<?php

/**
 * @category   KVS
 * @package    org.sabel.kvs
 * @author     Ebine Yutaka <ebine.yutaka@sabel.php-framework.org>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Kvs_Xml implements Sabel_Kvs_Interface
{
  private static $instances = array();
  
  /**
   * @var Sabel_Xml_Document
   */
  protected $document = "";
  
  private function __construct($filePath)
  {
    $dir = dirname($filePath);
    
    if (!is_dir($dir)) {
      $message = __METHOD__ . "() no such directory '{$dir}'.";
      throw new Sabel_Exception_DirectoryNotFound($message);
    }
    
    if (!file_exists($filePath)) {
      $xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<values/>
XML;
      
      if (file_put_contents($filePath, $xml) === false) {
        $message = __METHOD__ . "() can't create file '{$filePath}'.";
        throw new Sabel_Exception_Runtime($message);
      } else {
        chmod($filePath, 0777);
      }
    }
    
    $this->document = Sabel_Xml_Document::create();
    $this->docElement = $this->document->load("XML", $filePath);
  }
  
  public static function create($filePath)
  {
    if (isset(self::$instances[$filePath])) {
      return self::$instances[$filePath];
    }
    
    return self::$instances[$filePath] = new self($filePath);
  }
  
  public function read($key)
  {
    $result   = null;
    $elements = $this->docElement->$key;
    
    if ($elements->length > 0) {
      $element = $elements[0];
      
      if (($timeout = (int)$element->getAttribute("timeout")) === 0) {
        $result = $element->getNodeValue();
      } else {
        if ($timeout <= time()) {
          $element->remove();
          $this->document->save();
        } else {
          $result = $element->getNodeValue();
        }
      }
      
      if ($result !== null) {
        $result = unserialize(str_replace("\\000", "\000", $result));
      }
    }
    
    return ($result === false) ? null : $result;
  }
  
  public function write($key, $value, $timeout = 0)
  {
    $elements = $this->docElement->$key;
    $value = str_replace("\000", "\\000", serialize($value));
    
    if ($elements->length === 0) {
      $element = $this->docElement->addChild($key);
    } else {
      $element = $elements->item(0);
    }
    
    if ($timeout !== 0) {
      $timeout = time() + $timeout;
    }
    
    $element->setAttribute("timeout", $timeout);
    $element->setNodeValue($value, true);
    
    $this->document->save();
  }
  
  public function delete($key)
  {
    $result = null;
    $elements = $this->docElement->$key;
    
    if ($elements->length > 0) {
      $result = $this->read($key);
      $elements->item(0)->remove();
      $this->document->save();
    }
    
    return $result;
  }
}
