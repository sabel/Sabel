<?php

/**
 * Sabel_Db_Validate_Config_Column
 *
 * @category   DB
 * @package    org.sabel.db
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Db_Validate_Config_Column
{
  /**
   * @var object[]
   */
  private $validators = array();
  
  /**
   * @param string $validator
   */
  public function validator($validator)
  {
    $args = func_get_args();
    $validator = new stdClass();
    $validator->name = $args[0];
    
    if (count($args) > 1) {
      array_shift($args);
      $validator->arguments = $args;
    } else {
      $validator->arguments = array();
    }
    
    $this->validators[] = $validator;
  }
  
  /**
   * @return object[]
   */
  public function getValidators()
  {
    return $this->validators;
  }
  
  /**
   * @return array
   */
  public function getArguments()
  {
    return $this->arguments;
  }
}
