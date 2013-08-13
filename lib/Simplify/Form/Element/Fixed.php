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
 * Hidden form element
 *
 */
class Simplify_Form_Element_Fixed extends Simplify_Form_Element
{

  /**
   *
   * @var mixed
   */
  public $value;

  /**
   *
   * @return void
   */
  public function __construct($name, $label = false)
  {
    parent::__construct($name, $label);
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onInjectQueryParams()
   */
  public function onInjectQueryParams(Simplify_Form_Action $action, &$params)
  {
    $params[Simplify_Db_QueryParameters::SELECT][] = $this->getFieldName();
    $params[Simplify_Db_QueryParameters::WHERE][] = Simplify_Db_QueryObject::buildIn($this->getFieldName(), $this->value);
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::getValue()
   */
  public function getValue($data)
  {
    return $this->value;
  }

}
