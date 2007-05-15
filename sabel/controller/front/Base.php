<?php

/**
 * Sabel_Controller_Front_Base
 *
 * @category   Controller
 * @package    org.sabel.controller
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Controller_Front_Base
{
  abstract public function processCandidate($request = null);
  abstract protected function loadFilters();
  abstract protected function processHelper($request);
  abstract protected function processPreFilter($filters, $request);
  abstract protected function processPostFilter($filters);
  abstract protected function processView();
}
