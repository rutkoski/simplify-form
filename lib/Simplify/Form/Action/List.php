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
 * Form action list
 *
 */
class Simplify_Form_Action_List extends Simplify_Form_Action
{

  /**
   * Number of items per page
   *
   * @var int
   */
  public $limit = 10;

  /**
   * Current page offset
   *
   * @var int
   */
  public $offset = null;

  /**
   *
   * @var mixed
   */
  public $orderBy = array();

  /**
   *
   * @var int
   */
  protected $actionMask = Simplify_Form::ACTION_LIST;

  /**
   *
   * @var Simplify_Pager
   */
  protected $pager;

  /**
   *
   * @return int
   */
  protected function getLimit()
  {
    return $this->limit;
  }

  /**
   *
   * @return int
   */
  protected function getOffset()
  {
    return s::request()->get('offset', $this->offset);
  }

  /**
   *
   * @return array
   */
  public function getOrderBy()
  {
    return $this->orderBy;
  }

  /**
   *
   */
  public function toggleOrderBy($field, $dir = null)
  {
    $i = 0;

    if (empty($this->orderBy)) {
      $this->orderBy[] = array($field, $dir);
    }
    else {
      while ($i < count($this->orderBy) && $this->orderBy[$i][0] != $field) {
        $i++;
      }
    }

    $dir = is_string($dir) ? strtolower($dir) : $dir;

    if ($i < count($this->orderBy) && $this->orderBy[$i][0] == $field) {
      if ($dir == 'asc' || $dir == 'desc') {
        $this->orderBy[$i][1] = $dir;
      }
      elseif ($this->orderBy[$i][1] == 'asc') {
        $this->orderBy[$i][1] = 'desc';
      }
      elseif ($this->orderBy[$i][1] == 'desc' || $dir === false) {
        array_splice($this->orderBy, $i, 1);
      }
      else {
        $this->orderBy[$i][1] = 'asc';
      }
    }
    else {
      if (!$dir) {
        $dir = 'asc';
      }

      $this->orderBy[] = array($field, $dir);
    }
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Action::onExecute()
   */
  public function onExecute()
  {
    parent::onExecute();

    $this->onLoadData();
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Action::onRender()
   */
  public function onRender()
  {
    $elements = $this->getElements();

    $headers = array();
    foreach ($elements as $element) {
      $element->onRenderHeaders($this, $headers);
    }

    $data = array();
    foreach ($this->formData as $index => $row) {
      $line = array();
      $line[Simplify_Form::ID] = $row[Simplify_Form::ID];
      $line['name'] = Simplify_Form::ID . "[]";
      $line['menu'] = new Simplify_Menu('actions', null, Simplify_Menu::STYLE_TOOLBAR);
      $line['menu']->addItem(new Simplify_Menu('main', null, Simplify_Menu::STYLE_BUTTON_GROUP));

      $line['elements'] = array();

      $elements->rewind();
      while ($elements->valid()) {
        $element = $elements->current();
        $elements->next();

        $element->onRenderLine($this, $line, $row, $index);
      }

      $this->form->onCreateItemMenu($line['menu'], $this, $row);

      $data[] = $line;
    }

    $bulk = array();

    $this->form->onCreateBulkOptions($bulk);

    $this->set('headers', $headers);
    $this->set('data', $data);
    $this->set('pager', $this->pager);
    $this->set('bulk', $bulk);

    return parent::onRender();
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Action::onCreateMenu()
   */
  public function onCreateMenu(Simplify_Menu $menu)
  {
    $menu->getItemByName('main')->addItem(
        new Simplify_MenuItem($this->getName(), $this->getTitle(), null, $this->url()));
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Action::onLoadData()
   */
  protected function onLoadData()
  {
    $elements = $this->getElements();

    $pk = $this->form->getPrimaryKey();

    $params = array();
    $params[Simplify_Db_QueryParameters::SELECT][] = $this->form->getPrimaryKey();
    $params[Simplify_Db_QueryParameters::LIMIT] = $this->getLimit();
    $params[Simplify_Db_QueryParameters::OFFSET] = $this->getOffset();
    $params[Simplify_Db_QueryParameters::ORDER_BY] = $this->getOrderBy();

    while ($elements->valid()) {
      $element = $elements->current();
      $element->onInjectQueryParams($this, $params);

      $elements->next();
    }

    foreach ($this->form->getFilters() as $filter) {
      $filter->onInjectQueryParams($this, $params);
    }

    $this->onInjectQueryParams($params);

    $data = $this->repository()->findAll($params);

    $this->formData = array();

    foreach ($data as $index => $row) {
      $this->formData[$index] = array();
      $this->formData[$index][Simplify_Form::ID] = $row[$pk];
      $this->formData[$index][$pk] = $row[$pk];

      $elements->rewind();

      while ($elements->valid()) {
        $element = $elements->current();
        $element->onLoadData($this, $this->formData[$index], $row);

        $elements->next();
      }
    }

    $this->pager = $this->repository()->findPager($params);
  }

}
