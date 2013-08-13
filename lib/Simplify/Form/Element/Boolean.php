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
 * Boolean form element
 *
 */
class Simplify_Form_Element_Boolean extends Simplify_Form_Element
{

  /**
   *
   * @var string
   */
  public $trueLabel;

  /**
   *
   * @var mixed
   */
  public $trueValue = 1;

  /**
   *
   * @var string
   */
  public $falseLabel;

  /**
   *
   * @var mixed
   */
  public $falseValue = 0;

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onRender()
   */
  public function onRender(Simplify_Form_Action $action, $data, $index)
  {
    if (empty($this->trueLabel)) {
      $this->trueLabel = __('Yes');
    }

    if (empty($this->falseLabel)) {
      $this->falseLabel = __('No');
    }

    $this->set('trueLabel', $this->trueLabel);
    $this->set('trueValue', $this->trueValue);
    $this->set('falseLabel', $this->falseLabel);
    $this->set('falseValue', $this->falseValue);

    return parent::onRender($action, $data, $index);
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element::getDisplayValue()
   */
  public function getDisplayValue(Simplify_Form_Action $action, $data, $index)
  {
    return $data[$this->getName()] == $this->trueValue ? $this->trueLabel : $this->falseLabel;
  }

}
