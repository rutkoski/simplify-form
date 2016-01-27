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

namespace Simplify;

use Simplify;
use Simplify\Inflector;
use Simplify\URL;
use Simplify\Renderable;
use Simplify\Menu;
use Simplify\Form\Action;
use Simplify\Form\Element;
use Simplify\Form\ElementIterator;
use Simplify\Form\Repository;

/**
 *
 * Form
 *
 */
class Form extends Renderable
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
  
  const MODE_HTML = 'html';
  
  const MODE_AJAX = 'ajax';

  const ICON_LIST = 'list';
  
  const ICON_EDIT = 'pencil';

  const ICON_CREATE = 'plus';

  const ICON_DELETE = 'trash';
  
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
   * @var \Simplify\Form\Action[]
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
   * @var \Simplify\Form\Element[]
   */
  protected $elements = array();

  /**
   * Form filters
   *
   * @var Simplify\Form\Filter[]
   */
  protected $filters = array();

  /**
   * Form repository
   *
   * @var \Simplify\Form\Repository
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
   * @var \Simplify\URL
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
  public function __construct($name, $title = null)
  {
    $this->name = $name;
    $this->title = $title;
  }

  /**
   *
   * @param string $table
   * @param string $primaryKey
   */
  public function setTable($table, $primaryKey = null, $label = null)
  {
    $this->table = $table;
    $this->primaryKey = $primaryKey;
    $this->label = $label;
  }

  /**
   * Execute the action and return the result
   *
   * @param string $action action name
   * @return mixed
   */
  public function execute($action = null)
  {
    if (empty($action)) {
        $action = $this->getActionName();
    }

    if ($action === 'services') {
      $result = $this->executeServices();
    }
    
    else {
      $Action = $this->getAction($action);

      $result = $Action->onExecute();
    }

    //if ($Action->formMode('ajax')) {
      //$this->setLayout('form_ajax_body');
      
      //\Simplify::response()->output($this->render($action));
    //}
    
    return $result;
  }

  /**
   * Render the action and return the result view
   *
   * @param string $action action name
   * @return \Simplify\Form
   */
  public function render($action = null)
  {
    if (empty($action)) {
        $action = $this->getActionName();
    }
    
    if ($action === 'services') {
        return $this;
    }
    
    $Action = $this->getAction($action);
    
    $result = $Action->onRender();

    $this->set('actionBody', $result);

    if (\Simplify::request()->ajax()) {
      $this->setLayout('form_ajax_body');

     \Simplify::response()->output($this);
    }
    
    $this->set('title', $this->getTitle());
    $this->set('menu', $this->createMenu($Action));
    $this->set('showMenu', $this->showMenu);

    \Simplify\AssetManager::load('moment/moment.min.js', 'vendor');
    \Simplify\AssetManager::load('moment/lang/pt-br.js', 'vendor');
    
    \Simplify\AssetManager::load('fullcalendar/fullcalendar.min.js', 'vendor');
    \Simplify\AssetManager::load('fullcalendar/fullcalendar.min.css', 'vendor');
    \Simplify\AssetManager::load('fullcalendar/lang/pt-br.js', 'vendor');

    \Simplify\AssetManager::load('eonasdan-bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css', 'vendor');
    \Simplify\AssetManager::load('eonasdan-bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js', 'vendor');

    \Simplify\AssetManager::load('ckeditor/ckeditor.js', 'vendor', 0, false);
    
    \Simplify\AssetManager::load('simplify-form.js', 'vender');
    
    return $this;
  }

  public function executeServices()
  {
    $serviceName = \Simplify::request()->get('serviceName');
    $serviceAction = \Simplify::request()->get('serviceAction');

    $service = $this->getElementByName($serviceName);
    
    $response = $service->onExecuteServices($serviceAction);

    return $response;
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
   * @param \Simplify\Form\Action $action the action
   * @return \Simplify\Form
   */
  public function addAction(Action $action)
  {
    if (isset($this->actions[$action->getName()])) {
      throw new \Exception("Could not add action: there is already an action called {$action->getName()}");
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
   * @param \Simplify\Form\Element $element the element
   * @param int $actionMask a bit mask of the actions the element applies to
   * @param int $index the position where the element will be added, leave blank to add the element at the end
   * @return \Simplify\Form\Element the element
   */
  public function addElement(Element $element, $actionMask = Form::ACTION_ALL, $index = null)
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
   * @param \Simplify\Form\Filter $filter the filter
   * @param unknown_type $actionMask a bit mask of the actions the filter applies to
   * @return \Simplify\Form\Filter
   */
  public function addFilter(\Simplify\Form\Filter $filter, $actionMask = Form::ACTION_ALL)
  {
    $filter->form = $this;
    $filter->actionMask = $actionMask;

    $this->filters[] = $filter;

    return $filter;
  }

  /**
   * Calls each of the form's actions and let's them add itens the the form menu
   *
   * @param \Simplify\Form\Action $action current action
   * @return \Simplify\Menu the form menu
   */
  protected function createMenu(Action $action)
  {
    if ($this->showMenu) {
      $menu = new Menu('form');

      $menu->addItem(new Menu('main'));

      foreach ($this->getActions() as $_action) {
        $_action->onCreateMenu($menu, $action);
      }

      return $menu;
    }
  }

  /**
   *
   * @param string $action
   * @return \Simplify\Form\Action
   */
  public function getAction($action = null)
  {
    if (empty($action)) {
      $action = $this->getActionName();
    }
    
    if (! isset($this->actions[$action])) {
      throw new \Exception("Unknown action: <b>{$action}</b>");
    }

    return $this->actions[$action];
  }

  /**
   *
   * @param string $action
   * @return \Simplify\Form\Action
   */
  public function getActionName()
  {
    return Simplify::request()->get('formAction', $this->defaultAction, true);
  }
  
  /**
   *
   * @return \Simplify\Form\Action[]
   */
  public function getActions()
  {
    return $this->actions;
  }

  /**
   *
   * @return \Simplify\Form\Element
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
   * @return \Simplify\Form\Element
   */
  public function getElementByType($type)
  {
      foreach ($this->elements as $element) {
          $found = $element->getElementByType($type);
  
          if ($found) {
              return $found;
          }
      }
  
      return null;
  }
  
  /**
   *
   * @param \Simplify\Form\Action $action
   * @return \Simplify\Form\Element[]
   */
  public function getElements(Action $action = null)
  {
    return new ElementIterator($this->elements, $action ? $action->getActionMask() : Form::ACTION_ALL);
  }

  /**
   *
   * @return \Simplify\Form\Filter[]
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
      $this->id = (array) (Simplify::request()->method(Request::GET) ? Simplify::request()->get(Form::ID) : Simplify::request()->post(Form::ID));
    }

    return $this->id;
  }

  /**
   *
   */
  public function getTemplatesPath()
  {
    return array(Simplify::config()->get('templates_dir') . '/form', FORM_DIR . '/templates');
  }

  /**
   *
   * @return string
   */
  public function getTitle()
  {
    if (empty($this->title)) {
      $this->title = Inflector::titleize(Inflector::pluralize($this->getName()));
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
      $this->table = Inflector::tableize($this->getName());
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
   * Get post/files
   * 
   * @return \Simplify\Simplify_Data_View>
   */
  public function getPostData()
  {
    $post = \Simplify::request()->post('formData');
  
    $files = \Simplify::request()->files('formData');
  
    if (! empty($files)) {
      $files = sy_form_fix_files_array($files);
      $post = sy_form_array_merge_recursive($post, $files);
    }
  
    return $post;
  }
  
  /**
   *
   * @return string
   */
  public function getPrimaryKey()
  {
    if (empty($this->primaryKey)) {
      $this->primaryKey = Inflector::singularize($this->getTable()) . Form::ID;
    }

    return $this->primaryKey;
  }

  /**
   *
   * @param \Simplify\Menu $menu menu for form row
   * @param \Simplify\Form\Action $action current action
   * @param array $data form data
   */
  public function onCreateItemMenu(\Simplify\Menu $menu, \Simplify\Form\Action $action, $data)
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
   * @return \Simplify\Form\Repository
   */
  public function repository(Repository $repository = null)
  {
    if ($repository instanceof Repository) {
      $this->repository = $repository;
    }

    if (empty($this->repository)) {
      $this->repository = new Repository($this->getTable(), $this->getPrimaryKey());
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
   * @return \Simplify\URL
   */
  public function url()
  {
    if (empty($this->url)) {
      $this->url = new URL(null, array('formAction' => \Simplify::request()->get('formAction')));
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
