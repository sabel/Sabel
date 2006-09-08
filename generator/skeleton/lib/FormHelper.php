<?php

class FormHelper
{
  public static function create($column, $id = null, $style = null)
  {
    $id = (is_null($id)) ? $column->table .'_'. $column->name : $id;
    
    switch ($column->getType()) {
      case 'STRING':
        $format = '<input id="%s" style="%s" type="%s" name="%s" value="%s" />';
        return sprintf($format, $id, $style, 'text', $column->name, $column->value);
        break;
      case 'INT':
        $format = '<input id="%s" style="%s" type="%s" name="%s" value="%s" />';
        return sprintf($format, $id, $style, 'text', $column->name, $column->value);
        break;
      case 'TEXT':
        $format = '<textarea id="%s" style="%s" name="%s">%s</textarea>';
        return sprintf($format, $id, $style, $column->name, $column->value);
        break;
    }
  }
}