<?php

class Simplify_Form_Action_Delete extends Simplify_Form_Action
{

  /**
   *
   * @var int
   */
  protected $actionMask = Simplify_Form::ACTION_DELETE;

  public function onExecute()
  {
    $this->onLoadData();

    $this->onValidate();

    foreach ($this->formData as $row) {
      $this->form->dispatch(Simplify_Form::ON_BEFORE_DELETE, $this, $row);
    }

    if (s::request()->method(Simplify_Request::POST) && s::request()->post('deleteAction') == 'confirm') {
      $this->onDelete();

      return Simplify_Form::RESULT_SUCCESS;
    }
  }

  public function onRender()
  {
    $this->set(Simplify_Form::ID, (array) $this->form->getId());

    $data = array();
    foreach ($this->formData as $index => $row) {
      $line = array();
      $line[Simplify_Form::ID] = $row[Simplify_Form::ID];
      $line['name'] = Simplify_Form::ID . "[]";
      $line['label'] = $row['label'];

      $data[] = $line;
    }

    $this->set('data', $data);

    return parent::onRender();
  }

  public function onCreateItemMenu(Simplify_Menu $menu, Simplify_Form_Action $action, $data, $index)
  {
    if (! $action->show(Simplify_Form::ACTION_CREATE)) {
      $menu->getItemByName('main')->addItem(new Simplify_MenuItem($this->getName(), $this->getTitle(), null, new Simplify_URL(null, array('formAction' => $this->getName(), Simplify_Form::ID => $data[$index][Simplify_Form::ID]))));
    }
  }

  public function onCreateBulkOptions(array &$actions)
  {
    $actions[$this->getName()] = $this->getTitle();
  }

  protected function onDelete()
  {
    $elements = $this->getElements();

    foreach ($this->formData as $row) {
      foreach ($elements as $element) {
        $element->onBeforeDelete($this, $row);
      }
    }

    $id = $this->form->getId();
    $pk = $this->form->getPrimaryKey();

    $params = array();
    $params['where'][] = Simplify_Db_QueryObject::buildIn($pk, $id);
    $params['data'][$pk] = $id;

    $this->repository()->deleteAll($params);

    foreach ($this->formData as $row) {
      foreach ($elements as $element) {
        $element->onAfterDelete($this, $row);
      }
    }
  }

  protected function onLoadData()
  {
    $elements = $this->getElements();

    $id = $this->form->getId();
    $pk = $this->form->getPrimaryKey();
    $label = $this->form->getLabel();

    $params = array();
    $params['fields'][] = $pk;
    $params['fields'][] = $label;
    $params['where'][] = Simplify_Db_QueryObject::buildIn($pk, $id);

    foreach ($elements as $element) {
      $element->onInjectQueryParams($params);
    }

    $data = $this->repository()->findAll($params);

    $this->formData = array();

    foreach ($data as &$row) {
      $_row = array();
      $_row[Simplify_Form::ID] = $row[$pk];
      $_row['label'] = $row[$label];

      foreach ($elements as &$element) {
        $element->onLoadData($_row, $row);
      }

      $this->formData[] = $_row;
    }
  }

}
