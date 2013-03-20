<?php

class Simplify_Form_Filter_Select extends Simplify_Form_Filter
{

  public $options;

  public $showEmpty = true;

  public $emptyLabel;

  public $emptyValue;

  public function onRender(Simplify_Form_Action $action)
  {
    $this->set('showEmpty', $this->showEmpty);
    $this->set('emptyLabel', $this->emptyLabel);
    $this->set('emptyValue', $this->emptyValue);
    $this->set('options', $this->getOptions());

    return parent::onRender($action);
  }

  public function onInjectQueryParams(&$params)
  {
    $value = $this->getValue();

    if (strlen($value)) {
      $params['where'][] = "{$this->name} = :{$this->name}";
      $params['data'][$this->name] = $value;
    }
  }

  public function getOptions()
  {
    return $this->options;
  }

}
