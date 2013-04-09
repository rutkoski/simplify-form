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
 * Form date and time element
 *
 */
class Simplify_Form_Element_Datetime extends Simplify_Form_Element
{

  /**
   *
   * @var string
   */
  public $displayFormat = 'd/m/Y H:i:s';

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onRender()
   */
  public function onRender(Simplify_Form_Action $action, $data, $index)
  {
    $this->set('formatedValue', $this->getDisplayValue($action, $data, $index));

    return parent::onRender($action, $data, $index);
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onPostData()
   */
  public function onPostData(Simplify_Form_Action $action, &$data, $post)
  {
    $date = $post[$this->getName()];

    $dt = date_parse_from_format($this->displayFormat, $date);
    $dt = mktime($dt['hour'], $dt['minute'], $dt['second'], $dt['month'], $dt['day'], $dt['year']);

    $value = date('Y-m-d H:i:s', $dt);

    $data[$this->getName()] = $value;
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element::getDisplayValue()
   */
  public function getDisplayValue(Simplify_Form_Action $action, $data, $index)
  {
    return date($this->displayFormat, strtotime($this->getValue($data)));
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::getDefaultValue()
   */
  public function getDefaultValue()
  {
    return date('Y-m-d H:i:s');
  }

}
