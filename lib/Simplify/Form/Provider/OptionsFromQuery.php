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

namespace Simplify\Form\Provider;

/**
 *
 * Base class for list providers
 *
 */
class OptionsFromQuery extends \Simplify\Form\Provider
{

  /**
   *
   * @var \Simplify\Db\QueryObject
   */
  protected $query;

  protected $value;

  protected $label;

  public function __construct($query, $value, $label)
  {
    $this->query = $query;
    $this->value = $value;
    $this->label = $label;
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Provider::getData()
   */
  public function getData()
  {
    if ($this->data === false) {
      if (is_array($this->query)) {
        $this->query = \Simplify::db()->query()->select($this->value)->select($this->label)->setParams($this->query);
      }

      $data = $this->query->execute()->fetchAll();
      $this->data = sy_array_to_options($data, $this->value, $this->label);
    }

    return $this->data;
  }

}
