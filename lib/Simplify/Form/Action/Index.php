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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Rodrigo Rutkoski Rodrigues <rutkoski@gmail.com>
 */
namespace Simplify\Form\Action;

use Simplify;
use Simplify\Db\QueryParameters;
use Simplify\Form;
use Simplify\Form\Action;
use Simplify\Menu;
use Simplify\MenuItem;

/**
 * Form action list
 */
class Index extends Action
{

  /**
   * Number of items per page
   *
   * @var int
   */
  public $limit = 20;

  /**
   * Current page offset
   *
   * @var int
   */
  public $offset = null;

  /**
   *
   * @var mixed
   */
  public $orderBy = array();

  /**
   *
   * @var int
   */
  protected $actionMask = Form::ACTION_LIST;

  /**
   *
   * @var Simplify\Pager
   */
  protected $pager;

  /**
   *
   * @return int
   */
  protected function getLimit()
  {
    return $this->limit;
  }

  /**
   *
   * @return int
   */
  protected function getOffset()
  {
    return Simplify::request()->get('offset', $this->offset);
  }

  /**
   *
   * @return array
   */
  public function getOrderBy()
  {
    return $this->orderBy;
  }

  /**
   */
  public function toggleOrderBy($field, $dir = null)
  {
    $i = 0;
    
    if (empty($this->orderBy)) {
      $this->orderBy[] = array(
          $field,
          $dir
      );
    }
    else {
      while ($i < count($this->orderBy) && $this->orderBy[$i][0] != $field) {
        $i ++;
      }
    }
    
    $dir = is_string($dir) ? strtolower($dir) : $dir;
    
    if ($i < count($this->orderBy) && $this->orderBy[$i][0] == $field) {
      if ($dir == 'asc' || $dir == 'desc') {
        $this->orderBy[$i][1] = $dir;
      }
      elseif ($this->orderBy[$i][1] == 'asc') {
        $this->orderBy[$i][1] = 'desc';
      }
      elseif ($this->orderBy[$i][1] == 'desc' || $dir === false) {
        array_splice($this->orderBy, $i, 1);
      }
      else {
        $this->orderBy[$i][1] = 'asc';
      }
    }
    else {
      if (! $dir) {
        $dir = 'asc';
      }
      
      $this->orderBy[] = array(
          $field,
          $dir
      );
    }
  }

  /**
   * (non-PHPdoc)
   * 
   * @see Simplify\Form\Action::onExecute()
   */
  public function onExecute()
  {
    parent::onExecute();
    
    $this->onLoadData();
  }

  /**
   * (non-PHPdoc)
   * 
   * @see Simplify\Form\Action::onRender()
   */
  public function onRender()
  {
    $elements = $this->getElements();
    
    $headers = array();
    foreach ($elements as $element) {
      $element->onRenderHeaders($this, $headers);
    }
    
    $data = array();
    foreach ($this->formData as $index => $row) {
      $line = new \ArrayObject();
      $line[Form::ID] = $row[Form::ID];
      $line['name'] = Form::ID . "[]";
      $line['menu'] = new Menu('actions');
      $line['menu']->addItem(new Menu('main'));
      //$line['state'] = Form::STATE_WARNING;
      $line['elements'] = array();
      
      $elements->rewind();
      while ($elements->valid()) {
        $element = $elements->current();
        $elements->next();
        
        $element->onRenderLine($this, $line, $row, $index);
      }
      
      $this->form->onCreateItemMenu($line['menu'], $this, $row);
      
      $this->form->dispatch('onRenderListRow', $this, $line, $row);
      
      $data[] = $line;
    }
    
    $bulk = array();
    
    $this->form->onCreateBulkOptions($bulk);
    
    $this->set('headers', $headers);
    $this->set('data', $data);
    $this->set('pager', $this->pager);
    $this->set('bulk', $bulk);
    
    return parent::onRender();
  }

  /**
   * (non-PHPdoc)
   * 
   * @see Simplify\Form\Action::onCreateMenu()
   */
  public function onCreateMenu(Menu $menu)
  {
    $item = new MenuItem($this->getName(), $this->getTitle(), Form::ICON_LIST, $this->url());
    
    $menu->getItemByName('main')->addItem($item);
  }

  /**
   * (non-PHPdoc)
   * 
   * @see Simplify\Form\Action::onLoadData()
   */
  protected function onLoadData()
  {
    $elements = $this->getElements();
    
    $pk = $this->form->getPrimaryKey();
    
    $params = array();
    $params[QueryParameters::SELECT][] = $this->form->getPrimaryKey();
    $params[QueryParameters::LIMIT] = $this->getLimit();
    $params[QueryParameters::OFFSET] = $this->getOffset();
    $params[QueryParameters::ORDER_BY] = $this->getOrderBy();
    
//     beying done twice...
//     while ($elements->valid()) {
//       $element = $elements->current();
//       $element->onInjectQueryParams($this, $params);
      
//       $elements->next();
//     }
    
//     foreach ($this->form->getFilters() as $filter) {
//       $filter->onInjectQueryParams($this, $params);
//     }
    
    $this->onInjectQueryParams($params);

    $data = $this->repository()->findAll($params);
      
    $this->formData = array();
    
    foreach ($data as $index => $row) {
      $this->formData[$index] = array();
      $this->formData[$index][Form::ID] = $row[$pk];
      $this->formData[$index][$pk] = $row[$pk];

      foreach ($this->form->getFilters() as $filter) {
          $filter->onLoadData($this, $this->formData[$index], $row);
      }
      
      $elements->rewind();
      
      while ($elements->valid()) {
        $element = $elements->current();
        $element->onLoadData($this, $this->formData[$index], $row);
        
        $elements->next();
      }
    }
    
    if ($this->getLimit()) {
        $this->pager = $this->repository()->findPager($params);
    }
  }

}
