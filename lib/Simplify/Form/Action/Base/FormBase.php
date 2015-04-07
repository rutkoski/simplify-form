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
namespace Simplify\Form\Action\Base;

use Simplify;
use Simplify\Db\QueryObject;
use Simplify\Db\QueryParameters;
use Simplify\Form;
use Simplify\Form\Action;
use Simplify\Request;
use Simplify\Menu;
use Simplify\MenuItem;

/**
 * Base class for create/edit form actions
 */
abstract class FormBase extends Action
{

  /**
   *
   * @var string
   */
  protected $template = 'form_form';

  /**
   *
   * @param array $data          
   * @return \Simplify\URL
   */
  public function editUrl($data)
  {
    $url = $this->form->url();
    $url->set('formAction', 'edit');
    $url->set(Form::ID, $data[Form::ID]);
    return $url;
  }

  /**
   *
   * @return \Simplify\URL
   */
  public function createUrl()
  {
    return $this->form->url()->set('formAction', 'create');
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Action::onExecute()
   */
  public function onExecute()
  {
    parent::onExecute();
    
    $this->onLoadData();
    
    if (Simplify::request()->method(Request::POST)) {
      $this->onPostData();
      $this->onValidate();
      $this->onSave();
      
      return Form::RESULT_SUCCESS;
    }
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
   * @see \Simplify\Form\Action::jsonSerialize()
   */
  public function jsonSerialize()
  {
    $data = parent::jsonSerialize();
    
    foreach ($data['data'] as $i => $row) {
      $data['meta'][$i]['actions']['edit'] = $this->editUrl($row)->build();
    }
    
    $data['actions']['create'] = $this->createUrl()->build();
    
    return $data;
  }

}
