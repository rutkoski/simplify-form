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

namespace Simplify\Form\Element\Base;

use Simplify\Form;

use Simplify\Form\Action;

use Simplify\Menu;

use Simplify\MenuItem;

/**
 *
 * Base class for form elements that handle one to many associations
 *
 */
class HasMany extends \Simplify\Form\Element\Base\Composite
{

  const CASCADE = 'cascade';

  const SETNULL = 'setnull';

  const SERVICE_ACTION_SORT = 'sort';

  /**
   * On delete behavior
   *
   * @var string
   */
  public $deletePolicy = self::CASCADE;

  /**
   *
   * @var int|int[]
   */
  public $id;

  /**
   *
   * @var string
   */
  public $title;

  /**
   *
   * @var string
   */
  public $table;

  /**
   *
   * @var string
   */
  public $primaryKey;

  /**
   *
   * @var string
   */
  public $foreignKeyColumn;

  /**
   *
   * @var string
   */
  public $referenceColumn;

  /**
   *
   * @var string
   */
  public $style;

  /**
   *
   * @var boolean|string
   */
  protected $sortable;

  /**
   *
   * @var \Simplify\Form\Repository
   */
  protected $repository;

  /**
   *
   * @var int
   */
  protected $remove = \Simplify\Form::ACTION_LIST;

  /**
   * Set the sort field name or false for no sorting
   *
   * @param boolean|string $sortable
   */
  public function setSortable($sortable)
  {
    $this->sortable = $sortable;
  }

  /**
   *
   * @return Ambigous <boolean, string>
   */
  public function getSortable()
  {
    return $this->sortable;
  }

  /**
   *
   * @param Menu $menu
   */
  public function onCreateMenu(Menu $menu)
  {
    $menu->getItemByName('main')->addItem(new MenuItem('create', __('Create'), 'plus'));
  }
  
