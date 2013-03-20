<?php

class Simplify_Form_Action_Create extends Simplify_Form_Action_Form
{

  /**
   *
   * @var int
   */
  protected $actionMask = Simplify_Form::ACTION_CREATE;

  public function onCreateMenu(Simplify_Menu $menu)
  {
    $menu->getItemByName('main')->addItem(new Simplify_MenuItem($this->getName(), $this->getTitle(), null, $this->form->url()->extend(null, array('formAction' => $this->getName()))));
  }

  public function onLoadData()
  {
    $this->formData = array(array(Simplify_Form::ID => null));
  }

}
