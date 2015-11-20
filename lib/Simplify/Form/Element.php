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

use Simplify\Db\QueryParameters;
use Simplify\Form;
use Simplify\Inflector;

/**
 *
 * Form elements
 *
 */
abstract class Element extends Component
{

  /**
   *
   * @var int|boolean
   */
  public $validate = true;

  /**
   *
   * @var int|boolean
   */
  public $unique = false;

  /**
   *
   * @var string
   */
  public $state;

  /**
   *
   * @var string
   */
  public $stateMessage;

  /**
   *
   * @var string[]
   */
  public $errors;
  
  /**
   * 
   * @var bool
   */
  public $disabled;

  /**
   * (non-PHPdoc)
   * @see Component::getValue()
   */
  public function getValue($data)
  {
    return sy_get_param($data, $this->getName(), $this->getDefaultValue());
  }

  /**
   * Get the display value for the element.
   *
   * @param Action $action current action
   * @param array $data form data
   * @param mixed $index
   * @return string the display value
   */
  public function getDisplayValue(Action $action, $data, $index)
  {
    return sy_get_param($data, $this->getName());
  }

  /**
   * Get the input name for a given row $index.
   *
   * @param mixed $index
   * @return string
   */
  public function getInputName($index)
  {
    return "formData[" . implode('][', (array) $index) . "][" . $this->getName() . "]";
  }

  /**
   *
   * @return string
   */
  public function getElementClass()
  {
    return Inflector::underscore(get_class($this));
  }

  /**
   *
   * @param mixed[] $index
   * @return string
   */
  public function getElementId($index)
  {
    return "form_data_" . implode('_', (array) $index) . "_" . $this->getName();
  }

  /**
   * On validate callback.
   *
   * @param Action $action current action
   * @param array $data form data
   */
  public function onValidate(Action $action, $data)
  {
    if ($this->unique && $action->show($this->unique)) {
      $unique = $this->getError('unique', __('Value must be unique'));

      $rule = new \Simplify\Form\Validation\Unique($unique, $this, sy_get_param($data, $this->form->getPrimaryKey()));
      $rule->validate($this->getValue($data));
    }
  }

  /**
   *
   * @param Action $action
   * @param string[] $headers
   */
  public function onRenderHeaders(Action $action, &$headers)
  {
    $headers[$this->getName()] = $this->getLabel();
  }

  /**
   * Get the display value for the element.
   *
   * @param Action $action current action
   * @param array $line the table row
   * @param array $data form data
   * @param mixed $index
   * @return string the display value
   */
  public function onRenderLine(Action $action, &$line, $data, $index)
  {
    $element = array();

    $element['id'] = $this->getElementId($index);
    $element['name'] = $this->getInputName($index);
    $element['class'] = $this->getElementClass();
    $element['label'] = $this->getLabel();
    $element['controls'] = $this->getDisplayValue($action, $data, $index);

    $line['elements'][$this->getName()] = $element;
  }

  /**
   *
   * @param Action $action current action
   * @param array $line the form line
   * @param array $data form data
   * @param mixed $index
   */
  public function onRenderControls(Action $action, &$line, $data, $index)
  {
    $element = array();

    $element['id'] = $this->getElementId($index);
    $element['name'] = $this->getInputName($index);
    $element['class'] = $this->getElementClass();
    $element['label'] = $this->getLabel();
    $element['controls'] = $this->onRender($action, $data, $index)->render();

    $element['state'] = $this->state;
    $element['stateMessage'] = $this->stateMessage;

    $line['elements'][$this->getName()] = $element;
  }

  /**
   * (non-PHPdoc)
   * @see Component::onRender()
   */
  public function onRender(Action $action, $data, $index)
  {
    $exists = (!empty($data[Form::ID]));

    $this->set('state', $this->state);
    $this->set('stateMessage', $this->stateMessage);
    $this->set('exists', $exists);
    $this->set(Form::ID, sy_get_param($data, Form::ID));
    $this->set('id', $this->getElementId($index));
    $this->set('inputName', $this->getInputName($index));
    $this->set('name', $this->getName());
    $this->set('class', $this->getElementClass());
    $this->set('index', $index);
    $this->set('label', $this->getLabel());
    $this->set('value', $this->getValue($data));
    $this->set('action', $action);
    $this->set('disabled', $this->disabled);

    return parent::onRender($action);
  }

  /**
   * (non-PHPdoc)
   * @see Component::onInjectQueryParams()
   */
  public function onInjectQueryParams(Action $action, &$params)
  {
    if ($this->getFieldName()) {
      $params[QueryParameters::SELECT][] = $this->getFieldName();
    }
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Component::onLoadData()
   */
  public function onLoadData(Action $action, &$data, $row)
  {
    if (isset($row[$this->getFieldName()])) {
      $data[$this->getName()] = $row[$this->getFieldName()];
    }
  }

  /**
   * (non-PHPdoc)
   * @see Component::onPostData()
   */
  public function onPostData(Action $action, &$data, $post)
  {
    $data[$this->getName()] = sy_get_param($post, $this->getName(), $this->getDefaultValue());
  }

  /**
   * (non-PHPdoc)
   * @see Component::onCollectTableData()
   */
  public function onCollectTableData(Action $action, &$row, $data)
  {
    if (isset($data[$this->getName()])) {
      $row[$this->getFieldName()] = $data[$this->getName()];
    }
  }

  public function getError($id, $default = null)
  {
    return sy_get_param($this->errors, $id, $default);
  }

}
