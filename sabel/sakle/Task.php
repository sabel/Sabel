<?php

/**
 * Sabel_Sakle_Task
 *
 * @category   Sakle
 * @package    org.sabel.sakle
 * @author     Mori Reo <mori.reo@gmail.com>
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Sakle_Task extends Sabel_Object
{
  abstract public function run($arguments);
  
  public function success($msg)
  {
    echo Sabel_Cli::success($msg);
  }
  
  public function warning($msg)
  {
    echo Sabel_Cli::warning($msg);
  }
  
  public function message($msg)
  {
    echo Sabel_Cli::message($msg);
  }
  
  public function error($msg)
  {
    echo Sabel_Cli::error($msg);
  }
}
