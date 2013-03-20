<?php

class Simplify_Form_Action_List extends Simplify_Form_Action
{

  /**
   *
   * @var int
   */
  public $limit = 10;

  /**
   *
   * @var int
   */
  public $offset = null;

  /**
   *
   * @var int
   */
  protected $actionMask = Simplify_Form::ACTION_LIST;

  /**
   *
   * @var Pager
   */
  protected $pager;

  /**
   *
   */
  public function onExecute()
  {
    parent::onExecute();

    $this->onLoadData();
  }

  /**
   *
   */
  public function onRender()
  {
    $elements = $this->getElements();

    $headers = array();
    foreach ($elements as $element) {
      $headers[] = $element->getLabel();
    }

    $data = array();
    foreach ($this->formData as $index => $row) {
      $line = array();
      $line[Simplify_Form::ID] = $row[Simplify_Form::ID];
      $line['name'] = Simplify_Form::ID . "[]";
      $line['menu'] = new Simplify_Menu('actions', null, Simplify_Menu::STYLE_TOOLBAR);
      $line['menu']->addItem(new Simplify_Menu('main', null, Simplify_Menu::STYLE_BUTTON_GROUP));

      $line['elements'] = array();
      foreach ($elements as $element) {
        $line['elements'][$element->getName()] = $element->getDisplayValue($this, $this->formData, $index);
      }

      $this->form->onCreateItemMenu($line['menu'], $this, $this->formData, $index);

      $data[] = $line;
    }

    $bulk = array();

    $this->form->onCreateBulkOptions($bulk);

    $this->set('headers', $headers);
    $this->set('data', $data);
    $this->set('pager', $this->pager);
    $this->set('bulk', $bulk);

    $this->form->dispatch(Simplify_Form::ON_RENDER, $this);

    return parent::onRender();
  }

  /**
   *
   */
  public function onCreateMenu(Simplify_Menu $menu)
  {
    $menu->getItemByName('main')->addItem(new Simplify_MenuItem($this->getName(), $this->getTitle(), null, new Simplify_URL(null, array('formAction' => $this->getName()))));
  }

  /**
   *
   */
  protected function onLoadData()
  {
    $elements = $this->getElements();

    $params = array();
    $params['fields'][] = $this->form->getPrimaryKey();
    $params['limit'] = $this->getLimit();
    $params['offset'] = $this->getOffset();

    foreach ($elements as &$element) {
      $element->onInjectQueryParams($params);
    }

    foreach ($this->form->getFilters() as $filter) {
      $filter->onInjectQueryParams($params);
    }

    $this->onInjectQueryParams($params);

    $data = $this->repository()->findAll($params);

    $this->formData = array();

    foreach ($data as $index => $row) {
      $formRow = array();
      $formRow[Simplify_Form::ID] = $row[$this->form->getPrimaryKey()];

      foreach ($elements as &$element) {
        $element->onLoadData($formRow, $data, $index);
      }

      $this->formData[] = $formRow;
    }

    $this->pager = $this->repository()->findPager($params);
  }

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
    return s::request()->get('offset', $this->offset);
  }

}
