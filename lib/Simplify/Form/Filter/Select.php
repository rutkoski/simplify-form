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
 * Form filter with select element
 *
 */
class Simplify_Form_Filter_Select extends Simplify_Form_Filter
{

  /**
   * Selection options
   *
   * @var array
   */
  public $options;

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
   * @see Simplify_Form_Filter::onRender()
   */
  public function onRender(Simplify_Form_Action $action)
  {
    $this->set('options', $this->getOptions());
    $this->set('showEmpty', $this->showEmpty);
    $this->set('emptyLabel', $this->emptyLabel);
    $this->set('emptyValue', $this->emptyValue);

    return parent::onRender($action);
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::getValue()
   */
  public function getValue()
  {
    $value = s::request()->get($this->getName());

    // if show empty is true, default value makes no sense...
    if ('' . $value == '' . $this->emptyValue && !$this->showEmpty) {
      if ('' . $this->getDefaultValue() == '' . $this->emptyValue) {
        $options = $this->getOptions();
        $value = array_shift(array_keys($options));
      }
      else {
        $value = $this->getDefaultValue();
      }
    }

    return $value;
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onInjectQueryParams()
   */
  public function onInjectQueryParams(Simplify_Form_Action $action, &$params)
  {
    parent::onInjectQueryParams($action, $params);

    $value = $this->getValue();

    if ($value != $this->emptyValue) {
      $name = $this->getFieldName();

      $params[Simplify_Db_QueryParameters::WHERE][] = "{$name} = :{$name}";
      $params[Simplify_Db_QueryParameters::DATA][$name] = $value;
    }
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
