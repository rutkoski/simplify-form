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
 * Tree style form
 *
 */
class Simplify_Form_Mptt extends Simplify_Form
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
   * @see Simplify_Form::execute()
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

    if ($Action->show(Simplify_Form::ACTION_LIST)) {
      $listAction = s::request()->get('listAction');

      if ($listAction == self::LIST_ACTION_SORT) {
        $id = s::request()->get(Simplify_Form::ID);
        $index = s::request()->get('index');

        $this->repository()->moveTo($id, $index);
      }
    }

    /**
     *
     * filter by parent
     *
     */

    if ($Action->show(Simplify_Form::ACTION_LIST)) {
      $q = $this->repository()->mptt()->query()->select(false)->select("node.{$pk}")->select(
        "CONCAT(REPEAT('&ndash;', (COUNT(parent.{$pk}) - 1)), ' ', node.{$label}) AS {$label}")->select(
        "(COUNT(parent.{$pk}) - 1) AS depth");

      $data = $q->execute()->fetchAll();

      $parents = array(0 => 'Root');
      $parents += sy_array_to_options($data, $pk, $label);

      $filter = new Simplify_Form_Filter_Select($parent, 'Parent');
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

    if ($Action->show(Simplify_Form::ACTION_EDIT & Simplify_Form::ACTION_CREATE)) {
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

      $parentSelect = new Simplify_Form_Element_Select($parent, 'Parent');
      $parentSelect->showEmpty = false;
      $parentSelect->defaultValue = '0';
      $parentSelect->options = $parents;

      $this->addElement($parentSelect, Simplify_Form::ACTION_EDIT | Simplify_Form::ACTION_CREATE, 0);
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
      $this->parent = Inflector::singularize($this->getTable()) . '_parent_id';
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
      $this->left = Inflector::singularize($this->getTable()) . '_left';
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
      $this->right = Inflector::singularize($this->getTable()) . '_right';
    }

    return $this->right;
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form::onCreateItemMenu()
   */
  public function onCreateItemMenu(Simplify_Menu $menu, Simplify_Form_Action $action, $data)
  {
    if ($action->show(Simplify_Form::ACTION_LIST)) {
      $children = new Simplify_MenuItem('children', 'Children', null,
        $this->url()->extend(null, array($this->repository()->parent => $data[Simplify_Form::ID])));

      $moveMenu = new Simplify_Menu(null, null, Simplify_Menu::STYLE_DROPDOWN);
      $moveMenu->addItem(
        new Simplify_MenuItem('move_top', 'First', 'arrow_top',
          $this->url()->extend(null,
            array('listAction' => self::LIST_ACTION_SORT, 'index' => 'first',
              Simplify_Form::ID => $data[Simplify_Form::ID]))));
      $moveMenu->addItem(
        new Simplify_MenuItem('move_up', 'Previous', 'arrow_up',
          $this->url()->extend(null,
            array('listAction' => self::LIST_ACTION_SORT, 'index' => 'previous',
              Simplify_Form::ID => $data[Simplify_Form::ID]))));
      $moveMenu->addItem(
        new Simplify_MenuItem('move_down', 'Next', 'arrow_down',
          $this->url()->extend(null,
            array('listAction' => self::LIST_ACTION_SORT, 'index' => 'next',
              Simplify_Form::ID => $data[Simplify_Form::ID]))));
      $moveMenu->addItem(
        new Simplify_MenuItem('move_bottom', 'Last', 'arrow_bottom',
          $this->url()->extend(null,
            array('listAction' => self::LIST_ACTION_SORT, 'index' => 'last',
              Simplify_Form::ID => $data[Simplify_Form::ID]))));

      $moveItem = new Simplify_MenuItem('move', 'Move', null, null, $moveMenu);

      $menu->addItem(new Simplify_Menu('mptt', array($moveItem, $children), Simplify_Menu::STYLE_BUTTON_GROUP));
    }

    parent::onCreateItemMenu($menu, $action, $data);
  }

  /**
   *
   * @return Simplify_Form_Repository_Mptt
   */
  public function repository(Simplify_Form_Repository_Mptt $repository = null)
  {
    if ($repository instanceof Simplify_Form_Repository_Mptt) {
      $this->repository = $repository;
    }

    if (empty($this->repository)) {
      $this->repository = new Simplify_Form_Repository_Mptt($this->getTable(), $this->getPrimaryKey(),
        $this->getParent(), $this->getLeft(), $this->getRight());
    }

    return $this->repository;
  }

}
