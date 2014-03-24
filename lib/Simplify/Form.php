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
 * Form
 *
 */
class Simplify_Form extends Simplify_Renderable
{

  const ON_BEFORE_DELETE = 'onBeforeDelete';

  const ON_RENDER = 'onRender';

  const ON_VALIDATE = 'onValidate';

  const ACTION_NONE = 0;

  const ACTION_LIST = 1;

  const ACTION_VIEW = 2;

  const ACTION_EDIT = 4;

  const ACTION_CREATE = 8;

  const ACTION_FORM = 12;

  const ACTION_DELETE = 16;

  const ACTION_ALL = 31;

  const ACTION_CONFIG = 32;

  const ACTION_SERVICES = 64;

  const RESULT_SUCCESS = 1;

  const RESULT_ERROR = 2;

  const ID = '_id';

  const SERVICE_UPLOAD = 'upload';

  /**
   * Table name
   *
   * @var string
   */
  public $table;

  /**
   * Primary key column
   *
   * @var string
   */
  public $primaryKey;

  /**
   *
   * @var string
   */
  public $label;

  /**
   * Primary key value(s)
   *
   * @var int|int[]
   */
  public $id;

  /**
   * Form name
   *
   * @var string
   */
  public $name;

  /**
   * Form title
   *
   * @var string
   */
  public $title;

  /**
   * Form actions
   *
   * @var Simplify_Form_Action[]
   */
  protected $actions = array();

  /**
   * Action mask
   *
   * @var int
   */
  public $actionMask = 0;

  /**
   *
   * @var boolean
   */
  public $showMenu = true;

  /**
   *
   * @var boolean
   */
  public $showItemMenu = true;

  /**
   * Default action name
   *
   * @var string
   */
  protected $defaultAction;

  /**
   * Form elements
   *
   * @var Simplify_Form_Element[]
   */
  protected $elements = array();

  /**
   * Form filters
   *
   * @var Simplify_Form_Filter[]
   */
  protected $filters = array();

  /**
   * Form repository
   *
   * @var Simplify_Form_Repository
   */
  protected $repository;

  /**
   * Form template
   *
   * @var string
   */
  protected $template = 'form_body';

  /**
   * Form url
   *
   * @var Simplify_URL
   */
  protected $url;

  /**
   * Form hooks
   *
   * @var array
   */
  protected $hooks = array();

  /**
   * Construct a new form object
   *
   * @param string $name a $name that identifies the form
   */
  public function __construct($name)
  {
    $this->name = $name;
  }

  /**
   *
   * @param string $table
   * @param string $primaryKey
   */
  public function setTable($table, $primaryKey = null)
  {
    $this->table = $table;
    $this->primaryKey = $primaryKey;
  }

  /**
   * Execute the action and return the result
   *
   * @param string $action action name
   * @return mixed
   */
  public function execute($action = null)
  {
    $Action = $this->getAction($action);

    $result = $Action->onExecute();

    return $result;
  }

  /**
   * Render the action and return the result view
   *
   * @param string $action action name
   * @return Simplify_View
   */
  public function render($action = null)
  {
    $Action = $this->getAction($action);

    $result = $Action->onRender();

    $this->set('actionBody', $result);

    if (!s::request()->json()) {
      $this->set('title', $this->getTitle());
      $this->set('menu', $this->createMenu($Action));
    }

    $this->set('showMenu', $this->showMenu);

    return $this;//->getView();
  }

  public function jsonSerialize()
  {
    $data = array();
    $data['action'] = $this->actionBody;
    return $data;
  }

  /**
   * Add an action to the form
   *
   * @param Simplify_Form_Action $action the action
   * @return Simplify_Form
   */
  public function addAction(Simplify_Form_Action $action)
  {
    if (isset($this->actions[$action->getName()])) {
      throw new Exception("Could not add action: there is already an action called {$action->getName()}");
    }

    $action->form = $this;

    if (empty($this->actions)) {
      $this->defaultAction = $action->getName();
    }

    $this->actions[$action->getName()] = $action;

    return $this;
  }

  /**
   * Add an element to the form
   *
   * @param Simplify_Form_Element $element the element
   * @param int $actionMask a bit mask of the actions the element applies to
   * @param int $index the position where the element will be added, leave blank to add the element at the end
   * @return Simplify_Form_Element the element
   */
  public function addElement(Simplify_Form_Element $element, $actionMask = Simplify_Form::ACTION_ALL, $index = null)
  {
    $element->form = $this;
    $element->actionMask = $actionMask;

    $this->actionMask = $this->actionMask | $actionMask;

    if (is_null($index)) {
      $this->elements[] = $element;
    }
    else {
      array_splice($this->elements, $index, 0, array($element));
    }

    return $element;
  }

  /**
   * Add a filter to the form
   *
   * @param Simplify_Form_Filter $filter the filter
   * @param unknown_type $actionMask a bit mask of the actions the filter applies to
   * @return Simplify_Form_Filter
   */
  public function addFilter(Simplify_Form_Filter $filter, $actionMask = Simplify_Form::ACTION_ALL)
  {
    $filter->form = $this;
    $filter->actionMask = $actionMask;

    $this->filters[] = $filter;

    return $filter;
  }

