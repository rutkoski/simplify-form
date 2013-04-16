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
 * Sortable form
 *
 */
class Simplify_Form_Sortable extends Simplify_Form
{

  const LIST_ACTION_SORT = 'sort';

  /**
   *
   * @var string
   */
  public $sortField;

  /**
   * (non-PHPdoc)
   * @see Simplify_Form::execute()
   */
  public function execute($action = null)
  {

    $Action = $this->getAction($action);

    /**
     *
     * order
     *
     */

    if ($Action->show(Simplify_Form::ACTION_LIST)) {
      $listAction = s::request()->get('listAction');

      if ($listAction == self::LIST_ACTION_SORT) {
        $id = s::request()->get(Simplify_Form::ID);
        $index = s::request()->get('index');

        $this->repository()->moveTo($id, $index);
      }
    }

    return parent::execute($action);
  }

  /**
   *
   * @return string
   */
  public function getSortField()
  {
    if (empty($this->sortField)) {
      $this->sortField = Simplify_Inflector::singularize($this->getTable()) . '_order';
    }

    return $this->sortField;
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form::onCreateItemMenu()
   */
  public function onCreateItemMenu(Simplify_Menu $menu, Simplify_Form_Action $action, $data)
  {
    if ($action->show(Simplify_Form::ACTION_LIST)) {
      $moveMenu = new Simplify_Menu(null, null, Simplify_Menu::STYLE_DROPDOWN);
      $moveMenu->addItem(new Simplify_MenuItem('move_top', 'First', 'arrow_top', $this->url()->extend(null, array('listAction' => self::LIST_ACTION_SORT, 'index' => 'first', Simplify_Form::ID => $data[Simplify_Form::ID]))));
      $moveMenu->addItem(new Simplify_MenuItem('move_up', 'Previous', 'arrow_up', $this->url()->extend(null, array('listAction' => self::LIST_ACTION_SORT, 'index' => 'previous', Simplify_Form::ID => $data[Simplify_Form::ID]))));
      $moveMenu->addItem(new Simplify_MenuItem('move_down', 'Next', 'arrow_down', $this->url()->extend(null, array('listAction' => self::LIST_ACTION_SORT, 'index' => 'next', Simplify_Form::ID => $data[Simplify_Form::ID]))));
      $moveMenu->addItem(new Simplify_MenuItem('move_bottom', 'Last', 'arrow_bottom', $this->url()->extend(null, array('listAction' => self::LIST_ACTION_SORT, 'index' => 'last', Simplify_Form::ID => $data[Simplify_Form::ID]))));

      $moveItem = new Simplify_MenuItem('move', 'Move', null, null, $moveMenu);

      $menu->addItem(new Simplify_Menu('sortable', array($moveItem), Simplify_Menu::STYLE_BUTTON_GROUP));
    }

    parent::onCreateItemMenu($menu, $action, $data);
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form::repository()
   */
  public function repository(Simplify_Form_Repository $repository = null)
  {
    if ($repository instanceof Simplify_Form_Repository_Sortable) {
      $this->repository = $repository;
    }

    if (empty($this->repository)) {
      $this->repository = new Simplify_Form_Repository_Sortable($this->getTable(), $this->getPrimaryKey(), $this->getSortField());
    }

    return $this->repository;
  }

}
