    <?php

/**
 * undecumented class
 *
 * @package org.sabel
 * @author Mori Reo <mori.reo@gmail.com>
 */
class Sabel
{
  public static function main()
  {
      

    $frontController = new Sabel_Controller_Front();
    $frontController->ignition();
  }
}
