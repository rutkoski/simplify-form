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

use Simplify\Form;
/**
 * Form action delete
 */
class Delete extends \Simplify\Form\Action
{

  /**
   *
   * @var int
   */
  protected $actionMask = \Simplify\Form::ACTION_DELETE;

  /**
   * (non-PHPdoc)
   *
   * @see Form_Action::onExecute()
   */
  public function onExecute()
  {
    parent::onExecute();
    
    $this->onLoadData();
    
    $this->onValidate();
    
    foreach ($this->formData as $row) {
      $this->form->dispatch(\Simplify\Form::ON_BEFORE_DELETE, $this, $row);
    }
    
    if (\Simplify::request()->method(\Simplify\Request::POST) && \Simplify::request()->post('deleteAction') == 'confirm') {
      $this->onDelete();
      
      return \Simplify\Form::RESULT_SUCCESS;
    }
  }

  /**
   * (non-PHPdoc)
   *
   * @see \Simplify\Form\Action::onRender()
   */
  public function onRender()
  {
    $this->set(\Simplify\Form::ID, (array) $this->form->getId());
    
    $data = array();
    foreach ($this->formData as $index => $row) {
      $line = array();
      $line[\Simplify\Form::ID] = $row[\Simplify\Form::ID];
      $line['name'] = \Simplify\Form::ID . "[]";
      $line['label'] = $row['label'];
      
      $data[] = $line;
    }
    
    $this->set('data', $data);
    
    return parent::onRender();
  }

  /**
   * (non-PHPdoc)
   *
   * @see \Simplify\Form\Action::onCreateItemMenu()
   */
  public function onCreateItemMenu(\Simplify\Menu $menu,\Simplify\Form\Action $action, $data)
  {
    if (! $action->show(\Simplify\Form::ACTION_CREATE)) {
      $url = $this->form->url()->extend();
      $url->set('formAction', $this->getName());
      $url->set(Form::ID, $data[Form::ID]);
      
      $item = new \Simplify\MenuItem($this->getName(), $this->getTitle(), Form::ICON_DELETE, $url);
      
      $menu->getItemByName('main')->addItem($item);
    }
  }

  /**
   * (non-PHPdoc)
   *
   * @see \Simplify\Form\Action::onCreateBulkOptions()
   */
  public function onCreateBulkOptions(array &$actions)
  {
    $actions[$this->getName()] = $this->getTitle();
  }

  /**
   * (non-PHPdoc)
   *
   * @see \Simplify\Form\Action::onLoadData()
   */
  protected function onLoadData()
  {
    $elements = $this->getElements();
    
    $id = $this->form->getId();
    $pk = $this->form->getPrimaryKey();
    $label = $this->form->getLabel();
    
    $params = array();
    $params[\Simplify\Db\QueryParameters::SELECT][] = $pk;
    $params[\Simplify\Db\QueryParameters::SELECT][] = $label;
    $params[\Simplify\Db\QueryParameters::WHERE][] = \Simplify\Db\QueryObject::buildIn($pk, $id);
    
    while ($elements->valid()) {
      $element = $elements->current();
      $element->onInjectQueryParams($this, $params);
      
      $elements->next();
    }
    
    $data = $this->repository()->findAll($params);
    
    $this->formData = array();
    
    foreach ($data as $index => $row) {
      $this->formData[$index] = array();
      $this->formData[$index][\Simplify\Form::ID] = $row[$pk];
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
