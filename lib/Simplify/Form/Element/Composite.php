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
 * Composite
 *
 */
class Simplify_Form_Element_Composite extends Simplify_Form_Element_Base_Composite
{

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element::onRenderHeaders()
   */
  public function onRenderHeaders(Simplify_Form_Action $action, &$headers)
  {
    if ($action->show(Simplify_Form::ACTION_LIST)) {
      foreach ($this->getElements() as $element) {
        $element->onRenderHeaders($action, $headers);
      }
    } else {
      $headers[$this->getName()] = $this->getLabel();
    }
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element::onRenderLine()
   */
  public function onRenderLine(Simplify_Form_Action $action, &$line, $data, $index)
  {
    foreach ($this->getElements() as $element) {
      $element->onRenderLine($action, $line, $data, $index);
    }
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element::onRenderControls()
   */
  public function onRenderControls(Simplify_Form_Action $action, &$line, $data, $index)
  {
    parent::onRenderControls($action, $line, $data, $index);
    $line['elements'][$this->getName()]['label'] = false;
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onRender()
   */
  public function onRender(Simplify_Form_Action $action, $data, $index)
  {
    $headers = array();
    foreach ($this->getElements() as $element) {
      $element->onRenderHeaders($action, $headers);
    }

    $line = array();
    foreach ($this->getElements() as $element) {
      $element->onRenderControls($action, $line, $data, $index);
    }

    $this->set('headers', $headers);
    $this->set('elements', $line['elements']);

    $names = array_keys($line['elements']);
    $this->set('active', array_shift($names));

    return parent::onRender($action, $data, $index);
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element::onLoadData()
   */
  public function onLoadData(Simplify_Form_Action $action, &$data, $row)
  {
    foreach ($this->getElements() as $element) {
      $element->onLoadData($action, $data, $row);
    }
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element::onPostData()
   */
  public function onPostData(Simplify_Form_Action $action, &$data, $post)
  {
    foreach ($this->getElements() as $element) {
      $element->onPostData($action, $data, $post);
    }
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element::onCollectTableData()
   */
  public function onCollectTableData(&$row, $data)
  {
    foreach ($this->getElements() as $element) {
      $element->onCollectTableData($row, $data);
    }
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element::onInjectQueryParams()
   */
  public function onInjectQueryParams(Simplify_Form_Action $action, &$params)
  {
    foreach ($this->getElements() as $element) {
      $element->onInjectQueryParams($action, $params);
    }
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onSave()
   */
  public function onSave(Simplify_Form_Action $action, &$data)
  {
    foreach ($this->getElements() as $element) {
      $element->onSave($action, $data);
    }
  }

}
