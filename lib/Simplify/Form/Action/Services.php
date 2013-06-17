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
   * @var Simplify_Form_Element
   */
  protected $service;

  /**
   *
   * @var Simplify_Form_Element
   */
  protected $response;

  /**
   *
   */
  public function onExecute()
  {
    $serviceName = s::request()->get('serviceName');
    $serviceAction = s::request()->get('serviceAction');

    $this->service = $this->form->getElementByName($serviceName);

    $this->response = $this->service->onExecuteServices($this, $serviceAction);

    $this->set($serviceName, $this->response);
  }

  public function onRender()
  {
    return $this->response;
  }

}
