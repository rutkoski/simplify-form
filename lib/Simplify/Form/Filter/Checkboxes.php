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
 * Form filter with select element
 *
 */
class Simplify_Form_Filter_Checkboxes extends Simplify_Form_Filter
{

  /**
   * Selection options
   *
   * @var mixed
   */
  public $options = false;

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Filter::onRender()
   */
  public function onRender(Simplify_Form_Action $action)
  {
    $this->set('options', $this->getOptions());

    return parent::onRender($action);
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::getValue()
   */
  public function getValue()
  {
    return (array) parent::getValue();
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onInjectQueryParams()
   */
  public function onInjectQueryParams(Simplify_Form_Action $action, &$params)
  {
    parent::onInjectQueryParams($action, $params);

    $value = $this->getValue();

    if (! empty($value)) {
      $name = $this->getFieldName();

      $params[Simplify_Db_QueryParameters::WHERE][] = Simplify_Db_QueryObject::buildIn($name, $value);
    }
  }

  /**
   *
   * @return mixed[string]
   */
  public function getOptions()
  {
    if ($this->options === false) {
      $options = s::db()
        ->query()
        ->from($this->form->getTable())
        ->select($this->getFieldName())
        ->orderBy($this->getFieldName())
        ->execute()
        ->fetchCol();

      $options = array_combine($options, $options);
    } else {
      $options = (array) $this->options;
    }

    return $options;
  }

}
