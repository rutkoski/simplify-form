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

namespace Simplify\Form;

use Simplify;
use Simplify\Form;
use Simplify\URL;
use Simplify\Inflector;
use Simplify\Renderable;

/**
 *
 * Provides basic functionality to form components
 *
 */
abstract class Component extends Renderable
{

  /**
   * The action mask is a bit mask for the actions this component belongs to.
   *
   * @var int
   */
  public $actionMask = Form::ACTION_ALL;

  /**
   *
   * @var \Simplify\Form
   */
  public $form;

  /**
   *
   * @var string
   */
  public $name;

  /**
   *
   * @var string
   */
  public $fieldName;

  /**
   *
   * @var string
   */
  public $label;

  /**
   *
   * @var string
   */
  public $style;

  /**
   *
   * @var string
   */
  public $id;

  /**
   *
   * @var mixed
   */
  public $defaultValue;

  /**
   * Always add the object to these actions
   *
   * @var int
   */
  protected $add = Form::ACTION_NONE;

  /**
   * Never add the object to these actions
   *
   * @var int
   */
  protected $remove = Form::ACTION_NONE;

  /**
   * Form hooks
   *
   * @var array
   */
  protected $hooks = array();
  
  /**
   * Constructor
   *
   * @param string $name repository column name
   * @param string $label element label
   */
  public function __construct($name, $label = null)
  {
    $this->name = $name;
    $this->label = $label;
  }

  /**
   *
   * @param string $serviceAction
   * @return URL
   */
  public function getServiceUrl($serviceAction)
  {
    $uploaderUrl = URL::make(null, null, false, null, URL::JSON);
    $uploaderUrl->set('formAction', 'services');
    $uploaderUrl->set('serviceName', $this->getName());
    $uploaderUrl->set('serviceAction', $serviceAction);
    return $uploaderUrl;
  }

  /**
   *
   * @return Component
   */
  public function getElementByName($name)
  {
    return $this->getName() == $name ? $this : null;
  }

  /**
   * Get the html id for this component.
   *
   * @return string
   */
  public function getId()
  {
    if (empty($this->id)) {
      $this->id = $this->getName();
    }

    return $this->id;
  }

  /**
   * Get the component name.
   *
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Get the database field name for this component.
   * If null, the component name is used.
   *
   * @return string
   */
  public function getFieldName()
  {
    if (empty($this->fieldName)) {
      $this->fieldName = $this->getName();
    }

    return $this->fieldName;
  }

  /**
   * Get the label for the component.
   *
   * @return string
   */
  public function getLabel()
  {
    if (is_null($this->label)) {
      $this->label = Inflector::humanize($this->getName());
    }
    return $this->label;
  }

  /**
   * (non-PHPdoc)
   * @see Renderable::getTemplateFilename()
   */
  public function getTemplateFilename()
  {
    if (!empty($this->style)) {
      return $this->style;
    }
    elseif (empty($this->template)) {
      $this->template = 'form_element_' . strtolower(join('', array_slice(explode('\\', get_class($this)), -1)));
    }

    return parent::getTemplateFilename();
  }

  /**
   * Get the value for the component passed via GET.
   *
   * @return mixed
   */
  public function getValue()
  {
    return Simplify::request()->get($this->getName(), $this->getDefaultValue());
  }

  /**
   * Get the default value for the component.
   *
   * @return mixed
   */
  public function getDefaultValue()
  {
    return $this->defaultValue;
  }

  /**
   * On execute callback. Runs on action execute and before render.
   *
   * @param Action $action current executing action
   */
  public function onExecute(Action $action)
  {
  }

  /**
   * On execute services callback. Component services called via AJAX.
   *
   * @param string $serviceAction the name of the service in the component being called
   */
  public function onExecuteServices($serviceAction)
  {
  }

  /**
   * On render callback. Renders the component.
   *
   * @param Action $action current executing action
   * @return IView
   */
  public function onRender(Action $action)
  {
    return $this->getView();
  }

  /**
   * On load data callback.
   *
   * @param Action $action current action
   * @param array $data form data
   * @param array $row database row
   */
  public function onLoadData(Action $action, &$data, $row)
  {
  }

  /**
   * On post data callback.
   *
   * @param Action $action current action
   * @param array $data form data
   * @param array $post post data
   */
  public function onPostData(Action $action, &$data, $post)
  {
  }

  /**
   * On inject query params callback.
   *
   * @param Action $action current action
   * @param array $params query parameters
   */
  public function onInjectQueryParams(Action $action, &$params)
  {
  }

  /**
   * On collect table data callback.
   *
   * @param array $row database row
   * @param array $data form data
   */
  public function onCollectTableData(Action $action, &$row, $data)
  {
  }

  /**
   * On before delete callback.
   *
   * @param Action $action current action
   * @param array $data form data
   */
  public function onBeforeDelete(Action $action, &$data)
  {
  }

  /**
   * On after delete callback.
   *
   * @param Action $action current action
   * @param array $data form data
   */
  public function onAfterDelete(Action $action, &$data)
  {
  }

  /**
   * On save callback.
   *
   * @param Action $action current action
   * @param array $data form data
   */
  public function onSave(Action $action, &$data)
  {
  }

  /**
   * Get wheter this component belongs to the given action mask.
   *
   * @return boolean
   */
  public function show($actionMask)
  {
    return (($this->add | $this->actionMask & ~$this->remove) & $actionMask) == $actionMask;
  }

  public function onCollectRequirements($schema)
  {
  }
  
  public function setOption($name, $value = null)
  {
      $this->$name = $value;
      return $this;
  }

  /**
   *
   */
  public function addListener($hook, callable $listener)
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
              call_user_func_array($listener, $args);
          }
      }
  }

}
