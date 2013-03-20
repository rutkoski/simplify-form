<?php

abstract class Simplify_Form_Action_Form extends Simplify_Form_Action
{

  protected $template = 'form_form';

  protected $parentSelect;

  public function onExecute()
  {
    parent::onExecute();

    $this->onLoadData();

    if (s::request()->method(Simplify_Request::POST)) {
      $this->onPostData();
      $this->onValidate();
      $this->onSave();

      return Simplify_Form::RESULT_SUCCESS;
    }
  }

  public function onRender()
  {
    $elements = $this->getElements();

    $data = array();
    foreach ($this->formData as $index => $formRow) {
      $line = array();
      $line['name'] = Simplify_Form::ID . "[]";
      $line[Simplify_Form::ID] = $formRow[Simplify_Form::ID];
      $line['elements'] = array();
      $line['index'] = $index;
      $line['menu'] = new Simplify_Menu('actions', null, Simplify_Menu::STYLE_TOOLBAR);
      $line['menu']->addItem(new Simplify_Menu('main', null, Simplify_Menu::STYLE_BUTTON_GROUP));

      foreach ($elements as $element) {
        $line['elements'][] = array(
          'id' => $element->getElementId($index),
          'name' => $element->getInputName($index),
          'class' => $element->getElementClass(),
          'label' => $element->getLabel(),
          'controls' => $element->onRender($this, $this->formData, $index)->render(),
        );
      }

      $this->form->onCreateItemMenu($line['menu'], $this, $this->formData, $index);

      $data[] = $line;
    }

    $this->set('data', $data);

    return parent::onRender();
  }

  protected function onSave()
  {
    $elements = $this->getElements();

    $filters = $this->form->getFilters();

    foreach ($this->formData as &$formRow) {
      // this row will be saved in the database
      $row = array();

      $row[$this->form->getPrimaryKey()] = $formRow[Simplify_Form::ID];

      foreach ($elements as &$element) {
        $element->onCollectTableData($row, $formRow);
      }

      foreach ($filters as &$filter) {
        $filter->onCollectTableData($row, $formRow);
      }

      $this->repository()->save($row);

      // fill the primary key if this is a new record
      $formRow[Simplify_Form::ID] = $row[$this->form->getPrimaryKey()];

      foreach ($elements as &$element) {
        $element->onSave($formRow);
      }
    }
  }

  protected function onLoadData()
  {
    $elements = $this->getElements();

    $id = $this->form->getId();
    $pk = $this->form->getPrimaryKey();

    $params = array();
    $params['fields'][] = $pk;
    $params['where'][] = Simplify_Db_QueryObject::buildIn($pk, $id);

    foreach ($elements as $element) {
      $element->onInjectQueryParams($params);
    }

    $data = $this->repository()->findAll($params);

    $this->formData = array();

    foreach ($data as $index => &$row) {
      $formRow = array();
      $formRow[Simplify_Form::ID] = $row[$pk];

      foreach ($elements as &$element) {
        $element->onLoadData($formRow, $data, $index);
      }

      $this->formData[] = $formRow;
    }
  }

}