  /**
   * Calls each of the form's actions and let's them add itens the the form menu
   *
   * @param Simplify_Form_Action $action current action
   * @return Simplify_Menu the form menu
   */
  protected function createMenu(Simplify_Form_Action $action)
  {
    if ($this->showMenu) {
      $menu = new Simplify_Menu('form', null, Simplify_Menu::STYLE_TOOLBAR);

      $menu->addItem(new Simplify_Menu('main', null, Simplify_Menu::STYLE_BUTTON_GROUP));

      foreach ($this->getActions() as $_action) {
        $_action->onCreateMenu($menu, $action);
      }

      return $menu;
    }
  }

  /**
   *
   * @param string $action
   * @return Simplify_Form_Action
   */
  public function getAction($action = null)
  {
    if (empty($action)) {
      $action = s::request()->get('formAction', $this->defaultAction, true);
    }

    return $this->actions[$action];
  }

  /**
   *
   * @return Simplify_Form_Action[]
   */
  public function getActions()
  {
    return $this->actions;
  }

  /**
   *
   * @return Simplify_Form_Element
   */
  public function getElementByName($name)
  {
    foreach ($this->elements as $element) {
      $found = $element->getElementByName($name);

      if ($found) {
        return $found;
      }
    }

    return null;
  }

  /**
   *
   * @param Simplify_Form_Action $action
   * @return Simplify_Form_Element[]
   */
  public function getElements(Simplify_Form_Action $action)
  {
    return new Simplify_Form_ElementIterator($this->elements, $action->getActionMask());
  }

  /**
   *
   * @return Simplify_Form_Filter[]
   */
  public function getFilters()
  {
    return $this->filters;
  }

  /**
   *
   * @return int|int[]
   */
  public function getId()
  {
    if (empty($this->id)) {
      $this->id = array_filter(
        (array) (s::request()->method(Simplify_Request::GET) ? s::request()->get(Simplify_Form::ID) : s::request()->post(
          Simplify_Form::ID)));
    }

    return $this->id;
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
   * @return string
   */
  public function getTitle()
  {
    if (empty($this->title)) {
      $this->title = Simplify_Inflector::titleize(Simplify_Inflector::pluralize($this->getName()));
    }

    return $this->title;
  }

  /**
   *
   * @return string
   */
  public function getName()
  {
    return strtolower($this->name);
  }

  /**
   *
   * @return string
   */
  public function getTable()
  {
    if (empty($this->table)) {
      $this->table = Simplify_Inflector::tableize($this->getName());
    }

    return $this->table;
  }

  /**
   *
   * @return string
   */
  public function getLabel()
  {
    if (empty($this->label)) {
      $this->label = $this->getPrimaryKey();
    }

    return $this->label;
  }

  /**
   *
   * @return string
   */
  public function getPrimaryKey()
  {
    if (empty($this->primaryKey)) {
      $this->primaryKey = Simplify_Inflector::singularize($this->getTable()) . Simplify_Form::ID;
    }

    return $this->primaryKey;
  }

  /**
   *
   * @param Simplify_Menu $menu menu for form row
   * @param Simplify_Form_Action $action current action
   * @param array $data form data
   */
  public function onCreateItemMenu(Simplify_Menu $menu, Simplify_Form_Action $action, $data)
  {
    if ($this->showItemMenu) {
      foreach ($this->getActions() as $_action) {
        $_action->onCreateItemMenu($menu, $action, $data);
      }
    }
  }

  /**
   *
   * @param array $actions
   */
  public function onCreateBulkOptions(array &$actions)
  {
    foreach ($this->getActions() as $action) {
      $action->onCreateBulkOptions($actions);
    }
  }

  /**
   *
   * @return Simplify_Form_Repository
   */
  public function repository(Simplify_Form_Repository $repository = null)
  {
    if ($repository instanceof Simplify_Form_Repository) {
      $this->repository = $repository;
    }

    if (empty($this->repository)) {
      $this->repository = new Simplify_Form_Repository($this->getTable(), $this->getPrimaryKey());
    }

    return $this->repository;
  }

  /**
   *
   * @return boolean
   */
  public function show($actionMask)
  {
    return ($this->actionMask & $actionMask) == $actionMask;
  }

  /**
   *
   * @return Simplify_URL
   */
  public function url()
  {
    if (empty($this->url)) {
      $this->url = new Simplify_URL(null, array('formAction' => s::request()->get('formAction')));
    }

    return $this->url;
  }

  /**
   *
   */
  public function addListener($hook, $listener)
  {
    $this->hooks[$hook][] = $listener;
  }

  /**
   *
   */
  public function dispatch($hook)
  {
    if (isset($this->hooks[$hook])) {
      $args = func_get_args();

      unset($args[0]);

      foreach ($this->hooks[$hook] as $listener) {
        call_user_func_array(array($listener, $hook), $args);
      }
    }
  }

}
