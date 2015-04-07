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
 * Serializer
 *
 */
class Serializer extends \Simplify\Form\Element\Base\Composite
{

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::onRenderHeaders()
   */
  public function onRenderHeaders(\Simplify\Form\Action $action, &$headers)
  {
    $elements = $this->getElements($action);
    while ($elements->valid()) {
      $element = $elements->current();
      $elements->next();

      $element->onRenderHeaders($action, $headers);
    }
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::onRenderLine()
   */
  public function onRenderLine(\Simplify\Form\Action $action, &$line, $data, $index)
  {
    $elements = $this->getElements($action);
    while ($elements->valid()) {
      $element = $elements->current();
      $elements->next();

      $element->onRenderLine($action, $line, $data, $index);
    }
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::onRenderControls()
   */
  public function onRenderControls(\Simplify\Form\Action $action, &$line, $data, $index)
  {
    $elements = $this->getElements($action);
    while ($elements->valid()) {
      $element = $elements->current();
      $elements->next();

      $element->onRenderControls($action, $line, $data, $index);
    }
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::onLoadData()
   */
  public function onLoadData(\Simplify\Form\Action $action, &$data, $row)
  {
    if (isset($row[$this->getFieldName()])) {
      $_row = unserialize($row[$this->getFieldName()]);

      $elements = $this->getElements($action);
      while ($elements->valid()) {
        $element = $elements->current();
        $elements->next();

        $element->onLoadData($action, $data, $_row);
      }
    }
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::onPostData()
   */
  public function onPostData(\Simplify\Form\Action $action, &$data, $post)
  {
    $elements = $this->getElements($action);
    while ($elements->valid()) {
      $element = $elements->current();
      $elements->next();

      $element->onPostData($action, $data, $post);
    }
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::onCollectTableData()
   */
  public function onCollectTableData(\Simplify\Form\Action $action, &$row, $data)
  {
    $_row = array();

    $elements = $this->getElements($action);
    while ($elements->valid()) {
      $element = $elements->current();
      $elements->next();

      $element->onCollectTableData($action, $_row, $data);
    }

    $row[$this->getFieldName()] = serialize($_row);
  }

}
