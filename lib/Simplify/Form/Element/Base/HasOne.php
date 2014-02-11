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
 * Base class for form elements that handle one to many associations
 *
 */
class Simplify_Form_Element_Base_HasOne extends Simplify_Form_Element_Base_Composite
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
   * @var Simplify_Form_Repository
   */
  protected $repository;

  /**
   *
   * @var int
   */
  protected $remove = Simplify_Form::ACTION_LIST;

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element::onRender()
   */
  public function onRender(Simplify_Form_Action $action, $data, $index)
  {
    $elements = $this->getElements();

    $pk = $this->getPrimaryKey();

    $line = array();

    $__index = array_merge((array) $index, array($this->getName()));

    $line['id'] = "formData_" . implode('_', $__index);
    $line['name'] = "formData[" . implode('][', $__index) . "][" . Simplify_Form::ID . "]";
    $line['baseName'] = "formData[" . implode('][', $__index) . "]";
    $line[Simplify_Form::ID] = $data[$this->getName()][Simplify_Form::ID];
    $line['elements'] = array();

    while ($elements->valid()) {
      $element = $elements->current();
      $line['elements'][] = array('label' => $element->getLabel(),
        'controls' => $element->onRender($action, $data[$this->getName()], array($index, $this->getName()))->render());
      $elements->next();
    }

    $this->onRenderRow($line, $data[$this->getName()], array($index, $this->getName()));

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

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element::onLoadData()
   */
  public function onLoadData(Simplify_Form_Action $action, &$data, $row)
  {
    $elements = $this->getElements();

    $pk = $this->getPrimaryKey();
    $fk = $this->getForeignKeyColumn();

    $id = $data[$this->getReferenceColumn()];

    $params = array();
    $params[Simplify_Db_QueryParameters::SELECT][] = $pk;
    $params[Simplify_Db_QueryParameters::SELECT][] = $fk;
    $params[Simplify_Db_QueryParameters::WHERE][] = Simplify_Db_QueryObject::buildIn($fk, $id);
    $params[Simplify_Db_QueryParameters::DATA][$fk] = $id;

    if (!empty($this->id)) {
      $params[Simplify_Db_QueryParameters::WHERE][] = Simplify_Db_QueryObject::buildIn($pk, $this->id);
      $params[Simplify_Db_QueryParameters::DATA][$pk] = $this->id;
    }

    while ($elements->valid()) {
      $elements->current()->onInjectQueryParams($action, $params);
      $elements->next();
    }

    $this->onBeforeLoadData($params);

    $row = $this->repository()->find(null, $params);

    $data[$this->getName()][Simplify_Form::ID] = $row[$pk];

    $elements->rewind();
    while ($elements->valid()) {
      $elements->current()->onLoadData($action, $data[$this->getName()], $row);
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
   * @see Simplify_Form_Element::onPostData()
   */
  public function onPostData(Simplify_Form_Action $action, &$data, $post)
  {
    $id = $data[$this->getReferenceColumn()];

    $data[$this->getName()] = array();

    if (!empty($post[$this->getName()])) {
      $position = 0;

      $data[$this->getName()][Simplify_Form::ID] = sy_get_param($post[$this->getName()], Simplify_Form::ID);
      $data[$this->getName()][$this->getForeignKeyColumn()] = $id;

      $elements = $this->getElements();
      while ($elements->valid()) {
        $elements->current()->onPostData($action, $data[$this->getName()], $post[$this->getName()]);
        $elements->next();
      }
    }
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onSave()
   */
  public function onSave(Simplify_Form_Action $action, &$data)
  {
    $id = array();

    $elements = $this->getElements();

    if (!empty($data[$this->getName()])) {
      $_row = array();

      $_row[$this->getPrimaryKey()] = $data[$this->getName()][Simplify_Form::ID];

      $_row[$this->getForeignKeyColumn()] = $data[$this->getReferenceColumn()];

      while ($elements->valid()) {
        $elements->current()->onCollectTableData($action, $_row, $data[$this->getName()]);
        $elements->next();
      }

      $this->onBeforeSave($action, $_row, $data[$this->getName()]);

      $this->repository()->save($_row);

      $id[] = $row[Simplify_Form::ID] = $_row[$this->getPrimaryKey()];
    }
  }

  /**
   *
   * @param Simplify_Form_Action $action
   * @param unknown_type $row
   * @param unknown_type $data
   */
  public function onBeforeSave(Simplify_Form_Action $action, &$row, $data)
  {
  }

  /**
   *
   * @param Simplify_Form_Action $action
   * @param unknown_type $data
   * @param unknown_type $deleted
   */
  protected function onAfterSave(Simplify_Form_Action $action, &$data, $deleted)
  {
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onBeforeDelete()
   */
  public function onBeforeDelete(Simplify_Form_Action $action, &$data)
  {
    if (empty($data[$this->getName()]))
      return;

    $elements = $this->getElements();

    foreach ($data[$this->getName()] as $index => $row) {
      $elements->rewind();
      while ($elements->valid()) {
        $elements->current()->onBeforeDelete($action, $row);
      }
    }
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onAfterDelete()
   */
  public function onAfterDelete(Simplify_Form_Action $action, &$data)
  {
    if (empty($data[$this->getName()]))
      return;

    $pk = $this->getPrimaryKey();
    $fk = $this->getForeignKeyColumn();

    $id = $data[$this->getReferenceColumn()];

    $elements = $this->getElements();

    if ($this->deletePolicy == self::CASCADE) {
      $params = array();
      $params[Simplify_Db_QueryParameters::WHERE][] = Simplify_Db_QueryObject::buildIn($fk, $id);

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
        $_row = array($pk => $row[Simplify_Form::ID], $fk => null);

        $this->repository()->save($_row);
      }
    }
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element::onInjectQueryParams()
   */
  public function onInjectQueryParams(&$params)
  {
    // nothing to see here! move along!
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Element::onCollectTableData()
   */
  public function onCollectTableData(Simplify_Form_Action $action, &$row, $data)
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
      $this->table = Simplify_Inflector::tableize($this->getName());
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
      $this->primaryKey = Simplify_Inflector::singularize($this->getTable()) . '_id';
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
      $this->foreignKeyColumn = Simplify_Inflector::singularize($this->getTable()) . '_' . $this->getReferenceColumn();
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
      $this->referenceColumn = Simplify_Form::ID;
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
   * @return Simplify_Form_Repository
   */
  public function repository()
  {
    if (empty($this->repository)) {
      $this->repository = new Simplify_Form_Repository($this->getTable(), $this->getPrimaryKey());
    }

    return $this->repository;
  }

}