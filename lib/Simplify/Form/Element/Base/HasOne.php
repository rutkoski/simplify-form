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

use Simplify\Form\Action;
use Simplify\Form;
/**
 *
 * Base class for form elements that handle one to many associations
 *
 */
class HasOne extends \Simplify\Form\Element\Base\Composite
{

  const CASCADE = 'cascade';

  const SETNULL = 'setnull';

  /**
   * On delete behavior
   *
   * @var string
   */
  public $deletePolicy = self::CASCADE;

  /**
   *
   * @var mixed
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
   * @var \Simplify\Form\Repository
   */
  protected $repository;

  /**
   *
   * @var int
   */
  protected $remove = \Simplify\Form::ACTION_LIST;

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Element::onRender()
   */
  public function onRender(\Simplify\Form\Action $action, $data, $index)
  {
    $elements = $this->getElements($action);

    $pk = $this->getPrimaryKey();

    $line = array();

    $__index = array_merge((array) $index, (array)$this->getName());

    $row = sy_get_param($data, $this->getName(), array());

    $line['id'] = "formData_" . implode('_', $__index);
    $line['name'] = "formData[" . implode('][', $__index) . "][" . \Simplify\Form::ID . "]";
    $line['baseName'] = "formData[" . implode('][', $__index) . "]";
    $line[\Simplify\Form::ID] = sy_get_param($row, \Simplify\Form::ID);
    $line['elements'] = array();

    while ($elements->valid()) {
      $element = $elements->current();
      
      if ($action->show(Form::ACTION_VIEW)) {
        $element->onRenderLine($action, $line, $row, $__index);
      } else {
        $line['elements'][] = array('label' => $element->getLabel(),
          'controls' => $element->onRender($action, $row, $__index)->render());
      }
      
      $elements->next();
    }

    $this->onRenderRow($line, $row, array($index, $this->getName()));

    $this->set('data', $line);

    return parent::onRender($action, $data, $index);
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

  public function onRenderLine(Action $action, &$line, $data, $index)
  {
      $element['id'] = $this->getElementId($index);
      $element['name'] = $this->getInputName($index);
      $element['class'] = $this->getElementClass();
      $element['label'] = $this->getLabel();
      $element['controls'] = $this->onRender($action, $data, $index);
      
      $line['elements'][$this->getName()] = $element;
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
    //$params[\Simplify\Db\QueryParameters::DATA][$fk] = $id;

    if (!empty($this->id)) {
      $params[\Simplify\Db\QueryParameters::WHERE][] = \Simplify\Db\QueryObject::buildIn($pk, $this->id);
      $params[\Simplify\Db\QueryParameters::DATA][$pk] = $this->id;
    }

    while ($elements->valid()) {
      $elements->current()->onInjectQueryParams($action, $params);
      $elements->next();
    }

    $this->onBeforeLoadData($params);

    foreach ($this->getFilters() as $filter) {
        $filter->onInjectQueryParams($action, $params);
    }
    
    $row = $this->repository()->find(null, $params);

    $data[$this->getName()][\Simplify\Form::ID] = $row[$pk];

    $elements->rewind();
    while ($elements->valid()) {
      $elements->current()->onLoadData($action, $data[$this->getName()], $row);
      $elements->next();
    }

    $this->onAfterLoadData($data[$this->getName()], $row, null);
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

      $data[$this->getName()][\Simplify\Form::ID] = sy_get_param($post[$this->getName()], \Simplify\Form::ID);
      $data[$this->getName()][$this->getForeignKeyColumn()] = $id;

      $elements = $this->getElements($action);
      while ($elements->valid()) {
        $elements->current()->onPostData($action, $data[$this->getName()], $post[$this->getName()]);
        $elements->next();
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

    if (!empty($data[$this->getName()])) {
      $_row = array();

      $_row[$this->getPrimaryKey()] = $data[$this->getName()][\Simplify\Form::ID];

      $_row[$this->getForeignKeyColumn()] = $data[$this->getReferenceColumn()];

      while ($elements->valid()) {
        $elements->current()->onCollectTableData($action, $_row, $data[$this->getName()]);
        $elements->next();
      }

      $this->onBeforeSave($action, $_row, $data[$this->getName()]);

      foreach ($this->getFilters() as $filter) {
          $filter->onCollectTableData($action, $_row, $data[$this->getName()]);
      }
      
      $this->repository()->save($_row);

      $id[] = $row[\Simplify\Form::ID] = $_row[$this->getPrimaryKey()];
    }
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
    if (empty($data[$this->getName()]))
      return;

    $elements = $this->getElements($action);

    foreach ($data[$this->getName()] as $index => $row) {
      $elements->rewind();
      while ($elements->valid()) {
        $elements->current()->onBeforeDelete($action, $row);
      }
    }
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Component::onAfterDelete()
   */
  public function onAfterDelete(\Simplify\Form\Action $action, &$data)
  {
    if (empty($data[$this->getName()]))
      return;

    $pk = $this->getPrimaryKey();
    $fk = $this->getForeignKeyColumn();

    $id = $data[$this->getReferenceColumn()];

    $elements = $this->getElements($action);

    if ($this->deletePolicy == self::CASCADE) {
      $params = array();
      $params[\Simplify\Db\QueryParameters::WHERE][] = \Simplify\Db\QueryObject::buildIn($fk, $id);

      $this->repository()->deleteAll($params);

      foreach ($data[$this->getName()] as $index => $row) {
        while ($elements->valid()) {
          $elements->current()->onAfterDelete($action, $row);
          $elements->next();
        }
      }
    }
    elseif ($this->deletePolicy == self::SETNULL) {
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