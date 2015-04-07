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

namespace Simplify\Form\Element;

/**
 *
 * Textarea form element
 *
 */
class Textarea extends \Simplify\Form\Element\Text
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
   * @see \Simplify\Form\Element::getDisplayValue()
   */
  public function getDisplayValue(\Simplify\Form\Action $action, $data, $index)
  {
    $value = parent::getDisplayValue($action, $data, $index);

    if ($this->truncate) {
      $value = sy_truncate($value, $this->truncate);
    }

    return $value;
  }

}
