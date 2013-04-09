<?php

/**
 * SimplifyPHP Framework
 *
 * This file is part of SimplifyPHP Framework.
 *
 * SimplifyPHP Framework is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * SimplifyPHP Framework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Rodrigo Rutkoski Rodrigues <rutkoski@gmail.com>
 */

/**
 *
 * Form action edit
 *
 */
class Simplify_Form_Action_Edit extends Simplify_Form_Action_Base_Form
{

  /**
   *
   * @var int
   */
  protected $actionMask = Simplify_Form::ACTION_EDIT;

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Action::onCreateItemMenu()
   */
  public function onCreateItemMenu(Simplify_Menu $menu, Simplify_Form_Action $action, $data)
  {
    if (!$action->show(Simplify_Form::ACTION_CREATE) && !$action->show(Simplify_Form::ACTION_EDIT)) {
      $menu->getItemByName('main')->addItem(
        new Simplify_MenuItem($this->getName(), $this->getTitle(), null,
          new Simplify_URL(null, array('formAction' => $this->getName(), Simplify_Form::ID => $data[Simplify_Form::ID]))));
    }
  }

}
