<?php

class Simplify_Form_Filter extends Simplify_Form_Component
{

  public function getValue()
  {
    return s::request()->get($this->getName(), $this->getDefaultValue());
  }

  public function onExecute(Simplify_Form_Action $action)
  {
    $this->form->url()->set($this->getName(), $this->getValue());
  }

  public function onRender(Simplify_Form_Action $action)
  {
    $this->set('label', $this->label);
    $this->set('name', $this->name);
    $this->set('value', $this->getValue());

    return parent::onRender($action);
  }

  /**
   *
   */
  public function onPostData(&$row, $data, $index)
  {
    $row[$this->getFieldName()] = $this->getValue();
  }

  /**
   *
   */
  public function onCollectTableData(&$row, $data)
  {
    $row[$this->getFieldName()] = $data[$this->getName()];
  }

}
