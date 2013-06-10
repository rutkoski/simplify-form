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
 * Base class for form elements composed of other elements
 *
 */
class Simplify_Form_Element_Base_Composite extends Simplify_Form_Element
{

  /**
   *
   * @var Simplify_Form_Element[]
   */
  protected $elements = array();

  /**
   *
   * @param Simplify_Form_Element $element
   * @return Simplify_Form_Element
   */
  public function addElement(Simplify_Form_Element $element)
  {
    $element->form = $this->form;

    $this->elements[] = $element;

    return $element;
  }

  /**
   *
   * @return Simplify_Form_Element[]
   */
  public function getElements()
  {
    return $this->elements;
  }

  /**
   *
   * @return Simplify_Form_Element
   */
  public function getElementByName($name)
  {
    if ($this->getName() == $name) {
      return $this;
    }

    foreach ($this->getElements() as $element) {
      $found = $element->getElementByName($name);

      if ($found) {
        return $found;
      }
    }

    return null;
  }

}
