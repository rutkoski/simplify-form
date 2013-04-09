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
 * Textarea form element
 *
 */
class Simplify_Form_Element_Textarea extends Simplify_Form_Element
{

  /**
   * Truncate data on list actions
   *
   * @var boolean|int
   */
  public $truncate = 80;

  /**
   *
   * @var int|boolean
   */
  public $minLength = false;

  /**
   *
   * @var int
   */
  public $maxLength = false;

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onValidate()
   */
  public function onValidate(Simplify_Form_Action $action, Simplify_Validation_DataValidation $rules)
  {
    $msg = '%s must have between %s and %s characters';
    $msg = sprintf($msg, $this->getLabel(), $this->minLength, $this->maxLength);

    $rule = new Simplify_Validation_Length($msg, $this->minLength, $this->maxLength);
    $rules->setRule($this->getName(), $rule);
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element::getDisplayValue()
   */
  public function getDisplayValue(Simplify_Form_Action $action, $data, $index)
  {
    $value = parent::getDisplayValue($action, $data, $index);

    if ($this->truncate) {
      $value = sy_truncate($value, $this->truncate);
    }

    return $value;
  }

}
