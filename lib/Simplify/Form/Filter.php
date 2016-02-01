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

namespace Simplify\Form;

use Simplify\Db\QueryParameters;
/**
 *
 * Base class for form filters
 *
 */
abstract class Filter extends Component
{

  /**
   *
   * @var boolean
   */
  public $visible = true;

  /**
   *
   * @var boolean
   */
  public $editable = true;

  public $setOnLoad = true;
  
  public $setOnPost = false;
  
  /**
   * (non-PHPdoc)
   * @see Component::getValue()
   */
  public function getValue()
  {
    return $this->editable ? \Simplify::request()->get($this->getName(), $this->getDefaultValue()) : $this->getDefaultValue();
  }

  /**
   * (non-PHPdoc)
   * @see Renderable::getTemplateFilename()
   */
  public function getTemplateFilename()
  {
      if (!empty($this->style)) {
          return $this->style;
      }
      elseif (empty($this->template)) {
          $this->template = 'form_filter_' . strtolower(join('', array_slice(explode('\\', get_class($this)), -1)));
      }
  
      return parent::getTemplateFilename();
  }
  
  /**
   * (non-PHPdoc)
   * @see Component::onExecute()
   */
  public function onExecute(Action $action)
  {
    $this->form->url()->set($this->getName(), $this->getValue());
  }

  /**
   *
   * @param Action $action
   * @param array $filters
   */
  public function onRenderControls(Action $action, &$filters)
  {
    if ($this->visible) {
      $filter = array();
      
      $filter['name'] = $this->getName();
      $filter['label'] = $this->getLabel();
      $filter['controls'] = $this->onRender($action)->render();
      
      $filters[$this->getName()] = $filter;
    }
  }
  
  /**
   * (non-PHPdoc)
   * @see Component::onRender()
   */
  public function onRender(Action $action)
  {
    $this->set('label', $this->getLabel());
    $this->set('name', $this->getName());
    $this->set('value', $this->getValue());
    $this->set('editable', $this->editable);

    return parent::onRender($action);
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Component::onLoadData()
   */
  public function onLoadData(Action $action, &$data, $row)
  {
    if ($this->setOnLoad) {
      $data[$this->getName()] = $this->getValue();
    }
  }

  /**
   * On inject query params callback.
   *
   * @param Action $action current action
   * @param array $params query parameters
   */
  public function onInjectQueryParams(Action $action, &$params)
  {
    if ($this->setOnLoad) {
      $params[QueryParameters::SELECT][] = $this->getFieldName();
    }
  }
  
  /**
   * (non-PHPdoc)
   * @see Component::onPostData()
   */
  public function onPostData(Action $action, &$data, $post)
  {
    if ($this->setOnPost) {
      $data[$this->getName()] = $this->getValue();
    }
  }

  /**
   * (non-PHPdoc)
   * @see Component::onCollectTableData()
   */
  public function onCollectTableData(Action $action, &$row, $data)
  {
    $row[$this->getFieldName()] = sy_get_param($data, $this->getName(), $this->getDefaultValue());
  }

}
