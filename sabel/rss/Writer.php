<?php

/**
 * Sabel_Rss_Writer
 *
 * @category   RSS
 * @package    org.sabel.rss
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Rss_Writer extends Sabel_Object
{
  /**
   * @var string
   */
  protected $type = "Rss";
  
  /**
   * @var array
   */
  protected $info = array(
    "xmlVersion"  => "1.0",
    "encoding"    => "UTF-8",
    "language"    => "en",
    "home"        => "",
    "rss"         => "",
    "title"       => "",
    "description" => "",
    "updated"     => "",
  );
  
  /**
   * @var array
   */
  protected $items = array();
  
  /**
   * @var int
   */
  protected $summaryLength = 0;
  
  public function __construct($type = "Rss")
  {
    $type = ucfirst(strtolower($type));
    $type = str_replace(".", "", $type);
    
    if ($type === "Rss20") {
      $this->type = "Rss";
    } elseif ($type === "Rss10") {
      $this->type = "Rdf";
    } elseif (in_array($type, array("Rss", "Rdf", "Atom10", "Atom03"), true)) {
      $this->type = $type;
    } else {
      $message = __METHOD__ . "() '{$type}' is not supported now.";
      throw new Sabel_Exception_Runtime($message);
    }
  }
  
  /**
   * @param array $info
   *
   * @return self
   */
  public function setInfo(array $info)
  {
    $this->info = array_merge($this->info, $info);
    
    return $this;
  }
  
  /**
   * @param array $info
   *
   * @return self
   */
  public function setImage(array $info)
  {
    $this->info["image"] = $info;
    
    return $this;
  }
  
  /**
   * @param array $data
   *
   * @return self
   */
  public function addItem(array $data)
  {
    $this->items[] = $data;
    
    return $this;
  }
  
  /**
   * @param int $length
   *
   * @return self
   */
  public function setSummaryLength($length)
  {
    if (preg_match('/^[1-9][0-9]*$/', $length) === 1) {
      $this->summaryLength = $length;
    } else {
      $message = __METHOD__ . "() argument must be an integer.";
      throw new Sabel_Exception_Runtime($message);
    }
    
    return $this;
  }
  
  /**
   * @param string $path
   *
   * @return string
   */
  public function output($path = null)
  {
    $items = $this->items;
    
    if ($this->summaryLength > 0) {
      $length = $this->summaryLength;
      if (extension_loaded("mbstring")) {
        foreach ($items as &$item) {
          $item["summary"] = mb_strimwidth($item["content"], 0, $length + 3, "...");
        }
      } else {
        foreach ($items as &$item) {
          if (strlen($item["content"]) > $length) {
            $item["summary"] = substr($item["content"], 0, $length - 3) . "...";
          }
        }
      }
    }
    
    $info = $this->info;
    if ($info["home"] === "") {
      $info["home"] = "http://" . $_SERVER["SERVER_NAME"] . "/";
    }
    
    if ($info["updated"] === "" && isset($items[0])) {
      $info["updated"] = $items[0]["date"];
    }
    
    $className = "Sabel_Rss_Writer_" . $this->type;
    $instance = new $className($info);
    $xml = $instance->build($items);
    
    if ($path !== null) {
      file_put_contents($path, $xml);
    }
    
    return $xml;
  }
}
