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
 * Abstract class for form actions such as list, edit, create...
 *
 */
abstract class Simplify_Form_Action extends Simplify_Renderable
{

  /**
   *
   * @var Simplify_Form
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
  protected $formData;

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
   * Execute the action
   *
   * @return void
   */
  public function onExecute()
  {
    $elements = $this->getElements();

    foreach ($elements as $element) {
      $element->onExecute($this);
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
    $this->set('title', $this->getTitle());

    $filters = array();
    foreach ($this->form->getFilters() as $filter) {
      $filters[$filter->getLabel()] = $filter->onRender($this);
    }
    $this->set('filters', $filters);

    return $this->getView();
  }

  /**
   * Load data from repository
   */
  protected function onLoadData()
  {
  }

  /**
   * Save data to repository
   */
  protected function onSave()
  {
  }

  /**
   * Create the action menu
   *
   * @param Simplify_Menu $menu
   * @param Simplify_Form_Action $action
   */
  public function onCreateMenu(Simplify_Menu $menu, Simplify_Form_Action $action)
  {
  }

  /**
   * Create the menu for each row in the form
   *
   * @param Simplify_Menu $menu
   * @param Simplify_Form_Action $action
   * @param array $row
   */
  public function onCreateItemMenu(Simplify_Menu $menu, Simplify_Form_Action $action, $row)
  {
  }

  /**
   * Add bulk actions to the bulk menu
   *
   * Ex.:
   * 	$actions['action_name'] = 'Action Label';
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
  }

  /**
   * Fill form data with data sent via POST
   */
  public function onPostData()
  {
    $post = s::request()->post('formData');
    $files = s::request()->files('formData');

    $id = $this->form->getId();

    $elements = $this->getElements();
    $filters = $this->form->getFilters();

    foreach ($this->formData as $index => &$row) {
      $row[Simplify_Form::ID] = $id[$index];

      if (! empty($files)) {
        foreach ($files as $k => $file) {
          foreach ($file[$index] as $field => $value) {
            $post[$index][$field][$k] = $value['file'];
          }
        }
      }

      foreach ($filters as $filter) {
        $filter->onPostData($this, $row, $post[$index]);
      }

      foreach ($elements as $element) {
        $element->onPostData($this, $row, $post[$index]);
      }
    }
  }

  /**
   * Validate form data
   */
  public function onValidate()
  {
    $rules = new Simplify_Validation_DataValidation();

    $elements = $this->getElements();

    foreach ($elements as $element) {
      $element->onValidate($this, $rules);
    }

    $this->form->dispatch(Simplify_Form::ON_VALIDATE, $this, $rules);

    foreach ($this->formData as $index => &$data) {
      $rules->validate($data);
    }

    /*$errors = array();

    $elements = $this->getElements();

    foreach ($this->formData as $index => &$data) {
      $this->form->dispatch(Simplify_Form::ON_VALIDATE, $this, $data);

      foreach ($elements as $element) {
        try {
          $element->onValidate($this, $data);
        }
        catch (Simplify_ValidationException $e) {
          $errors = array_merge($errors, (array) $e->getErrors());
        }
      }
    }

    if (! empty($errors)) {
      throw new Simplify_ValidationException($errors);
    }*/
  }

  /**
   * Get the action name
   *
   * @return string
   */
  public function getName()
  {
    if (empty($this->name)) {
      $this->name = strtolower(substr(get_class($this), strlen('Simplify_Form_Action_')));
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
   * @return Simplify_Form_Element[]
   */
  public function getElements()
  {
    return $this->form->getElements($this->getActionMask());
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Renderable::getTemplateFilename()
   */
  public function getTemplateFilename()
  {
    return 'form_' . $this->getName();
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Renderable::getTemplatesPath()
   */
  public function getTemplatesPath()
  {
    return array(s::config()->get('templates_dir') . '/form', FORM_DIR . '/templates');
  }

  /**
   *
   * @return Simplify_Form_Repository
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

}