  /**
   *
   * @param Menu $menu
   */
  public function onCreateItemMenu(Menu $menu, $item)
  {
    $menu->getItemByName('main')->addItem(new MenuItem('delete', __('Delete'), 'minus'));
  }
  
  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::onRender()
   */
  public function onRender(\Simplify\Form\Action $action, $data, $index)
  {
    $elements = $this->getElements($action);

    $pk = $this->getPrimaryKey();

    $lines = array();

    if (isset($data[$this->getName()])) {
      foreach ($data[$this->getName()] as $_index => $_row) {
        $__index = array_merge((array) $index, array($this->getName(), $_index));

        $line = array();
        $line['id'] = "formData_" . implode('_', $__index);
        $line['name'] = "formData[" . implode('][', $__index) . "][" . \Simplify\Form::ID . "]";
        $line['baseName'] = "formData[" . implode('][', $__index) . "]";
        $line[\Simplify\Form::ID] = $_row[\Simplify\Form::ID];
        $line['elements'] = array();
        $line['menu'] = new Menu('actions');
        $line['menu']->addItem(new Menu('main'));
        
        $this->onCreateItemMenu($line['menu'], $line);

        $elements->rewind();
        while ($elements->valid()) {
          $element = $elements->current();
          $line['elements'][] = array('label' => $element->getLabel(),
            'controls' => $element->onRender($action, $_row, array($index, $this->getName(), $_index))->render());
          $elements->next();
        }

        $this->onRenderRow($line, $data[$this->getName()][$_index], array($index, $this->getName(), $_index));

        $lines[] = $line;
      }
    }

    $__index = array_merge((array) $index, array($this->getName(), 'dummy'));

    $dummy = array();
    $dummy['baseName'] = "formData[" . implode('][', $__index) . "]";
    $dummy['name'] = "formData[" . implode('][', $__index) . "][" . \Simplify\Form::ID . "]";
    $dummy[\Simplify\Form::ID] = '';
    $dummy['elements'] = array();
    $dummy['menu'] = new Menu('actions');
    $dummy['menu']->addItem(new Menu('main'));
    
    $this->onCreateItemMenu($dummy['menu'], $dummy);
    
    $elements->rewind();
    while ($elements->valid()) {
      $element = $elements->current();
      $dummy['elements'][] = array('label' => $element->getLabel(),
        'controls' => $element->onRender($action, $dummy, array($index, $this->getName(), 'dummy'))->render());
      $elements->next();
    }

    $this->set('data', $lines);
    $this->set('dummy', $dummy);
    
    $menu = new Menu('actions');
    $menu->addItem(new Menu('main'));
    
    $this->onCreateMenu($menu);
    
    $this->set('menu', $menu);

    $this->set('sortable', $this->sortable);

    $this->set('sortableServiceUrl', $action->form->url()->set('formAction', 'services')->set('serviceName', $this->getName())->set('serviceAction', self::SERVICE_ACTION_SORT));

    return parent::onRender($action, $data, $index);
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::onRenderLine()
   */
  public function onRenderLine(Action $action, &$line, $data, $index)
  {
    if ($action->show(Form::ACTION_VIEW)) {
      $_element = array();
      
      $_element['id'] = $this->getElementId($index);
      $_element['name'] = $this->getInputName($index);
      $_element['class'] = $this->getElementClass();
      $_element['label'] = $this->getLabel();
      
      $elements = $this->getElements($action);
      
      $rows = (array) $this->getValue($data);
  
      $_lines = array();
      
      foreach ($rows as $row) {
        $_line = array();
        
        $elements->rewind();
        while ($elements->valid()) {
          $element = $elements->current();
          $element->onRenderLine($action, $_line, $row, $index);
          $elements->next();
        }
        
        $_lines[] = $_line;
      }

      $this->set('action', $action);
      $this->set('data', $_lines);
      $this->set('label', $this->getLabel());

      $_element['controls'] = $this->getView();
  
      $line['elements'][$this->getName() . '_b'] = $_element;
    }
  }
  
  /**
   * 
   * @param unknown_type $row
   * @param unknown_type $data
   * @param unknown_type $index
   */
  public function onRenderRow(&$row, $data, $index)
  {
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::onLoadData()
   */
  public function onLoadData(\Simplify\Form\Action $action, &$data, $row)
  {
    $elements = $this->getElements($action);

    $pk = $this->getPrimaryKey();
    $fk = $this->getForeignKeyColumn();

    $id = $data[$this->getReferenceColumn()];

    $params = array();
    $params[\Simplify\Db\QueryParameters::SELECT][] = $pk;
    $params[\Simplify\Db\QueryParameters::SELECT][] = $fk;
    $params[\Simplify\Db\QueryParameters::WHERE][] = \Simplify\Db\QueryObject::buildIn($fk, $id);
    $params[\Simplify\Db\QueryParameters::DATA][$fk] = $id;

    if ($this->sortable) {
      $params[\Simplify\Db\QueryParameters::SELECT][] = $this->sortable;
      $params[\Simplify\Db\QueryParameters::ORDER_BY][] = $this->sortable;
    }

    while ($elements->valid()) {
      $elements->current()->onInjectQueryParams($action, $params);
      $elements->next();
    }

    $this->onBeforeLoadData($params);

    $row[$this->getName()] = $this->repository()->findAll($params);

    foreach ($row[$this->getName()] as $_index => $_row) {
      $data[$this->getName()][$_index] = array();
      $data[$this->getName()][$_index][\Simplify\Form::ID] = $_row[$pk];

      $elements->rewind();
      while ($elements->valid()) {
        $elements->current()->onLoadData($action, $data[$this->getName()][$_index], $_row);
        $elements->next();
      }

      $this->onAfterLoadData($data[$this->getName()][$_index], $_row, $_index);
    }
  }

  /**
   *
   * @param unknown_type $queryParams
   */
  protected function onBeforeLoadData(&$queryParams)
  {
  }

  /**
   *
   * @param unknown_type $data
   * @param unknown_type $row
   * @param unknown_type $index
   */
  protected function onAfterLoadData(&$data, $row, $index)
  {
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::onPostData()
   */
  public function onPostData(\Simplify\Form\Action $action, &$data, $post)
  {
    $id = $data[$this->getReferenceColumn()];

    $data[$this->getName()] = array();

    if (!empty($post[$this->getName()])) {
      $position = 0;

      foreach ($post[$this->getName()] as $index => $row) {
        if ($index !== 'dummy') {
          $data[$this->getName()][$index][\Simplify\Form::ID] = sy_get_param($row, \Simplify\Form::ID);
          $data[$this->getName()][$index][$this->getForeignKeyColumn()] = $id;

          if ($this->sortable) {
            $data[$this->getName()][$index][$this->sortable] = $position++;
          }

          $elements = $this->getElements($action);
          while ($elements->valid()) {
            $elements->current()->onPostData($action, $data[$this->getName()][$index], $row);
            $elements->next();
          }
        }
      }
    }
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Component::onSave()
   */
  public function onSave(\Simplify\Form\Action $action, &$data)
  {
    $id = array();
    
    $elements = $this->getElements($action);

    foreach ($data[$this->getName()] as &$row) {
      $_row = array();

      if (strpos($row[\Simplify\Form::ID], 'new-') !== 0) {
        $_row[$this->getPrimaryKey()] = $row[\Simplify\Form::ID];
      }

      $_row[$this->getForeignKeyColumn()] = $data[$this->getReferenceColumn()];

      if ($this->sortable) {
        $_row[$this->sortable] = $row[$this->sortable];
      }

      $elements->rewind();
      while ($elements->valid()) {
        $element = $elements->current();
        $element->onSave($action, $row);
        $element->onCollectTableData($action, $_row, $row);
        $elements->next();
      }

      $this->onBeforeSave($action, $_row, $row);

      $this->repository()->save($_row);

      $id[] = $row[\Simplify\Form::ID] = $_row[$this->getPrimaryKey()];
    }

    $params = array();
    $params[\Simplify\Db\QueryParameters::WHERE][] = \Simplify\Db\QueryObject::buildIn($this->getForeignKeyColumn(), $data[$this->getReferenceColumn()]);
    $params[\Simplify\Db\QueryParameters::WHERE][] = \Simplify\Db\QueryObject::buildIn($this->getPrimaryKey(), $id, true);

    $deleted = $this->repository()->findAll($params);

    $this->onAfterSave($action, $data, $deleted);

    $this->repository()->deleteAll($params);
  }

  /**
   * 
   * @param \Simplify\Form\Action $action
   * @param unknown_type $row
   * @param unknown_type $data
   */
  public function onBeforeSave(\Simplify\Form\Action $action, &$row, $data)
  {
  }

  /**
   * 
   * @param \Simplify\Form\Action $action
   * @param unknown_type $data
   * @param unknown_type $deleted
   */
  protected function onAfterSave(\Simplify\Form\Action $action, &$data, $deleted)
  {
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Component::onBeforeDelete()
   */
  public function onBeforeDelete(\Simplify\Form\Action $action, &$data)
  {
    if (empty($data[$this->getName()])) return;

    $elements = $this->getElements($action);

    foreach ($data[$this->getName()] as $index => $row) {
      $elements->rewind();
      while ($elements->valid()) {
        $elements->current()->onBeforeDelete($action, $row);
        $elements->next();
      }
    }
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Component::onAfterDelete()
   */
  public function onAfterDelete(\Simplify\Form\Action $action, &$data)
  {
    if (empty($data[$this->getName()])) return;

    $pk = $this->getPrimaryKey();
    $fk = $this->getForeignKeyColumn();

    $id = $data[$this->getReferenceColumn()];

    $elements = $this->getElements($action);

    if ($this->deletePolicy == self::CASCADE) {
      $params = array();
      $params[\Simplify\Db\QueryParameters::WHERE][] = \Simplify\Db\QueryObject::buildIn($fk, $id);

      $this->repository()->deleteAll($params);

      foreach ($data[$this->getName()] as $index => $row) {
        $elements->rewind();
        while ($elements->valid()) {
          $elements->current()->onAfterDelete($action, $row);
          $elements->next();
        }
      }
    } elseif ($this->deletePolicy == self::SETNULL) {
      foreach ($data[$this->getName()] as $index => $row) {
        $_row = array($pk => $row[\Simplify\Form::ID], $fk => null);

        $this->repository()->save($_row);
      }
    }
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::onInjectQueryParams()
   */
  public function onInjectQueryParams(&$params)
  {
    // nothing to see here! move along!
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::onCollectTableData()
   */
  public function onCollectTableData(\Simplify\Form\Action $action, &$row, $data)
  {
    // nothing to see here! move along!
  }

  /**
   *
   * @return string
   */
  public function getTable()
  {
    if (empty($this->table)) {
      $this->table = \Simplify\Inflector::tableize($this->getName());
    }

    return $this->table;
  }

  /**
   *
   * @return string
   */
  public function getPrimaryKey()
  {
    if (empty($this->primaryKey)) {
      $this->primaryKey = \Simplify\Inflector::singularize($this->getTable()) . '_id';
    }

    return $this->primaryKey;
  }

  /**
   *
   * @return string
   */
  public function getForeignKeyColumn()
  {
    if (empty($this->foreignKeyColumn)) {
      $this->foreignKeyColumn = \Simplify\Inflector::singularize($this->getTable()) . '_' . $this->getReferenceColumn();
    }

    return $this->foreignKeyColumn;
  }

  /**
   *
   * @return string
   */
  public function getReferenceColumn()
  {
    if (empty($this->referenceColumn)) {
      $this->referenceColumn = \Simplify\Form::ID;
    }

    return $this->referenceColumn;
  }

  /**
   *
   * @param string $table
   * @param string $primaryKey
   * @param string $foreignKey
   */
  public function setTable($table, $primaryKey = null, $foreignKeyColumn = null, $referenceColumn = null)
  {
    $this->table = $table;
    $this->primaryKey = $primaryKey;
    $this->foreignKeyColumn = $foreignKeyColumn;
    $this->referenceColumn = $referenceColumn;
  }

  /**
   *
   * @return \Simplify\Form\Repository
   */
  public function repository()
  {
    if (empty($this->repository)) {
      $this->repository = new \Simplify\Form\Repository($this->getTable(), $this->getPrimaryKey());
    }

    return $this->repository;
  }

}
