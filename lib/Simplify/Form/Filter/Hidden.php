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

namespace Simplify\Form\Filter;

/**
 *
 * Form filter with hidden element
 *
 */
class Hidden extends \Simplify\Form\Filter
{

  /**
   *
   * @var boolean
   */
  public $visible = false;

  /**
   *
   * @var boolean
   */
  public $editable = false;

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Component::onInjectQueryParams()
   */
  public function onInjectQueryParams(\Simplify\Form\Action $action, &$params)
  {
    parent::onInjectQueryParams($action, $params);

    $value = $this->getValue();
    $name = $this->getFieldName();

    $params[\Simplify\Db\QueryParameters::WHERE][] = "{$name} = :{$name}";
    $params[\Simplify\Db\QueryParameters::DATA][$name] = $value;
  }

}
