<?php

/**
 * Sabel_Db_Validate_Config
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
abstract class Sabel_Db_Validate_Config implements Sabel_Config
{
  /**
   * @var Sabel_Db_Validate_Config_Model[]
   */
  private $models = array();
  
  /**
   * @var array
   */
  protected static $messages  = array("maxlength" => "%NAME% must be %MAX% characters or less.",
                                      "minlength" => "%NAME% must be %MIN% characters or more.",
                                      "maximum"   => "%NAME% must be %MAX% or less.",
                                      "minimum"   => "%NAME% must be %MIN% or more.",
                                      "nullable"  => "%NAME% is required.",
                                      "numeric"   => "%NAME% must be a numeric.",
                                      "type"      => "wrong %NAME% format.",
                                      "unique"    => "'%VALUE%'(%NAME%) is unavailable.");
  
  /**
   * @param array $messages
   *
   * @return void
   */
  public static function setMessages(array $messages)
  {
    self::$messages = $messages;
  }
  
  /**
   * @return array
   */
  public static function getMessages()
  {
    return self::$messages;
  }
  
  /**
   * @param string $mdlName
   *
   * @return Sabel_Db_Validate_Config_Model
   */
  public function model($mdlName)
  {
    if (!isset($this->models[$mdlName])) {
      $this->models[$mdlName] = new Sabel_Db_Validate_Config_Model();
    }
    
    return $this->models[$mdlName];
  }
  
  /**
   * @param string $mdlName
   *
   * @return boolean
   */
  public function has($mdlName)
  {
    return isset($this->models[$mdlName]);
  }
  
  /**
   * @param string $mdlName
   *
   * @return Sabel_Db_Validate_Config_Model
   */
  public function get($mdlName)
  {
    if ($this->has($mdlName)) {
      return $this->models[$mdlName];
    } else {
      return null;
    }
  }
}
