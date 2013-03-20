<?php

class Simplify_Form_Action_Edit extends Simplify_Form_Action_Form
{

  /**
   *
   * @var int
   */
  protected $actionMask = Simplify_Form::ACTION_EDIT;

  public function onCreateItemMenu(Simplify_Menu $menu, Simplify_Form_Action $action, $data, $index)
  {
    if (! $action->show(Simplify_Form::ACTION_CREATE) && ! $action->show(Simplify_Form::ACTION_EDIT)) {
      $menu->getItemByName('main')->addItem(new Simplify_MenuItem($this->getName(), $this->getTitle(), null, new Simplify_URL(null, array('formAction' => $this->getName(), Simplify_Form::ID => $data[$index][Simplify_Form::ID]))));
    }
  }

}
