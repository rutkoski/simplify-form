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

use Simplify\Form\Action;
/**
 *
 * Hidden form element
 *
 */
class Hidden extends \Simplify\Form\Element
{

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Component::getLabel()
   */
  public function getLabel()
  {
    return false;
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::onRenderLine()
   */
  public function onRenderLine(Action $action, &$line, $data, $index)
  {
      //
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::onRenderHeaders()
   */
  public function onRenderHeaders(Action $action, &$headers)
  {
      //
  }
  
}
