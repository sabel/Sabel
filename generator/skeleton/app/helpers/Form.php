<?php

/**
 * Helpers_Form
 *
 * @author    Ebine Yutaka <ebine.yutaka@gmail.com>
 * @copyright 2002-2006 Ebine Yutaka <ebine.yutaka@gmail.com>
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Helpers_Form
{
  protected $model   = null;
  protected $mdlName = "";
  protected $columns = null;
  
  public function __construct($model = null)
  {
    if ($model !== null) {
      $this->model   = $model;
      $this->mdlName = $model->getModelName();
      $this->schema  = $model->getSchema();
      $this->columns = $this->schema->getColumns();
    }
  }
  
  public function create($uri, $id = null, $class = null, $name = null)
  {
    $columns = $this->columns;
    $schema  = $this->schema;
    $mdlName = $this->mdlName;
    if (empty($columns)) return "";
    
    $html  = array($this->start($uri, $id, $class, "POST", $name));
    $names = Sabel_DB_Model_Localize::getColumnNames($mdlName);
    foreach ($columns as $colName => $column) {
      if ($column->increment || $column->primary) continue;
      
      if ($schema->isForeignKey($colName)) {
        $html[] = $this->hidden($colName);
        continue;
      }
      
      if (isset($names[$colName])) {
        $html[] = $names[$colName];
      } else {
        $html[] = $colName;
      }
      
      if ($column->isBool()) {
        $html[] = $this->checkbox($colName);
      } elseif ($column->isDatetime()) {
        $html[] = $this->datetime($colName);
      } elseif ($column->isText()) {
        $html[] = $this->textarea($colName);
      } else {
        $html[] = $this->text($colName);
      }
    }
    
    $html[] = "<br/>" . $this->submit();
    $html[] = $this->end();
    
    return implode("<br/>\n", $html) . "\n";
  }
  
  public function start($uri, $class = null, $id = null, $method = "POST", $name = null)
  {
    $html = '<form action="' . uri($uri) . '" method="' . $method . '" ';
    $this->addIdAndClass($html, $id, $class);
    if ($name !== null) $html .= 'name="' . $name . '" ';
    
    return $html . " />";
  }
  
  public function end()
  {
    return "</form>";
  }
  
  public function text($name, $class = null, $id = null)
  {
    $value = $this->getValue($name);
    $name  = $this->getName($name);
    $html  = '<input type="text" ';
    $this->addIdAndClass($html, $id, $class);
    $html .= 'name="' . $name . '" value="' . $value . '">';
    
    return $html;
  }
  
  public function textarea($name, $class = null, $id = null)
  {
    $value = $this->getValue($name);
    $name  = $this->getName($name);
    $html  = '<textarea ';
    $this->addIdAndClass($html, $id, $class);
    $html .= 'name="' . $name . '">' . $value . '</textarea>';
    
    return $html;
  }
  
  public function select($name, $values, $class = null, $id = null, $size = 1)
  {
    $value  = $this->getValue($name);
    $name   = $this->getName($name);
    $select = new Helpers_Form_Select($name, $size);
    $select->setOptions($values);
    return $select->create($id, $class);
  }
  
  public function checkbox($name, $class = null, $id = null)
  {
    $value = $this->getValue($name);
    $name  = $this->getName($name);
    $html  = '<input type="checkbox" ';
    $this->addIdAndClass($radio, $id, $class);
    $html .= 'name="' . $name . '" value="1"';
    
    if ($value === true) $html .= ' checked="checked"';
    
    return $html . ' />';
  }
  
  public function radio($name, $values, $selected = null, $class = null, $id = null)
  {
    $value = $this->getValue($name);
    $name  = $this->getName($name);
    $html  = array();
    
    $i = 1;
    foreach ($values as $value) {
      $radio = '<input type="radio" ';
      $this->addIdAndClass($radio, $id, $class);
      $radio .= 'name="' . $name . '" value="' . $value . '"';
      
      if ($selected === null && $i === 1 || $selected === $value) {
        $radio .= 'checked="checked"';
      }
      
      $html[] = $radio . ' />';
      $i++;
    }
    
    return implode("&nbsp;", $html);
  }
  
  public function datetime($name)
  {
    $value = $this->getValue($name);
    $name  = $this->getName($name);
    $dtime = new Helpers_Form_Select_Datetime($name, $value);
    return $dtime->create();
  }
  
  public function hidden($name, $class = null, $id = null)
  {
    $value = $this->getValue($name);
    $name  = $this->getName($name);
    $html  = '<input type="hidden" ';
    $this->addIdAndClass($html, $id, $class);
    $html .= 'name="' . $name . '" value="' . $value . '">';
    
    return $html;
  }
  
  public function submit($value = "")
  {
    if ($value !== "") $value = 'value="' . $value. '" ';
    return '<input type="submit" ' . $value . '/>';
  }

  protected function getName($name)
  {
    if (isset($this->columns[$name])) {
      return $this->mdlName . "::" . $name;
    } else {
      return $name;
    }
  }
  
  protected function getValue($name)
  {
    if ($this->model === null) {
      return "";
    } else {
      $value = $this->model->$name;
      return (is_string($value)) ? htmlspecialchars($value) : $value;
    }
  }
  
  protected function addIdAndClass(&$html, $id, $class)
  {
    if ($id !== null)    $html .= 'id="' . $id . '" ';
    if ($class !== null) $html .= 'class="' . $class . '" ';
  }
}
