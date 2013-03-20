<?php

abstract class Simplify_Form_Element extends Simplify_Form_Component
{

  /**
   *
   * @return array
   */
  public function getRow(&$data, $index)
  {
    $row = $data;

    if (is_array($data)) {
      foreach ((array) $index as $i) {
        $row = $row[$i];
      }
    }

    return $row;
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::getValue()
   */
  public function getValue($data, $index)
  {
    $row = $this->getRow($data, $index);
    return sy_get_param($row, $this->getName(), $this->getDefaultValue());
  }

  /**
   * Get the display value for the element.
   *
   * @param Simplify_Form_Action $action current action
   * @param array $row current form row
   * @param index $index current row index
   * @return string the display value
   */
  public function getDisplayValue(Simplify_Form_Action $action, $data, $index)
  {
    return $this->getValue($data, $index);
  }

  /**
   * Get the input name for a given row $index.
   *
   * @param int $index
   * @return string
   */
  public function getInputName($index)
  {
    return "formData[".implode('][', (array) $index)."][".$this->getName()."]";
  }

  public function getElementClass()
  {
    return Inflector::underscore(get_class($this));
  }

  public function getElementId($index)
  {
    return "formData-".implode('-', (array) $index)."-".$this->getName();
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onRender()
   */
  public function onRender(Simplify_Form_Action $action, $data, $index)
  {
    $row = is_array($index) ? $index[0] : $data[$index];

    $this->set(Simplify_Form::ID, $row[Simplify_Form::ID]);
    $this->set('id', $this->getElementId($index));
    $this->set('name', $this->getInputName($index));
    $this->set('class', $this->getElementClass());
    $this->set('index', $index);
    $this->set('label', $this->getLabel());
    $this->set('value', $this->getValue($data, $index));
    $this->set('action', $action);

    return parent::onRender($action);
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onInjectQueryParams()
   */
  public function onInjectQueryParams(&$params)
  {
    $params['fields'][] = $this->getFieldName();
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onLoadData()
   */
  public function onLoadData(&$row, $data, $index)
  {
    $_row = $this->getRow($data, $index);
    $row[$this->getName()] = $_row[$this->getFieldName()];
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onPostData()
   */
  public function onPostData(&$row, $data, $index)
  {
    $_row = $this->getRow($data, $index);
    $row[$this->getFieldName()] = $_row[$this->getName()];
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onCollectTableData()
   */
  public function onCollectTableData(&$row, $data)
  {
    $row[$this->getFieldName()] = $data[$this->getName()];
  }

}
