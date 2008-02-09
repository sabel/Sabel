<?php

/**
 * Sabel_Sakle_Task
 *
 * @category   Sakle
 * @package    org.sabel.sakle
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Sakle_Task extends Sabel_Object
{
  protected $arguments = array();
  
  abstract public function run();
  
  public function setArguments($arguments)
  {
    $this->arguments = $arguments;
  }
  
  public function success($msg)
  {
    echo Sabel_Console::success($msg);
  }
  
  public function warning($msg)
  {
    echo Sabel_Console::warning($msg);
  }
  
  public function message($msg)
  {
    echo Sabel_Console::message($msg);
  }
  
  public function error($msg)
  {
    echo Sabel_Console::error($msg);
  }
}
