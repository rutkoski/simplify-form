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
 * Form elements
 *
 */
abstract class Simplify_Form_Element extends Simplify_Form_Component
{

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::getValue()
   */
  public function getValue($data)
  {
    return sy_get_param($data, $this->getName(), $this->getDefaultValue());
  }

  /**
   * Get the display value for the element.
   *
   * @param Simplify_Form_Action $action current action
   * @param array $data form data
   * @param mixed $index
   * @return string the display value
   */
  public function getDisplayValue(Simplify_Form_Action $action, $data, $index)
  {
    return $data[$this->getName()];
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
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onRender()
   */
  public function onRender(Simplify_Form_Action $action, $data, $index)
  {
    $exists = (!empty($data[Simplify_Form::ID]));

    $this->set('exists', $exists);
    $this->set(Simplify_Form::ID, $data[Simplify_Form::ID]);
    $this->set('id', $this->getElementId($index));
    $this->set('inputName', $this->getInputName($index));
    $this->set('name', $this->getName());
    $this->set('class', $this->getElementClass());
    $this->set('index', $index);
    $this->set('label', $this->getLabel());
    $this->set('value', $this->getValue($data));
    $this->set('action', $action);

    return parent::onRender($action);
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onInjectQueryParams()
   */
  public function onInjectQueryParams(Simplify_Form_Action $action, &$params)
  {
    $params[Simplify_Db_QueryParameters::SELECT][] = $this->getFieldName();
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onLoadData()
   */
  public function onLoadData(Simplify_Form_Action $action, &$data, $row)
  {
    if (isset($row[$this->getFieldName()])) {
      $data[$this->getName()] = $row[$this->getFieldName()];
    }
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onPostData()
   */
  public function onPostData(Simplify_Form_Action $action, &$data, $post)
  {
    $data[$this->getName()] = sy_get_param($post, $this->getName(), $this->getDefaultValue());
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onCollectTableData()
   */
  public function onCollectTableData(&$row, $data)
  {
    $row[$this->getFieldName()] = $data[$this->getName()];
  }

}
