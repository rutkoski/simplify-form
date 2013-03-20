<?php

class Simplify_Form_Element_Base_HasMany extends Simplify_Form_Element
{

  /**
   * @var array
   */
  public $fields = array();

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
  public $foreignKey;

  /**
   *
   * @var string
   */
  public $style;

  /**
   *
   * @var Simplify_Form_Element[]
   */
  protected $elements = array();

  /**
   *
   * @var IRepository
   */
  protected $repository;

  public function onRender(Simplify_Form_Action $action, $data, $index)
  {
    $elements = $this->getElements();

    $pk = $this->getPrimaryKey();

    $lines = array();

    $row = $this->getRow($data, $index);

    if (isset($row[$this->getName()])) {
      foreach ($row[$this->getName()] as $_index => $_row) {
        $__index = array_merge((array) $index, array($this->getName(), $_index));

        $line = array();
        $line['id'] = "formData_".implode('_', $__index);
        $line['name'] = "formData[".implode('][', $__index)."]";
        $line[Simplify_Form::ID] = $_row[Simplify_Form::ID];
        $line['elements'] = array();

        foreach ($elements as $element) {
          $line['elements'][] = array(
            'label' => $element->getLabel(),
            'controls' => $element->onRender($action, $data, array($index, $this->getName(), $_index))->render(),
          );
        }

        $this->onRenderRow($line, $data, array($index, $this->getName(), $_index));

        $lines[] = $line;
      }
    }

    $dummy = array();
    $dummy['name'] = "formData[{$index}][{$this->getName()}][dummy]";
    $dummy[Simplify_Form::ID] = '';
    $dummy['elements'] = array();
    foreach ($elements as $element) {
      $dummy['elements'][] = array(
        'label' => $element->getLabel(),
        'controls' => $element->onRender($action, $dummy, array($index, $this->getName(), 'dummy'))->render(),
      );
    }

    $this->set('data', $lines);
    $this->set('dummy', $dummy);

    return parent::onRender($action, $row, $index);
  }

  public function onRenderRow(&$row, $data, $index)
  {
  }

  public function onLoadData(&$row, $data, $index)
  {
    $elements = $this->getElements();

    $pk = $this->getPrimaryKey();
    $fk = $this->getForeignKey();

    $id = $data[$index][$this->form->getPrimaryKey()];

    $params = array();
    $params['fields'][] = $pk;
    $params['fields'][] = $fk;
    $params['where'][] = Simplify_Db_QueryObject::buildIn($fk, $id);
    $params['data'][$fk] = $id;

    foreach ($elements as $element) {
      $element->onInjectQueryParams($params);
    }

    $row[$this->getName()] = array();

    $this->onBeforeFindAll($params);

    $data[$index][$this->getName()] = $this->repository()->findAll($params);

    foreach ($data[$index][$this->getName()] as $_index => $_row) {
      $__row = array();
      $__row[Simplify_Form::ID] = $_row[$pk];

      foreach ($elements as &$element) {
        $element->onLoadData($__row, $data, array($index, $this->getName(), $_index));
      }

      $this->onAfterFindAll($__row, $data, array($index, $this->getName(), $_index));

      $row[$this->getName()][] = $__row;
    }

    $this->data[$index] = $row;
  }

  public function onPostData(&$row, $data, $index)
  {
    $id = $row[Simplify_Form::ID];

    $row[$this->getName()] = array();

    $_row = $this->getRow($data, $index);

    if (! empty($_row[$this->getName()])) {
      foreach ($_row[$this->getName()] as $i => $__row) {
        if ($i !== 'dummy') {
          $_id = $__row[Simplify_Form::ID];

          $row[$this->getName()][$i][Simplify_Form::ID] = $_id;
          $row[$this->getName()][$i][$this->getForeignKey()] = $id;

          foreach ($this->getElements() as $element) {
            $element->onPostData($row[$this->getName()][$i], $_row[$this->getName()], $i);
          }

          $this->onAfterPostData($row[$this->getName()][$i], $_row[$this->getName()], $i);
        }
      }
    }
  }

  public function onSave(&$row)
  {
    $id = array();

    $elements = $this->getElements();

    foreach ($row[$this->getName()] as &$_row) {
      $__row = array();

      if (strpos($_row[Simplify_Form::ID], 'new-') !== 0) {
        $__row[$this->getPrimaryKey()] = $_row[Simplify_Form::ID];
      }

      $__row[$this->getForeignKey()] = $row[Simplify_Form::ID];

      foreach ($elements as &$element) {
        $element->onCollectTableData($__row, $_row);
      }

      $this->onBeforeSave($__row, $_row);

      $this->repository()->save($__row);

      $id[] = $_row[Simplify_Form::ID] = $__row[$this->getPrimaryKey()];
    }

    $params = array();
    $params['where'][] = Simplify_Db_QueryObject::buildIn($this->getForeignKey(), $row[Simplify_Form::ID]);
    $params['where'][] = Simplify_Db_QueryObject::buildIn($this->getPrimaryKey(), $id, true);

    $deleted = $this->repository()->findAll($params);

    $this->repository()->deleteAll($params);

    $this->onAfterSave($row, $deleted);
  }

  public function onInjectQueryParams(&$params)
  {
    // do nothing
  }

  public function onCollectTableData(&$row, $data)
  {
    // do nothing
  }

  public function getTable()
  {
    if (empty($this->table)) {
      $this->table = Inflector::tableize($this->getName());
    }

    return $this->table;
  }

  public function getPrimaryKey()
  {
    if (empty($this->primaryKey)) {
      $this->primaryKey = $this->form->getPrimaryKey();
    }

    return $this->primaryKey;
  }

  public function getForeignKey()
  {
    if (empty($this->foreignKey)) {
      $this->foreignKey = Inflector::singularize($this->getTable()) . '_id';
    }

    return $this->foreignKey;
  }

  /**
   *
   * @param Simplify_Form_Element $element
   * @return Simplify_Form_Element
   */
  public function addElement(Simplify_Form_Element $element)
  {
    $element->form = $this;

    $this->elements[] = $element;

    return $element;
  }

  /**
   *
   * @return Simplify_Form_Element[]
   */
  public function getElements()
  {
    return $this->elements;
  }

  public function getFieldName($name = null)
  {
    if (is_null($name))
      return parent::getFieldName();

    if (is_array($this->fields) && isset($this->fields[$name])) {
      $name = $this->fields[$name];
    } else {
      $name = Inflector::singularize($this->table) . '_' . $name;
    }

    return $name;
  }

  /**
   *
   * @return IRepository
   */
  public function repository()
  {
    if (empty($this->repository)) {
      $this->repository = new Simplify_Form_Repository($this->getTable(), $this->getPrimaryKey());
    }

    return $this->repository;
  }

  protected function onBeforeFindAll(&$queryParams)
  {
  }

  protected function onAfterFindAll(&$row, $data, $index)
  {
  }

  protected function onAfterPostData(&$row, $data, $index)
  {
  }

  protected function onBeforeSave(&$row, $data)
  {
  }

  protected function onAfterSave(&$row, $deleted)
  {
  }

}
