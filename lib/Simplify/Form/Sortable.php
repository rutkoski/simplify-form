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

namespace Simplify\Form;

/**
 *
 * Sortable form
 *
 */
class Sortable extends \Simplify\Form
{

  const LIST_ACTION_SORT = 'sort';

  /**
   *
   * @var string
   */
  public $sortField;

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form::setTable()
   */
  public function setTable($table, $primaryKey, $sortField = null)
  {
    $this->sortField = $sortField;

    return parent::setTable($table, $primaryKey);
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form::execute()
   */
  public function execute($action = null)
  {

    $Action = $this->getAction($action);

    /**
     *
     * order
     *
     */

    if ($Action->show(\Simplify\Form::ACTION_LIST)) {
      $Action->limit = false;

      $listAction = \Simplify::request()->get('listAction');

      if ($listAction == self::LIST_ACTION_SORT) {
        $id = \Simplify::request()->get(\Simplify\Form::ID);
        $index = \Simplify::request()->get('index');

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
      $this->sortField = \Simplify\Inflector::singularize($this->getTable()) . '_order';
    }

    return $this->sortField;
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form::onCreateItemMenu()
   */
  public function onCreateItemMenu(\Simplify\Menu $menu, \Simplify\Form\Action $action, $data)
  {
    if ($action->show(\Simplify\Form::ACTION_LIST)) {
      $moveMenu = new \Simplify\Menu('move', null, 'Move');
      $moveMenu->addItem(new \Simplify\MenuItem('move-first', __('Move to Top'), 'fast-backward', $this->url()->extend(null, array('listAction' => self::LIST_ACTION_SORT, 'index' => 'first', \Simplify\Form::ID => $data[\Simplify\Form::ID]))));
      $moveMenu->addItem(new \Simplify\MenuItem('move-previous', __('Move Up'), 'backward', $this->url()->extend(null, array('listAction' => self::LIST_ACTION_SORT, 'index' => 'previous', \Simplify\Form::ID => $data[\Simplify\Form::ID]))));
      $moveMenu->addItem(new \Simplify\MenuItem('move-next', __('Move Down'), 'forward', $this->url()->extend(null, array('listAction' => self::LIST_ACTION_SORT, 'index' => 'next', \Simplify\Form::ID => $data[\Simplify\Form::ID]))));
      $moveMenu->addItem(new \Simplify\MenuItem('move-last', __('Move to Bottom'), 'fast-forward', $this->url()->extend(null, array('listAction' => self::LIST_ACTION_SORT, 'index' => 'last', \Simplify\Form::ID => $data[\Simplify\Form::ID]))));

      $moveItem = new \Simplify\MenuItem('move', 'Move', null, null, $moveMenu);
      $menu->addItem(new \Simplify\Menu('sortable', array($moveItem), __('Move')));
      //$menu->addItem($moveMenu);
    }

    parent::onCreateItemMenu($menu, $action, $data);
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form::repository()
   */
  public function repository(\Simplify\Form\Repository $repository = null)
  {
    if ($repository instanceof \Simplify\Form\Repository\Sortable) {
      $this->repository = $repository;
    }

    if (empty($this->repository)) {
      $this->repository = new \Simplify\Form\Repository\Sortable($this->getTable(), $this->getPrimaryKey(), $this->getSortField());
    }

    return $this->repository;
  }

}
