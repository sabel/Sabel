<?php

/**
 * HTTP Request
 *
 * @interface
 * @category   Http
 * @package    org.sabel.http
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_Http_Requestable
{
  public function connect($host, $port);
  public function send($data);
  public function disconnect();
}
