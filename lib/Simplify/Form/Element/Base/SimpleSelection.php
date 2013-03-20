<?php

abstract class Simplify_Form_Element_Base_SimpleSelection extends Simplify_Form_Element
{

  public $options;

  public function onRender(Simplify_Form_Action $action, $data, $index)
  {
    $this->set('options', $this->getOptions());

    return parent::onRender($action, $data, $index);
  }

  public function getDisplayValue(Simplify_Form_Action $action, $data, $index)
  {
    return sy_get_param($this->getOptions(), $this->getValue($data, $index));
  }

  public function getOptions()
  {
    return $this->options;
  }

}
