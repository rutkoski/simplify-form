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
 * Base class for create/edit form actions
 *
 */
abstract class Simplify_Form_Action_Base_Form extends Simplify_Form_Action
{

  /**
   *
   * @var string
   */
  protected $template = 'form_form';

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Action::onExecute()
   */
  public function onExecute()
  {
    parent::onExecute();

    $this->onLoadData();

    if (s::request()->method(Simplify_Request::POST)) {
      $this->onPostData();
      $this->onValidate();
      $this->onSave();

      return Simplify_Form::RESULT_SUCCESS;
    }
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Action::onRender()
   */
  public function onRender()
  {
    $elements = $this->getElements();

    $data = array();
    foreach ($this->formData as $index => $row) {
      $line = array();
      $line['name'] = Simplify_Form::ID . "[]";
      $line[Simplify_Form::ID] = $row[Simplify_Form::ID];
      $line['elements'] = array();
      $line['index'] = $index;
      $line['menu'] = new Simplify_Menu('actions', null, Simplify_Menu::STYLE_TOOLBAR);
      $line['menu']->addItem(new Simplify_Menu('main', null, Simplify_Menu::STYLE_BUTTON_GROUP));

      $elements->rewind();

      while ($elements->valid()) {
        $element = $elements->current();
        $element->onRenderControls($this, $line, $this->formData[$index], $index);

        $elements->next();
      }

      $this->form->onCreateItemMenu($line['menu'], $this, $row);

      $data[] = $line;
    }

    $this->set('data', $data);

    return parent::onRender();
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Action::onSave()
   */
  protected function onSave()
  {
    $elements = $this->getElements();

    $filters = $this->form->getFilters();

    foreach ($this->formData as $index => &$data) {
      // $row will be saved in the database
      $row = array();

      $row[$this->form->getPrimaryKey()] = $data[Simplify_Form::ID];

      $elements->rewind();

      while ($elements->valid()) {
        $element = $elements->current();
        $element->onCollectTableData($this, $row, $data);

        $elements->next();
      }

      foreach ($filters as &$filter) {
        $filter->onCollectTableData($this, $row, $data);
      }

      $this->repository()->save($row);

      // fill the primary key if this is a new record
      $data[Simplify_Form::ID] = $row[$this->form->getPrimaryKey()];

      $elements->rewind();

      while ($elements->valid()) {
        $element = $elements->current();
        $element->onSave($this, $data);

        $elements->next();
      }
    }
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Action::onLoadData()
   */
  protected function onLoadData()
  {
    $elements = $this->getElements();

    $id = $this->form->getId();
    $pk = $this->form->getPrimaryKey();

    $params = array();
    $params[Simplify_Db_QueryParameters::SELECT][] = $pk;
    $params[Simplify_Db_QueryParameters::WHERE][] = Simplify_Db_QueryObject::buildIn($pk, $id);

    $elements->rewind();

    while ($elements->valid()) {
      $element = $elements->current();
      $element->onInjectQueryParams($this, $params);

      $elements->next();
    }

    foreach ($this->form->getFilters() as $filter) {
      $filter->onInjectQueryParams($this, $params);
    }

    $data = $this->repository()->findAll($params);

    $this->formData = array();

    foreach ($data as $index => &$row) {
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
  }

}
