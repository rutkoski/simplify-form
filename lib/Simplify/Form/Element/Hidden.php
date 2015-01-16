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
 * Hidden form element
 *
 */
class Hidden extends \Simplify\Form\Element
{

  /**
   *
   * @return void
   */
  public function __construct($name, $label = null)
  {
    parent::__construct($name, $label);

    $this->remove = \Simplify\Form::ACTION_VIEW ^ \Simplify\Form::ACTION_LIST;
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Component::getLabel()
   */
  public function getLabel()
  {
    return false;
  }

}
