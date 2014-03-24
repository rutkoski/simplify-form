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
 * Form action delete
 *
 */
class Simplify_Form_Action_Delete extends Simplify_Form_Action
{

  /**
   *
   * @var int
   */
  protected $actionMask = Simplify_Form::ACTION_DELETE;

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Action::onExecute()
   */
  public function onExecute()
  {
    $this->onLoadData();

    $this->onValidate();

    foreach ($this->formData as $row) {
      $this->form->dispatch(Simplify_Form::ON_BEFORE_DELETE, $this, $row);
    }

    if (s::request()->method(Simplify_Request::POST) && s::request()->post('deleteAction') == 'confirm') {
      $this->onDelete();

      return Simplify_Form::RESULT_SUCCESS;
    }
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Action::onRender()
   */
  public function onRender()
  {
    $this->set(Simplify_Form::ID, (array) $this->form->getId());

    $data = array();
    foreach ($this->formData as $index => $row) {
      $line = array();
      $line[Simplify_Form::ID] = $row[Simplify_Form::ID];
      $line['name'] = Simplify_Form::ID . "[]";
      $line['label'] = $row['label'];

      $data[] = $line;
    }

    $this->set('data', $data);

    return parent::onRender();
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Action::onCreateItemMenu()
   */
  public function onCreateItemMenu(Simplify_Menu $menu, Simplify_Form_Action $action, $data)
  {
    if (!$action->show(Simplify_Form::ACTION_CREATE)) {
      $menu->getItemByName('main')->addItem(
          new Simplify_MenuItem($this->getName(), $this->getTitle(), null,
              new Simplify_URL(null,
                  array('formAction' => $this->getName(), Simplify_Form::ID => $data[Simplify_Form::ID]))));
    }
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Action::onCreateBulkOptions()
   */
  public function onCreateBulkOptions(array &$actions)
  {
    $actions[$this->getName()] = $this->getTitle();
  }

  /**
   * Delete data from repository
   */
  protected function onDelete()
  {
    $elements = $this->getElements();

    foreach ($this->formData as $row) {
      while ($elements->valid()) {
        $element = $elements->current();
        $element->onBeforeDelete($this, $row);

        $elements->next();
      }

      $elements->rewind();
    }

    $id = $this->form->getId();
    $pk = $this->form->getPrimaryKey();

    $params = array();
    $params[Simplify_Db_QueryParameters::WHERE][] = Simplify_Db_QueryObject::buildIn($pk, $id);

    $this->repository()->deleteAll($params);

    foreach ($this->formData as $row) {
      foreach ($elements as $element) {
        $element->onAfterDelete($this, $row);
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
    $label = $this->form->getLabel();

    $params = array();
    $params[Simplify_Db_QueryParameters::SELECT][] = $pk;
    $params[Simplify_Db_QueryParameters::SELECT][] = $label;
    $params[Simplify_Db_QueryParameters::WHERE][] = Simplify_Db_QueryObject::buildIn($pk, $id);

    while ($elements->valid()) {
      $element = $elements->current();
      $element->onInjectQueryParams($this, $params);

      $elements->next();
    }

    $data = $this->repository()->findAll($params);

    $this->formData = array();

    foreach ($data as $index => $row) {
      $this->formData[$index] = array();
      $this->formData[$index][Simplify_Form::ID] = $row[$pk];
      $this->formData[$index]['label'] = $row[$label];

      $elements->rewind();

      while ($elements->valid()) {
        $element = $elements->current();
        $element->onLoadData($this, $this->formData[$index], $row);

        $elements->next();
      }
    }
  }

}
