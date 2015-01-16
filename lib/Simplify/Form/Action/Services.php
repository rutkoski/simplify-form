<?php

namespace Simplify\Form\Action;

class Services extends \Simplify\Form\Action
{

  /**
   *
   * @var int
   */
  protected $actionMask = \Simplify\Form::ACTION_SERVICES;

  /**
   *
   * @var \Simplify\Form\Element
   */
  protected $service;

  /**
   *
   * @var \Simplify\Form\Element
   */
  protected $response;

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Action::onExecute()
   */
  public function onExecute()
  {
    $serviceName = \Simplify::request()->get('serviceName');
    $serviceAction = \Simplify::request()->get('serviceAction');

    $this->service = $this->form->getElementByName($serviceName);

    $this->response = $this->service->onExecuteServices($this, $serviceAction);

    $this->set($serviceName, $this->response);
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Action::onRender()
   */
  public function onRender()
  {
    return $this->response;
  }

}
