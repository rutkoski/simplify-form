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
 * Base class for form filters
 *
 */
abstract class Simplify_Form_Filter extends Simplify_Form_Component
{

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::getValue()
   */
  public function getValue()
  {
    return s::request()->get($this->getName(), $this->getDefaultValue());
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onExecute()
   */
  public function onExecute(Simplify_Form_Action $action)
  {
    $this->form->url()->set($this->getName(), $this->getValue());
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onRender()
   */
  public function onRender(Simplify_Form_Action $action)
  {
    $this->set('label', $this->getLabel());
    $this->set('name', $this->getName());
    $this->set('value', $this->getValue());

    return parent::onRender($action);
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onPostData()
   */
  public function onPostData(Simplify_Form_Action $action, &$data, $post)
  {
    $data[$this->getName()] = $this->getValue();
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onCollectTableData()
   */
  public function onCollectTableData(&$row, $data)
  {
    $row[$this->getFieldName()] = $data[$this->getName()];
  }

}
