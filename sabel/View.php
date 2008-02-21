<?php

/**
 * Sabel_View
 *
 * @interface
 * @category   View
 * @package    org.sabel.view
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_View
{
  /**
   * @param string $name
   * @param Sabel_View_Template $template
   *
   * @return void
   */
  public function addTemplate($name, Sabel_View_Template $template);
  
  /**
   * @param string $name
   *
   * @return Sabel_View_Template $template
   */
  public function getTemplate($name);
  
  /**
   * @return Sabel_View_Template[]
   */
  public function getTemplates();
  
  /**
   * @param string $tplName
   *
   * @return void
   */
  public function setName($tplName);
  
  /**
   * @return string
   */
  public function getName();
  
  /**
   * @param string $tplPath
   *
   * @return Sabel_View_Template
   */
  public function getValidTemplate($tplPath = null);
  
  /**
   * @param string $tplPath
   *
   * @return string
   */
  public function getContents($tplPath = null);
  
  /**
   * @param string $name
   * @param string $tplPath
   * @param string $contents
   *
   * @return void
   */
  public function create($name, $tplPath, $contents = "");
  
  /**
   * @param string $name
   * @param string $tplPath
   *
   * @return void
   */
  public function delete($name, $tplPath);
  
  /**
   * @param string $name
   * @param string $tplPath
   *
   * @return boolean
   */
  public function isValid($name, $tplPath);
}
