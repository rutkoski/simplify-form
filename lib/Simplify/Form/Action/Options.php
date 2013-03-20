<?php

class Simplify_Form_Action_Options extends Simplify_Form_Action
{

  protected $template = 'form_form';

  /**
   *
   * @var int
   */
  protected $actionMask = Simplify_Form::ACTION_OPTIONS;

  public function onExecute()
  {
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
    foreach ($this->formData as $index => $row) {
      $line = array();
      $line['elements'] = array();
      $line[Simplify_Form::ID] = $row[Simplify_Form::ID];
      $line['index'] = $index;
      $line['menu'] = new Simplify_Menu('actions', null, Simplify_Menu::STYLE_TOOLBAR);
      $line['menu']->addItem(new Simplify_Menu('main', null, Simplify_Menu::STYLE_BUTTON_GROUP));
      $line['name'] = Simplify_Form::ID . "[]";

      foreach ($elements as $element) {
        $line['elements'][] = $element->onRender($this, $row, $index)->render();
      }

      $this->form->onCreateItemMenu($line['menu'], $this, $row);

      $data[] = $line;
    }

    $this->set('data', $data);

    return parent::onRender();
  }

  protected function onSave()
  {
    $elements = $this->getElements();

    foreach ($this->formData as &$row) {
      $_row = array();

      foreach ($elements as &$element) {
        $element->onCollectTableData($_row, $row);

        $this->options()->update($element->getFieldName(), $row[$element->getFieldName()]);
      }

      foreach ($elements as &$element) {
        $element->onSave($row);
      }
    }
  }

  protected function onLoadData()
  {
    $elements = $this->getElements();

    $this->formData = array();

    $data = array();
    $row = array();

    foreach ($elements as $element) {
      $data[$element->getFieldName()] = $this->options()->value($element->getFieldName());
      $element->onLoadData($row, $data);
    }

    $this->formData[] = $row;
  }

  protected function options()
  {
    return Options::getInstance($this->form->getTable(), $this->form->getPrimaryKey());
  }

}
