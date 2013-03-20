<?php

abstract class Simplify_Form_Action extends Simplify_Renderable
{

  /**
   *
   * @var Simplify_Form_
   */
  public $form;

  /**
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
   * Execute the action.
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
   * Render the action.
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
   * Create the action menu.
   *
   * @param Simplify_Menu $menu
   * @param Simplify_Form_Action $action
   */
  public function onCreateMenu(Simplify_Menu $menu, Simplify_Form_Action $action)
  {
  }

  /**
   * Create the menu for each row in the form.
   *
   * @param Simplify_Menu $menu
   * @param Simplify_Form_Action $action
   * @param array $row
   */
  public function onCreateItemMenu(Simplify_Menu $menu, Simplify_Form_Action $action, $row)
  {
  }

  /**
   * Add bulk actions to the bulk menu.
   * Ex.:
   * 	$actions['action_name'] = 'Action Label';
   *
   * @param array $actions
   */
  public function onCreateBulkOptions(array &$actions)
  {
  }

  /**
   * Inject query parameters for loading form data.
   *
   * @param array $params
   */
  public function onInjectQueryParams(&$params)
  {
  }

  /**
   * Fill form data with data sent via POST.
   */
  public function onPostData()
  {
    $data = s::request()->post('formData');
    $id = s::request()->post(Simplify_Form::ID);

    $elements = $this->getElements();
    $filters = $this->form->getFilters();

    foreach ($this->formData as $i => &$row) {
      $row[Simplify_Form::ID] = $id[$i];

      foreach ($filters as $filter) {
        $filter->onPostData($row, $data, $i);
      }

      foreach ($elements as $element) {
        $element->onPostData($row, $data, $i);
      }
    }
  }

  /**
   * Validate form data.
   */
  public function onValidate()
  {
    $elements = $this->getElements();

    foreach ($this->formData as $i => &$row) {
      $this->form->dispatch(Simplify_Form::ON_VALIDATE, $this, $row);

      foreach ($elements as $element) {
        $element->onValidate($this, $this->formData, $i);
      }
    }
  }

  /**
   * Get the action name.
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
   * Get the action title.
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
   * Get the action mask.
   *
   * @return int
   */
  public function getActionMask()
  {
    return $this->actionMask;
  }

  /**
   * Get the elements for this action according to the action mask.
   *
   * @return Simplify_Form_Element[]
   */
  public function getElements()
  {
    return $this->form->getElements($this->getActionMask());
  }

  /**
   *
   */
  public function getTemplateFilename()
  {
    return 'form_' . $this->getName();
  }

  /**
   *
   */
  public function getTemplatesPath()
  {
    return array(s::config()->get('templates_dir') . '/form', FORM_DIR . '/templates');
  }

  /**
   *
   * @return IRepository
   */
  protected function repository()
  {
    return $this->form->repository();
  }

  /**
   * Get wheter this action should be shown for a specifig action mask.
   *
   * @return boolean
   */
  public function show($actionMask)
  {
    return ($this->getActionMask() & $actionMask) == $actionMask;
  }

}
