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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Rodrigo Rutkoski Rodrigues <rutkoski@gmail.com>
 */
namespace Simplify\Form\Action;

use Simplify;
use Simplify\Form;
use Simplify\Form\Action;
use Simplify\Menu;
use Simplify\MenuItem;

/**
 * Form action edit
 */
class Edit extends Base\FormBase
{

  /**
   *
   * @var int
   */
  protected $actionMask = Form::ACTION_EDIT;

  /**
   * (non-PHPdoc)
   *
   * @see \Simplify\Form\Action::onCreateItemMenu()
   */
  public function onCreateItemMenu(Menu $menu, Action $action, $data)
  {
    if (! $action->show(Form::ACTION_CREATE) && ! $action->show(Form::ACTION_EDIT)) {
      $url = $this->editUrl($data);
      
      $item = new MenuItem($this->getName(), $this->getTitle(), Form::ICON_EDIT, $url);
      
      $menu->getItemByName('main')->addItem($item);
    }
  }

}
