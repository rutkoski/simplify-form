<?php

class Simplify_Form_Element_Hidden extends Simplify_Form_Element
{

  public $defaultValue;

  public function onRender(Simplify_Form_Action $action, $row, $index)
  {
    $value = $this->getValue($row);

    if (empty($value)) {
      $value = $this->defaultValue;
    }

    $output = '<input type="hidden" name="'.$this->getName().'['.$index.']" value="'.$value.'"/>';

    return $output;
  }

}
