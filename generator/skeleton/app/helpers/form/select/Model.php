<?php

/**
 * Helpers_Form_Select_Model
 *
 * @author    Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright 2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Helpers_Form_Select_Model extends Helpers_Form_Select
{
  protected $option = "";
  protected $text   = "";
  
  public function setOptionKey($option)
  {
    $this->option = $option;
  }
  
  public function setTextKey($text)
  {
    $this->text = $text;
  }
  
  public function create($id = null, $class = null, $multiple = false)
  {
    $model  = MODEL($this->name);
    $option = $this->getOptionKey($model);
    $text   = $this->getTextKey($model, $option);
    $array  = array();
    
    foreach ($model->select() as $model) {
      $array[$model->$option] = $model->$text;
    }
    
    $this->name = $model->getTableName() . "_" . $option;
    $this->setOptions(_hash($array));
    
    return parent::create($id, $class, $multiple);
  }
  
  protected function getOptionKey($model)
  {
    if ($this->option === "") {
      $pkey = $model->getPrimaryKey();
      if (is_string($pkey)) {
        return $pkey;
      } else {
        return "id";
      }
    } else {
      return $this->option;
    }
  }
  
  protected function getTextKey($model, $option)
  {
    if ($this->text === "") {
      $columns = $model->getColumnNames();
      if (count($columns) === 2) {
        foreach ($columns as $column) {
          if ($column !== $option) return $column;
        }
      } else {
        return "element";
      }
    } else {
      return $this->text;
    }
  }
}
