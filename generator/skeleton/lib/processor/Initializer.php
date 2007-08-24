<?php

/**
 * Processor_I18n
 *
 * @author     Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright  2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Initializer extends Sabel_Bus_Processor
{
  public function execute($bus)
  {
    $libDb = RUN_BASE . DIR_DIVIDER . "lib" . DIR_DIVIDER . "db" . DIR_DIVIDER;
    
    Sabel::fileUsing($libDb . "utility.php");
    Sabel::fileUsing($libDb . "validators.php");
    Sabel::fileUsing($libDb . "Manipulator.php");
    Sabel_DB_Config::initialize();
  }
}
