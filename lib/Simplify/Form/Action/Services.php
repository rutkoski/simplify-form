<?php

class Simplify_Form_Action_Services extends Simplify_Form_Action
{

  /**
   *
   * @var int
   */
  protected $actionMask = Simplify_Form::ACTION_SERVICES;

  /**
   *
   */
  public function onExecute()
  {
    $serviceName = s::request()->get('serviceName');
    $serviceAction = s::request()->get('serviceAction');

    $element = $this->form->getElementByName($serviceName);

    $this->set($serviceName, $element->onExecuteServices($this, $serviceAction));
  }

  public function onRender()
  {
    return $this->getView();
  }

}
