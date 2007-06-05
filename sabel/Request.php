<?php

/**
 * Sabel_Request
 *
 * @category   Request
 * @package    org.sabel.request
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
interface Sabel_Request
{
  const GET    = "get";
  const POST   = "post";
  const PUT    = "put";
  const delete = "delete";
  
  public function getParameters();
  public function getPostRequests();
  public function hasParameter($name);
  public function getParameter($name);
  public function __toString();
}
