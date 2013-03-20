<?php

class Simplify_Form_Element_Select extends Simplify_Form_Element_Base_SimpleSelection
{

  public $showEmpty = true;

  public $emptyLabel;

  public $emptyValue;

  public function onRender(Simplify_Form_Action $action, $row, $index)
  {
    $this->set('showEmpty', $this->showEmpty);
    $this->set('emptyLabel', $this->emptyLabel);
    $this->set('emptyValue', $this->emptyValue);

    return parent::onRender($action, $row, $index);
  }

}
