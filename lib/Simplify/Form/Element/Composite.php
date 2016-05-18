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
 * Composite
 *
 */
class Composite extends \Simplify\Form\Element\Base\Composite
{

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::onRenderHeaders()
   */
  public function onRenderHeaders(\Simplify\Form\Action $action, &$headers)
  {
    if ($action->show(\Simplify\Form::ACTION_LIST)) {
      $elements = $this->getElements($action);
      while ($elements->valid()) {
        $element = $elements->current();
        $element->onRenderHeaders($action, $headers);
        $elements->next();
      }
    }
    else {
      $headers[$this->getName()] = $this->getLabel();
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
          $element->onRenderLine($action, $line, $data, $index);
          $elements->next();
      }
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::onRenderControls()
   */
  public function __onRenderControls(\Simplify\Form\Action $action, &$line, $data, $index)
  {
    parent::onRenderControls($action, $line, $data, $index);
    $line['elements'][$this->getName()]['label'] = false;
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::onRender()
   */
  public function onRender(\Simplify\Form\Action $action, $data, $index)
  {
    $headers = array();

    $elements = $this->getElements($action);

    while ($elements->valid()) {
      $element = $elements->current();
      $elements->next();

      $element->onRenderHeaders($action, $headers);
    }

    $line = array();
    $line['elements'] = array();

    $elements->rewind();
    while ($elements->valid()) {
      $element = $elements->current();
      $elements->next();

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
   * @see \Simplify\Form\Element::onLoadData()
   */
  public function onLoadData(\Simplify\Form\Action $action, &$data, $row)
  {
    $elements = $this->getElements($action);
    while ($elements->valid()) {
      $element = $elements->current();
      $elements->next();

      $element->onLoadData($action, $data, $row);
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
    $elements = $this->getElements($action);
    while ($elements->valid()) {
      $element = $elements->current();
      $elements->next();

      $element->onCollectTableData($action, $row, $data);
    }
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::onInjectQueryParams()
   */
  public function onInjectQueryParams(\Simplify\Form\Action $action, &$params)
  {
    $elements = $this->getElements($action);
    while ($elements->valid()) {
      $element = $elements->current();
      $elements->next();

      $element->onInjectQueryParams($action, $params);
    }
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Component::onSave()
   */
  public function onSave(\Simplify\Form\Action $action, &$data)
  {
    $elements = $this->getElements($action);
    while ($elements->valid()) {
      $element = $elements->current();
      $elements->next();

      $element->onSave($action, $data);
    }
  }

}
