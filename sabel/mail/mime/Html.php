<?php

/**
 * Sabel_Mail_Mime_Html
 *
 * @category   Mail
 * @package    org.sabel.mail
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Mail_Mime_Html extends Sabel_Mail_Mime_Abstract
{
  /**
   * @var array
   */
  protected $inlineImages = array();
  
  /**
   * @var string
   */
  protected $type = Sabel_Mail_Mime_Abstract::HTML;
  
  public function __construct($content)
  {
    $this->content = $content;
  }
  
  public function addImage($contentId, $data, $mimeType, $encoding = "base64")
  {
    $this->inlineImages[] = array("cid"      => $contentId,
                                  "data"     => $data,
                                  "mimetype" => $mimeType,
                                  "encoding" => $encoding);
    
    return $this;
  }
  
  public function getImages()
  {
    return $this->inlineImages;
  }
  
  public function hasImage()
  {
    return !empty($this->inlineImages);
  }
  
  /**
   * @return string
   */
  public function toMailPart($boundary = null)
  {
    if ($this->hasImage() && $boundary === null) {
      $message = __METHOD__ . "() Because the inline image exists, boundary is necessary.";
      throw new Sabel_Mail_Exception($message);
    }
    
    $part = array();
    $eol  = Sabel_Mail::getEol();
    
    $part[] = "Content-Disposition: " . $this->disposition;
    $part[] = "Content-Transfer-Encoding: " . $this->encoding;
    $part[] = "Content-Type: {$this->type}; charset=" . $this->charset . $eol;
    $part[] = $this->getEncodedContent() . $eol;
    
    if ($this->hasImage()) {
      foreach ($this->inlineImages as $image) {
        $enc    = $image["encoding"];
        $part[] = "--{$boundary}";
        $part[] = "Content-Type: {$image["mimetype"]}";
        $part[] = "Content-Transfer-Encoding: $enc";
        $part[] = "Content-ID: <{$image["cid"]}>";
        $part[] = $eol . $this->encode($image["data"], $enc, $eol) . $eol;
      }
    }
    
    return implode($eol, $part);
  }
}
