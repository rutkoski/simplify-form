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

namespace Simplify\Form\Filter;

/**
 *
 * Form filter with select element
 *
 */
class Select extends \Simplify\Form\Filter
{

  /**
   * Selection options
   *
   * @var mixed
   */
  public $options = false;

  /**
   * Show option for empty value
   *
   * @var boolean
   */
  public $showEmpty = false;

  /**
   *
   * @var string
   */
  public $emptyLabel = '';

  /**
   *
   * @var mixed
   */
  public $emptyValue = '';

  /**
   *
   * @param string|boolean $label
   * @param mixed $value
   */
  public function showEmpty($label = '', $value = '')
  {
    $this->showEmpty = $label !== false;
    $this->emptyLabel = $label;
    $this->emptyValue = $value;
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Filter::onRender()
   */
  public function onRender(\Simplify\Form\Action $action)
  {
    $this->set('options', $this->getOptions());
    $this->set('showEmpty', $this->showEmpty);
    $this->set('emptyLabel', $this->emptyLabel);
    $this->set('emptyValue', $this->emptyValue);

    return parent::onRender($action);
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Filter::getValue()
   */
  public function getValue()
  {
    $value = \Simplify::request()->get($this->getName());

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
   * @see \Simplify\Form\Component::onInjectQueryParams()
   */
  public function onInjectQueryParams(\Simplify\Form\Action $action, &$params)
  {
    parent::onInjectQueryParams($action, $params);

    $value = $this->getValue();

    if ($value != $this->emptyValue) {
      $name = $this->getFieldName();

      $params[\Simplify\Db\QueryParameters::WHERE][] = "{$name} = :{$name}";
      $params[\Simplify\Db\QueryParameters::DATA][$name] = $value;
    }
  }

  /**
   *
   * @return mixed[string]
   */
  public function getOptions()
  {
    if ($this->options === false) {
      $options = \Simplify::db()
        ->query()
        ->from($this->form->getTable())
        ->select($this->getFieldName())
        ->orderBy($this->getFieldName())
        ->execute()
        ->fetchCol();

      $options = array_combine($options, $options);
    } else {
      $options = (array) $this->options;
    }

    if ($this->showEmpty) {
      $empty = array($this->emptyValue => $this->emptyLabel);
      $options = $empty + $options;
    }

    return $options;
  }

}
