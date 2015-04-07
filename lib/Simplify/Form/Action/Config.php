<?php

namespace Simplify\Form\Action;

class Config extends \Simplify\Form\Action
{

  /**
   * Table name
   *
   * @var string
   */
  public $table;

  /**
   * Config name column/primary key
   *
   * @var string
   */
  public $nameField;

  /**
   * Config value column
   *
   * @var string
   */
  public $valueField;

  /**
   *
   * @var string
   */
  protected $template = 'form_form';

  /**
   *
   * @var int
   */
  protected $actionMask = \Simplify\Form::ACTION_CONFIG;

  /**
   *
   * @param string $name
   * @param string $table
   * @param string $nameField
   * @param string $valueField
   */
  public function __construct($name = null, $table = null, $nameField = null, $valueField = null)
  {
    parent::__construct($name);

    $this->table = $table;
    $this->nameField = $nameField;
    $this->valueField = $valueField;
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Action::onCreateMenu()
   */
  public function onCreateMenu(\Simplify\Menu $menu)
  {
    $menu->getItemByName('main')->addItem(
        new \Simplify\MenuItem($this->getName(), $this->getTitle(), null,
            new \Simplify\URL(null, array('formAction' => $this->getName()))));
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Action::onExecute()
   */
  public function onExecute()
  {
    parent::onExecute();

    $this->onLoadData();

    if (\Simplify::request()->method(\Simplify\Request::POST)) {
      $this->onPostData();
      $this->onValidate();
      $this->onSave();

      return \Simplify\Form::RESULT_SUCCESS;
    }
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Action::onRender()
   */
  public function onRender()
  {
    $elements = $this->getElements();

    $data = array();

    $line = array();
    $line['name'] = \Simplify\Form::ID . "[]";
    $line[\Simplify\Form::ID] = null;
    $line['elements'] = array();

    while ($elements->valid()) {
      $element = $elements->current();

      $line['index'] = $element->getName();
      //$line['menu'] = new \Simplify\Menu('actions');
      //$line['menu']->addItem(new \Simplify\Menu('main'));

      $element->onRenderControls($this, $line, $this->formData[$element->getName()]['data'], $element->getName());

      $elements->next();
    }

    //$this->form->onCreateItemMenu($line['menu'], $this, null);

    $data[] = $line;

    $this->set('data', $data);

    return parent::onRender();
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Action::onPostData()
   */
  public function onPostData()
  {
    $post = \Simplify::request()->post('formData');
    $files = \Simplify::request()->files('formData');

    $elements = $this->getElements();

    while ($elements->valid()) {
      $element = $elements->current();

      if (!empty($files)) {
        foreach ($files as $k => $file) {
          foreach ($file[$element->getName()] as $field => $value) {
            $post[$element->getName()][$field][$k] = $value['file'];
          }
        }
      }

      $element->onPostData($this, $this->formData[$element->getName()]['data'], $post[$element->getName()]);

      $elements->next();
    }
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Action::onSave()
   */
  protected function onSave()
  {
    $elements = $this->getElements();

    //foreach ($elements as &$element) {
    $elements->rewind();
    while ($elements->valid()) {
      $element = $elements->current();
      $elements->next();

      $row = array();

      $element->onCollectTableData($this, $row, $this->formData[$element->getName()]['data']);

      $data = array();
      $data[$this->getNameField()] = $element->getFieldName();
      $data[$this->getValueField()] = $row[$element->getFieldName()];

      if (empty($this->formData[$element->getName()]['found'])) {
        \Simplify::db()->insert($this->getTable(), $data)->execute();
      }
      else {
        \Simplify::db()->update($this->getTable(), $data, "{$this->getNameField()} = :{$this->getNameField()}")->execute();
      }
    }
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Action::onLoadData()
   */
  protected function onLoadData()
  {
    $elements = $this->getElements();

    $name = $this->getNameField();
    $value = $this->getValueField();

    $params = array();
    $params[\Simplify\Db\QueryParameters::SELECT][] = $value;

    $this->formData = array();

    $elements->rewind();
    while ($elements->valid()) {
      $element = $elements->current();
      $elements->next();

      $params[\Simplify\Db\QueryParameters::WHERE][] = "{$name} = :{$name}";
      $params[\Simplify\Db\QueryParameters::DATA] = array($name => $element->getFieldName());

      $row = array();

      $row['found'] = \Simplify::db()->query()->from($this->getTable())->setParams($params)->execute()->fetchRow();

      $row['data'][$element->getFieldName()] = sy_get_param($row['found'], $value);

      $this->formData[$element->getName()] = $row;

      $element->onLoadData($this, $this->formData[$element->getName()]['data'], $row['data']);
    }
  }

  /**
   *
   * @return string
   */
  public function getTable()
  {
    if (empty($this->table)) {
      return $this->form->getTable() . '_config';
    }

    return $this->table;
  }

  /**
   *
   * @return string
   */
  public function getNameField()
  {
    if (empty($this->nameField)) {
      $this->nameField = $this->getTable() . '_name';
    }

    return $this->nameField;
  }

  /**
   *
   * @return string
   */
  public function getValueField()
  {
    if (empty($this->valueField)) {
      $this->valueField = $this->getTable() . '_value';
    }

    return $this->valueField;
  }

}
