<?php

/**
 * Sabel_Response
 *
 * @category   Response
 * @package    org.sabel.response
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_Response
{
  public function setResponse($key, $value);
  public function getResponse($key);
  public function setResponses($array);
  public function getResponses();
}
