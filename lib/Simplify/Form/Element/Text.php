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
 * Text form element
 *
 */
class Text extends \Simplify\Form\Element
{

  /**
   *
   * @var int|boolean
   */
  public $minLength = false;

  /**
   *
   * @var int
   */
  public $maxLength = 255;

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::onValidate()
   */
  public function onValidate(\Simplify\Form\Action $action, $data)
  {
    if ($this->minLength !== false || $this->maxLength !== false) {
      if ($this->minLength === $this->maxLength) {
        $msg = _n('%1$s must have exactly %2$s character', '%1$s must have exactly %2$s characters', $this->minLength);
      }
      elseif ($this->minLength !== false) {
        if ($this->maxLength !== false) {
          $msg = __('%1$s must have between %2$s and %3$s characters');
        }
        else {
          $msg = __('%1$s must have %2$s or more characters');
        }
      }
      elseif ($this->maxLength !== false) {
        $msg = __('%1$s must have %3$s or less characters');
      }

      $msg = sprintf($msg, $this->getLabel(), $this->minLength, $this->maxLength);

      $rule = new \Simplify\Validation\Length($msg, $this->minLength, $this->maxLength);
      $rule->validate($this->getValue($data));
    }
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::onRender()
   */
  public function onRender(\Simplify\Form\Action $action, $data, $index)
  {
    $this->set('minLength', $this->minLength);
    $this->set('maxLength', $this->maxLength);

    return parent::onRender($action, $data, $index);
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Component::onCollectRequirements()
   */
  public function onCollectRequirements($schema)
  {
    $schema[$this->form->getTable()]['fields'][$this->getFieldName()] = array('type' => 'TEXT',
      'size' => $this->maxLength, 'null' => true);
  }

}
