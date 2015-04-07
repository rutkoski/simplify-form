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

namespace Simplify\Form\Element\Base;

/**
 *
 * Base class for form elements composed of other elements
 *
 */
class Composite extends \Simplify\Form\Element
{

  /**
   *
   * @var \Simplify\Form\Element[]
   */
  protected $elements = array();

  /**
   *
   * @param \Simplify\Form\Element $element
   * @return \Simplify\Form\Element
   */
  public function addElement(\Simplify\Form\Element $element, $actionMask = \Simplify\Form::ACTION_ALL, $index = null)
  {
    $element->form = $this->form;
    $element->actionMask = $actionMask;

    if (is_null($index)) {
      $this->elements[] = $element;
    }
    else {
      array_splice($this->elements, $index, 0, array($element));
    }

    return $element;
  }

  /**
   *
   * @return \Simplify\Form\ElementIterator
   */
  public function getElements(\Simplify\Form\Action $action)
  {
    return new \Simplify\Form\ElementIterator($this->elements, $action->getActionMask());
  }

  /**
   *
   * @return \Simplify\Form\Element
   */
  public function getElementByName($name)
  {
    if ($this->getName() == $name) {
      return $this;
    }

    foreach ($this->elements as $element) {
      $found = $element->getElementByName($name);

      if ($found) {
        return $found;
      }
    }

    return null;
  }

}
