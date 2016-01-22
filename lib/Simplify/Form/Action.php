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
namespace Simplify\Form;

use Simplify\Db\QueryObject;
use Simplify\Db\QueryParameters;
use Simplify;
use Simplify\Form;
use Simplify\Renderable;
use Simplify\Inflector;
use Simplify\URL;
use Simplify\Menu;

/**
 * Abstract class for form actions such as list, edit, create...
 */
abstract class Action extends Renderable
{

  /**
   *
   * @var Form
   */
  public $form;

  /**
   * Action title
   *
   * @var string
   */
  public $title;

  /**
   *
   * @var array
   */
  public $formData = array();

  /**
   *
   * @var string
   */
  public $mode = Form::MODE_HTML;

  /**
   *
   * @var string
   */
  protected $name;

  /**
   *
   * @var int
   */
  protected $actionMask;

  /**
   *
   * @var string[string]
   */
  protected $errors;

  /**
   *
   * @param string $name
   *          action name
   */
  public function __construct($name = null, $title = null)
  {
    $this->name = $name;
    $this->title = $title;
  }

  /**
   * Execute the action
   *
   * @return void
   */
  public function onExecute()
  {
    $elements = $this->getElements();
    
    while ($elements->valid()) {
      $element = $elements->current();
      $element->onExecute($this);
      $elements->next();
    }
    
    foreach ($this->form->getFilters() as $filter) {
      $filter->onExecute($this);
    }
  }

  /**
   * Render the action
   *
   * @return void
   */
  public function onRender()
  {
    $this->set('action', $this);
    $this->set('elements', $this->getElements());
    $this->set('formData', $this->formData);
    $this->set('formMode', $this->formMode());
    $this->set('formUrl', $this->url());
    $this->set('formAjaxUrl', $this->url()->extend()->format('json')->set('formMode', Form::MODE_AJAX));
    $this->set('title', $this->getTitle());
    
    $filters = array();
    foreach ($this->form->getFilters() as $filter) {
      $filter->onRenderControls($this, $filters);
    }
    $this->set('filters', $filters);
    
    $this->form->dispatch(Form::ON_RENDER, $this);
    
    return $this;
  }

  /**
   *
   * @param string $mode          
   * @return string boolean
   */
  public function formMode($mode = null)
  {
    return $mode ? \Simplify::request()->get('formMode', $this->mode) == $mode : \Simplify::request()->get('formMode', 
        $this->mode);
  }

  /**
   * (non-PHPdoc)
   *
   * @see Dictionary::jsonSerialize()
   */
  public function jsonSerialize()
  {
    $data = array();
    $data['data'] = $this->formData;
    return $data;
  }

  /**
   * Load data from repository
   */
  protected function onLoadData()
  {
    $elements = $this->getElements();
    $filters = $this->form->getFilters();
    
    $id = $this->form->getId();
    $pk = $this->form->getPrimaryKey();
    
    $params = array();
    $params[QueryParameters::SELECT][] = $pk;
    $params[QueryParameters::WHERE][] = QueryObject::buildIn($pk, $id);
    
    $this->onInjectQueryParams($params);
    
    $data = $this->repository()->findAll($params);
    
    $this->formData = array();
    
    foreach ($data as $index => &$row) {
      $this->formData[$index] = array();
      $this->formData[$index][Form::ID] = $row[$pk];
      $this->formData[$index][$pk] = $row[$pk];
      
      $this->onExtractData($this->formData[$index], $row);
      
      $elements->rewind();

      while ($elements->valid()) {
        $element = $elements->current();
        $element->onLoadData($this, $this->formData[$index], $row);
        $elements->next();
      }

      foreach ($filters as $filter) {
          $filter->onLoadData($this, $this->formData[$index], $row);
      }
    }
  }

  /**
   *
   * @param array $data
   *          form data
   * @param array $row
   *          database row
   */
  protected function onExtractData(&$data, $row)
  {
  }

  /**
   * Save data to repository
   */
  protected function onSave()
  {
    $elements = $this->getElements();
    
    $filters = $this->form->getFilters();
    
    foreach ($this->formData as $index => &$formRow) {
      $databaseRow = array();
      $databaseRow[$this->form->getPrimaryKey()] = $formRow[Form::ID];
      
      $elements->rewind();
      while ($elements->valid()) {
        $element = $elements->current();
        $element->onCollectTableData($this, $databaseRow, $formRow);
        $elements->next();
      }
      
      foreach ($filters as &$filter) {
        $filter->onCollectTableData($this, $databaseRow, $formRow);
      }
      
      $this->repository()->save($databaseRow);
      
      // fill the primary key if this is a new record
      if (empty($formRow[Form::ID])) {
        $formRow[$this->form->getPrimaryKey()] = $formRow[Form::ID] = $databaseRow[$this->form->getPrimaryKey()];
      }
      
      $elements->rewind();
      while ($elements->valid()) {
        $element = $elements->current();
        $element->onSave($this, $formRow);
        $elements->next();
      }
    }
  }

  /**
   * Create the action menu
   *
   * @param Menu $menu          
   * @param Action $action          
   */
  public function onCreateMenu(Menu $menu, Action $action)
  {
  }

