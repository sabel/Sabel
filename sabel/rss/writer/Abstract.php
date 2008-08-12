<?php

/**
 * Sabel_Rss_Writer_Abstract
 *
 * @interface
 * @category   RSS
 * @package    org.sabel.rss
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Rss_Writer_Abstract extends Sabel_Object
{
  /**
   * @var array
   */
  protected $info = array();
  
  /**
   * @var DOMDocument
   */
  protected $document = null;
  
  public function __construct(array $info)
  {
    $this->info = $info;
    
    $this->document = new DOMDocument();
    $this->document->preserveWhiteSpace = false;
    $this->document->formatOutput = true;
    $this->document->xmlVersion = $info["xmlVersion"];
    $this->document->encoding = $info["encoding"];
  }
  
  abstract public function build(array $items);
}
