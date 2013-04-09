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
 * Base class for form elements that allow for single selection
 *
 */
abstract class Simplify_Form_Element_Base_SingleSelection extends Simplify_Form_Element
{

  /**
   * Selection options
   *
   * @var array
   */
  public $options;

  /**
   *
   * @var boolean
   */
  public $required = true;

  /**
   * Show option for empty value
   *
   * @var boolean
   */
  public $showEmpty = true;

  /**
   *
   * @var string
   */
  public $emptyLabel = '';

  /**
   *
   * @var mixed
   */
  public $emptyValue;

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onRender()
   */
  public function onRender(Simplify_Form_Action $action, $data, $index)
  {
    $this->set('options', $this->getOptions());
    $this->set('showEmpty', $this->showEmpty);
    $this->set('emptyLabel', $this->emptyLabel);
    $this->set('emptyValue', $this->emptyValue);

    return parent::onRender($action, $data, $index);
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onValidate()
   */
  public function onValidate(Simplify_Form_Action $action, Simplify_Validation_DataValidation $rules)
  {
    if ($this->required) {
      $rule = new Simplify_Validation_StrictEqual('Invalid selection', $this->emptyValue);
      $rules->setRule($this->getName(), $rule);
    }
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onPostData()
   */
  public function onPostData(Simplify_Form_Action $action, &$data, $post)
  {
    $value = sy_get_param($post, $this->getName(), $this->getDefaultValue());
    $data[$this->getName()] = $value == '' ? null : $value;
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element::getDisplayValue()
   */
  public function getDisplayValue(Simplify_Form_Action $action, $data, $index)
  {
    return sy_get_param($this->getOptions(), $this->getValue($data));
  }

  /**
   *
   * @return mixed[string]
   */
  public function getOptions()
  {
    $options = (array) $this->options;

    if ($this->showEmpty) {
      $empty = array($this->emptyValue => $this->emptyLabel);
      $options = $empty + $options;
    }

    return $options;
  }

}
