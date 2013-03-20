<?php

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

  const ACTION_OPTIONS = 32;

  const ACTION_SERVICES = 64;

  const RESULT_SUCCESS = 1;

  const RESULT_ERROR = 2;

  const ID = '_id';

  /**
   *
   * @var string
   */
  public $table;

  /**
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
   *
   * @var int|int[]
   */
  public $id;

  /**
   *
   * @var string
   */
  public $name;

  /**
   *
   * @var string
   */
  public $title;

  /**
   *
   * @var Simplify_Form_Action[]
   */
  protected $actions = array();

  /**
   *
   * @var int
   */
  public $actionMask = 0;

  /**
   *
   * @var string
   */
  protected $defaultAction;

  /**
   *
   * @var Simplify_FormElement[]
   */
  protected $elements = array();

  /**
   *
   * @var Simplify_FormFilter[]
   */
  protected $filters = array();

  /**
   *
   * @var IRepository
   */
  protected $repository;

  /**
   *
   * @var string
   */
  protected $template = 'form_body';

  /**
   *
   * @var Simplify_URL
   */
  protected $url;

  /**
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
    
    return $this->getView();
  }

  /**
   * Add an action to the form
   * 
   * @param Simplify_Form_Action $action the action
   * @return Simplify_Form
   */
  public function addAction(Simplify_Form_Action $action)
  {
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
    $menu = new Simplify_Menu('form', null, Simplify_Menu::STYLE_TOOLBAR);
    $menu->addItem(new Simplify_Menu('main', null, Simplify_Menu::STYLE_BUTTON_GROUP));
    
    foreach ($this->getActions() as $_action) {
      $_action->onCreateMenu($menu, $action);
    }
    
    return $menu;
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
   * @return Simplify_FormElement
   */
  public function getElementByName($name)
  {
    foreach ($this->elements as &$element) {
      if ($element->getName() == $name) {
        return $element;
      }
    }
    
    return false;
  }

  /**
   *
   * @return Simplify_FormElement[]
   */
  public function getElements($actionMask)
  {
    $elements = array();
    
    $elements[$actionMask] = array();
    
    foreach ($this->elements as &$element) {
      if ($element->show($actionMask)) {
        $elements[$actionMask][] = &$element;
      }
    }
    
    return $elements[$actionMask];
  }

  /**
   *
   * @return Simplify_FormFilter[]
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
      $this->id = s::request()->method(Simplify_Request::GET) ? s::request()->get(Simplify_Form::ID) : s::request()->post(Simplify_Form::ID);
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
   *
   * @return string
   */
  public function getPrimaryKey()
  {
    if (empty($this->primaryKey)) {
      $this->primaryKey = Inflector::singularize($this->getTable()) . Simplify_Form::ID;
    }
    
    return $this->primaryKey;
  }

  public function onCreateItemMenu(Simplify_Menu $menu, Simplify_Form_Action $action, $data, $index)
  {
    foreach ($this->getActions() as $_action) {
      $_action->onCreateItemMenu($menu, $action, $data, $index);
    }
  }

  public function onCreateBulkOptions(array &$actions)
  {
    foreach ($this->getActions() as $action) {
      $action->onCreateBulkOptions($actions);
    }
  }

  /**
   *
   * @return IRepository
   */
  public function repository(IRepository $repository = null)
  {
    if ($repository instanceof IRepository) {
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
