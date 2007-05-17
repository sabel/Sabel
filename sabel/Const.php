<?php

/**
 * Sabel_Const
 *
 * @category   Const
 * @package    org.sabel.const
 * @author     Mori Reo <mori.reo@gmail.com>
 * @copyright  2002-2006 Mori Reo <mori.reo@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Const
{
  /**
   * controllers directory.
   */
  const CONTROLLER_DIR = "/controllers/";

  /**
   * postfix extention of controller class.
   */
  const CONTROLLER_SUFFIX = ".php";

  /**
   * modules directory
   */
  const MODULES_DIR = "/app/";

  /**
   * common files of project
   */
  const COMMONS_DIR = "app/commons/";

  /**
   * templates dirctory
   */
  const TEMPLATE_DIR = "views/";

  /**
   * postfix extention for template
   */
  const TEMPLATE_SUFFIX = ".tpl";

  /**
   * separater of template
   */
  const TEMPLATE_NAME_SEPARATOR = ".";

  /**
   * modules default name
   */
  const DEFAULT_MODULE = "Index";

  /**
   * controllers default name
   */
  const DEFAULT_CONTROLLER = "index";

  /**
   * default action method
   */
  const DEFAULT_ACTION = "index";
  
  const DEFAULT_LAYOUT = "layout";
  
  const DEFAULT_MAP_FILE = "/config/map.php";
  
  const REQUEST_CLASS = "Sabel_Request_Web";
}
