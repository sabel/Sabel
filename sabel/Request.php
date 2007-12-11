<?php

/**
 * Sabel_Request
 *
 * @interface
 * @category   Request
 * @package    org.sabel.request
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_Request
{
  const GET    = "GET";
  const POST   = "POST";
  const PUT    = "PUT";
  const DELETE = "DELETE";
  
  public function getParameters();
  public function getPostRequests();
  public function hasParameter($name);
  public function getParameter($name);
  public function __toString();
  public function toArray();
}
