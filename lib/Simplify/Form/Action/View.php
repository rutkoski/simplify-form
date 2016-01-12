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

use Simplify\Db\QueryObject;

use Simplify;
use Simplify\Db\QueryParameters;
use Simplify\Form;
use Simplify\Form\Action;
use Simplify\Menu;
use Simplify\MenuItem;

/**
 * Form action view
 */
class View extends Action
{

  /**
   *
   * @var int
   */
  protected $actionMask = Form::ACTION_VIEW;

  /**
   * (non-PHPdoc)
   *
   * @see Simplify\Form\Action::onExecute()
   */
  public function onExecute()
  {
    parent::onExecute();
    
    $this->onLoadData();
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Action::onRender()
   */
  public function onRender()
  {
      $elements = $this->getElements();
  
      $data = array();
      foreach ($this->formData as $index => $row) {
          $line = array();
          $line['name'] = Form::ID . "[]";
          $line[Form::ID] = $row[Form::ID];
          $line['elements'] = array();
          $line['index'] = $index;
          $line['menu'] = new Menu('actions');
          $line['menu']->addItem(new Menu('main'));
  
          $elements->rewind();
  
          while ($elements->valid()) {
              $element = $elements->current();
              
//               if ($element->renderControlsForView) {
                  $element->onRenderControls($this, $line, $this->formData[$index], $index);
//               } else {
//                   $element->onRenderLine($this, $line, $row, $index);
//               }
              
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
   *
   * @see Simplify\Form\Action::onRender()
   */
  public function _onRender()
  {
    $elements = $this->getElements();
    
    $data = array();
    foreach ((array) $this->formData as $index => $row) {
      $line = array();
      $line['name'] = Form::ID . "[]";
      $line[Form::ID] = $row[Form::ID];
      $line['elements'] = array();
      $line['index'] = $index;
      $line['menu'] = new Menu('actions');
      $line['menu']->addItem(new Menu('main'));
      
      $elements->rewind();
      
      while ($elements->valid()) {
        $element = $elements->current();
        $element->onRenderLine($this, $line, $row, $index);
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
   *
   * @see \Simplify\Form\Action::onCreateItemMenu()
   */
  public function onCreateItemMenu(Menu $menu, Action $action, $data)
  {
    if (! $action->show(Form::ACTION_VIEW)) {
      $url = $this->form->url()->extend(null, 
          array(
              'formAction' => $this->getName(),
              Form::ID => $data[Form::ID]
          ));
      
      $menu->getItemByName('main')->addItem(new MenuItem($this->getName(), $this->getTitle(), 'search', $url));
    }
  }

  /**
   * (non-PHPdoc)
   *
   * @see Action::onLoadData()
   */
  protected function onLoadData()
  {
    $elements = $this->getElements();
    
    $id = $this->form->getId();
    $pk = $this->form->getPrimaryKey();
    
    $params = array();
    $params[QueryParameters::SELECT][] = $pk;
    $params[QueryParameters::WHERE][] = QueryObject::buildIn($pk, $id);
    
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
      $this->formData[$index][Form::ID] = $row[$pk];
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