  /**
   * Delete data from repository
   */
  protected function onDelete()
  {
    $elements = $this->getElements();
    
    foreach ($this->formData as $row) {
      $elements->rewind();
      while ($elements->valid()) {
        $element = $elements->current();
        $element->onBeforeDelete($this, $row);
        $elements->next();
      }
    }
    
    $id = $this->form->getId();
    $pk = $this->form->getPrimaryKey();
    
    $params = array();
    $params[\Simplify\Db\QueryParameters::WHERE][] = \Simplify\Db\QueryObject::buildIn($pk, $id);
    
    $this->repository()->deleteAll($params);
    
    foreach ($this->formData as $row) {
      $elements->rewind();
      while ($elements->valid()) {
        $element = $elements->current();
        $element->onAfterDelete($this, $row);
        $elements->next();
      }
    }
  }

  /**
   * Create the menu for each row in the form
   *
   * @param Menu $menu          
   * @param Action $action          
   * @param array $row          
   */
  public function onCreateItemMenu(Menu $menu, Action $action, $row)
  {
  }

  /**
   *
   * @return \Simplify\URL
   */
  public function url()
  {
    /*return new URL(null, array(
        'formAction' => $this->getName()
    ));*/
    return $this->form->url()->extend()->set('formAction', $this->getName());
  }

  /**
   * Add bulk actions to the bulk menu
   *
   * Ex.:
   * $actions['action_name'] = 'Action Label';
   *
   * @param array $actions          
   */
  public function onCreateBulkOptions(array &$actions)
  {
  }

  /**
   * Inject query parameters for loading form data
   *
   * @param array $params          
   */
  public function onInjectQueryParams(&$params)
  {
    $elements = $this->getElements();
    
    $elements->rewind();
    
    while ($elements->valid()) {
      $element = $elements->current();
      $element->onInjectQueryParams($this, $params);
      
      $elements->next();
    }
    
    foreach ($this->form->getFilters() as $filter) {
      $filter->onInjectQueryParams($this, $params);
    }
  }

  /**
   * Fill form data with data sent via POST
   */
  public function onPostData()
  {
    $post = $this->form->getPostData();
    
    $id = $this->form->getId();
    
    $elements = $this->getElements();
    $filters = $this->form->getFilters();
    
    foreach ($this->formData as $index => &$row) {
      $row[Form::ID] = sy_get_param($id, $index);
      
      $elements->rewind();
      while ($elements->valid()) {
        $element = $elements->current();
        $element->onPostData($this, $row, $post[$index]);
        $elements->next();
      }

      foreach ($filters as $filter) {
          $filter->onPostData($this, $row, $post[$index]);
      }
    }
  }

  /**
   * Validate form data
   */
  public function onValidate()
  {
    $this->errors = array();
    
    $elements = $this->getElements();
    
    foreach ($this->formData as $index => $row) {
      
      $elements->rewind();
      
      while ($elements->valid()) {
        $element = $elements->current();
        $elements->next();
        
        if ($this->show($element->validate)) {
          
          try {
            $element->onValidate($this, $row);
          }
          catch (\Simplify\ValidationException $e) {
            $this->errors[$element->getName()] = $e->getErrors();
            
            $element->state = 'has-error';
            $element->stateMessage = $this->errors[$element->getName()];
          }
        }
      }
      
      try {
        $this->form->dispatch(Form::ON_VALIDATE, $this, $row);
      }
      catch (\Simplify\ValidationException $e) {
        $this->errors = array_merge_recursive($this->errors, $e->getErrors());
      }
    }
    
    if (! empty($this->errors)) {
      throw new \Simplify\ValidationException($this->errors);
    }
  }

  /**
   * Get the action name
   *
   * @return string
   */
  public function getName()
  {
    if (empty($this->name)) {
      $this->name = strtolower(join('', array_slice(explode('\\', get_class($this)), - 1)));
    }
    
    return $this->name;
  }

  /**
   * Get the action title
   *
   * @return string
   */
  public function getTitle()
  {
    if (empty($this->title)) {
      $this->title = Inflector::titleize($this->getName());
    }
    
    return $this->title;
  }

  /**
   * Get the action mask
   *
   * @return int
   */
  public function getActionMask()
  {
    return $this->actionMask;
  }

  /**
   * Get the elements for this action according to the action mask
   *
   * @return ElementIterator
   */
  public function getElements()
  {
    return $this->form->getElements($this);
  }

  /**
   * (non-PHPdoc)
   *
   * @see Renderable::getTemplateFilename()
   */
  public function getTemplateFilename()
  {
    return 'form_' . $this->getName();
  }

  /**
   * (non-PHPdoc)
   *
   * @see Renderable::getTemplatesPath()
   */
  /*
   * public function getTemplatesPath() { return
   * array(Simplify::config()->get('templates:dir') . '/form', FORM_DIR .
   * '/templates'); }
   */
  
  /**
   *
   * @return Repository
   */
  protected function repository()
  {
    return $this->form->repository();
  }

  /**
   * Get wheter this action should be shown for a specifig action mask
   *
   * @return boolean
   */
  public function show($actionMask)
  {
    return ($this->getActionMask() & $actionMask) == $actionMask;
  }

  /**
   *
   * @param int $actionMask          
   */
  public function setActionMask($actionMask)
  {
    $this->actionMask = $actionMask;
  }

}
