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
 * Serializer
 *
 */
class Simplify_Form_Element_Serializer extends Simplify_Form_Element_Base_Composite
{

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element::onRenderHeaders()
   */
  public function onRenderHeaders(Simplify_Form_Action $action, &$headers)
  {
    foreach ($this->getElements() as $element) {
      $element->onRenderHeaders($action, $headers);
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
    foreach ($this->getElements() as $element) {
      $element->onRenderControls($action, $line, $data, $index);
    }
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element::onLoadData()
   */
  public function onLoadData(Simplify_Form_Action $action, &$data, $row)
  {
    if (isset($row[$this->getFieldName()])) {
      $_row = unserialize($row[$this->getFieldName()]);

      foreach ($this->getElements() as $element) {
        $element->onLoadData($action, $data, $_row);
      }
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
    $_row = array();
    foreach ($this->getElements() as $element) {
      $element->onCollectTableData($_row, $data);
    }
    $row[$this->getFieldName()] = serialize($_row);
  }

}
