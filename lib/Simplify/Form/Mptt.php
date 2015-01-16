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
 * Tree style form
 *
 */
class Mptt extends \Simplify\Form
{

  /**
   * Value for the sort list action
   *
   * @var string
   */
  const LIST_ACTION_SORT = 'sort';

  /**
   * The parent id column
   *
   * @var string
   */
  public $parent;

  /**
   * The left id column
   *
   * @var string
   */
  public $left;

  /**
   * The right id column
   *
   * @var string
   */
  public $right;

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form_Form::setTable()
   */
  public function setTable($table, $primaryKey, $parent = null, $left = null, $right = null)
  {
    $this->parent = $parent;
    $this->left = $left;
    $this->right = $right;

    return parent::setTable($table, $primaryKey);
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form::execute()
   */
  public function execute($action = null)
  {
    $Action = $this->getAction($action);

    $label = $this->getLabel();
    $pk = $this->getPrimaryKey();
    $parent = $this->getParent();
    $left = $this->getLeft();
    $right = $this->getRight();

    /**
     *
     * order
     *
     */

    if ($Action->show(\Simplify\Form::ACTION_LIST)) {
      $listAction = \Simplify::request()->get('listAction');

      if ($listAction == self::LIST_ACTION_SORT) {
        $id = \Simplify::request()->get(\Simplify\Form::ID);
        $index = \Simplify::request()->get('index');

        $this->repository()->moveTo($id, $index);
      }
    }

    /**
     *
     * filter by parent
     *
     */

    if ($Action->show(\Simplify\Form::ACTION_LIST)) {
      $q = $this->repository()->mptt()->query()->select(false)->select("node.{$pk}")->select(
        "CONCAT(REPEAT('&ndash;', (COUNT(parent.{$pk}) - 1)), ' ', node.{$label}) AS {$label}")->select(
        "(COUNT(parent.{$pk}) - 1) AS depth");

      $data = $q->execute()->fetchAll();

      $parents = array(0 => 'Root');
      $parents += sy_array_to_options($data, $pk, $label);

      $filter = new \Simplify\Form\Filter\Select($parent, 'Parent');
      $filter->showEmpty = false;
      $filter->defaultValue = '0';
      $filter->options = $parents;

      $this->addFilter($filter);
    }

    /**
     *
     * edit parent
     *
     */

    if ($Action->show(\Simplify\Form::ACTION_EDIT & \Simplify\Form::ACTION_CREATE)) {
      $q = $this->repository()->mptt()->query()->select(false)->select("node.{$pk}")->select(
        "CONCAT(REPEAT('&ndash;', (COUNT(parent.{$pk}) - 1)), ' ', node.{$label}) AS {$label}")->select(
        "(COUNT(parent.{$pk}) - 1) AS depth");

      $id = $this->getId();

      if (!empty($id)) {
        $row = $this->repository()->find($id[0]);
        $q->where("node.{$left} NOT BETWEEN {$row[$left]} AND {$row[$right]}");
      }

      $data = $q->execute()->fetchAll();

      $parents = array(0 => 'Root');
      $parents += sy_array_to_options($data, $pk, $label);

      $parentSelect = new \Simplify\Form\Element\Select($parent, 'Parent');
      $parentSelect->showEmpty = false;
      $parentSelect->defaultValue = '0';
      $parentSelect->options = $parents;

      $this->addElement($parentSelect, \Simplify\Form::ACTION_EDIT | \Simplify\Form::ACTION_CREATE, 0);
    }

    return parent::execute($action);
  }

  /**
   *
   * @return string
   */
  public function getParent()
  {
    if (empty($this->parent)) {
      $this->parent = \Simplify\Inflector::singularize($this->getTable()) . '_parent_id';
    }

    return $this->parent;
  }

  /**
   *
   * @return string
   */
  public function getLeft()
  {
    if (empty($this->left)) {
      $this->left = \Simplify\Inflector::singularize($this->getTable()) . '_left';
    }

    return $this->left;
  }

  /**
   *
   * @return string
   */
  public function getRight()
  {
    if (empty($this->right)) {
      $this->right = \Simplify\Inflector::singularize($this->getTable()) . '_right';
    }

    return $this->right;
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form::onCreateItemMenu()
   */
  public function onCreateItemMenu(\Simplify\Menu $menu, \Simplify\Form\Action $action, $data)
  {
    if ($action->show(\Simplify\Form::ACTION_LIST)) {
      $children = new \Simplify\MenuItem('children', 'Children', null,
        $this->url()->extend(null, array($this->repository()->parent => $data[\Simplify\Form::ID])));

      $moveMenu = new \Simplify\Menu(null, null, \Simplify\Menu::STYLE_DROPDOWN);
      $moveMenu->addItem(
        new \Simplify\MenuItem('move_top', 'First', 'arrow_top',
          $this->url()->extend(null,
            array('listAction' => self::LIST_ACTION_SORT, 'index' => 'first',
              \Simplify\Form::ID => $data[\Simplify\Form::ID]))));
      $moveMenu->addItem(
        new \Simplify\MenuItem('move_up', 'Previous', 'arrow_up',
          $this->url()->extend(null,
            array('listAction' => self::LIST_ACTION_SORT, 'index' => 'previous',
              \Simplify\Form::ID => $data[\Simplify\Form::ID]))));
      $moveMenu->addItem(
        new \Simplify\MenuItem('move_down', 'Next', 'arrow_down',
          $this->url()->extend(null,
            array('listAction' => self::LIST_ACTION_SORT, 'index' => 'next',
              \Simplify\Form::ID => $data[\Simplify\Form::ID]))));
      $moveMenu->addItem(
        new \Simplify\MenuItem('move_bottom', 'Last', 'arrow_bottom',
          $this->url()->extend(null,
            array('listAction' => self::LIST_ACTION_SORT, 'index' => 'last',
              \Simplify\Form::ID => $data[\Simplify\Form::ID]))));

      $moveItem = new \Simplify\MenuItem('move', 'Move', null, null, $moveMenu);

      $menu->addItem(new \Simplify\Menu('mptt', array($moveItem, $children), \Simplify\Menu::STYLE_BUTTON_GROUP));
    }

    parent::onCreateItemMenu($menu, $action, $data);
  }

  /**
   *
   * @return \Simplify\Form_Repository_Mptt
   */
  public function repository(\Simplify\Form\Repository\Mptt $repository = null)
  {
    if ($repository instanceof \Simplify\Form\Repository\Mptt) {
      $this->repository = $repository;
    }

    if (empty($this->repository)) {
      $this->repository = new \Simplify\Form\Repository\Mptt($this->getTable(), $this->getPrimaryKey(),
        $this->getParent(), $this->getLeft(), $this->getRight());
    }

    return $this->repository;
  }

}
