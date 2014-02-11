<?php

class Simplify_Form_Action_Config extends Simplify_Form_Action
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
  protected $actionMask = Simplify_Form::ACTION_CONFIG;

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
   * @see Simplify_Form_Action::onCreateMenu()
   */
  public function onCreateMenu(Simplify_Menu $menu)
  {
    $menu->getItemByName('main')->addItem(
      new Simplify_MenuItem($this->getName(), $this->getTitle(), null,
        new Simplify_URL(null, array('formAction' => $this->getName()))));
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Action::onExecute()
   */
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

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Action::onRender()
   */
  public function onRender()
  {
    $elements = $this->getElements();

    $data = array();

    $line = array();
    $line['name'] = Simplify_Form::ID . "[]";
    $line[Simplify_Form::ID] = null;
    $line['elements'] = array();

    foreach ($elements as $element) {
      $line['index'] = $element->getName();
      $line['menu'] = new Simplify_Menu('actions', null, Simplify_Menu::STYLE_TOOLBAR);
      $line['menu']->addItem(new Simplify_Menu('main', null, Simplify_Menu::STYLE_BUTTON_GROUP));

      $element->onRenderControls($this, $line, $this->formData[$element->getName()]['data'], $element->getName());
    }

    $this->form->onCreateItemMenu($line['menu'], $this, null);

    $data[] = $line;

    $this->set('data', $data);

    return parent::onRender();
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Action::onPostData()
   */
  public function onPostData()
  {
    $post = s::request()->post('formData');
    $files = s::request()->files('formData');

    $elements = $this->getElements();

    foreach ($elements as $element) {
      if (!empty($files)) {
        foreach ($files as $k => $file) {
          foreach ($file[$element->getName()] as $field => $value) {
            $post[$element->getName()][$field][$k] = $value['file'];
          }
        }
      }

      $element->onPostData($this, $this->formData[$element->getName()]['data'], $post[$element->getName()]);
    }
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Action::onSave()
   */
  protected function onSave()
  {
    $elements = $this->getElements();

    foreach ($elements as &$element) {
      $row = array();

      $element->onCollectTableData($this, $row, $this->formData[$element->getName()]['data']);

      $data = array();
      $data[$this->getNameField()] = $element->getFieldName();
      $data[$this->getValueField()] = $row[$element->getFieldName()];

      if (empty($this->formData[$element->getName()]['found'])) {
        s::db()->insert($this->getTable(), $data)->execute();
      }
      else {
        s::db()->update($this->getTable(), $data, "{$this->getNameField()} = :{$this->getNameField()}")->execute();
      }
    }
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Action::onLoadData()
   */
  protected function onLoadData()
  {
    $elements = $this->getElements();

    $name = $this->getNameField();
    $value = $this->getValueField();

    $params = array();
    $params[Simplify_Db_QueryParameters::SELECT][] = $value;

    $this->formData = array();

    foreach ($elements as &$element) {
      $params[Simplify_Db_QueryParameters::WHERE][] = "{$name} = :{$name}";
      $params[Simplify_Db_QueryParameters::DATA] = array($name => $element->getFieldName());

      $row = array();

      $row['found'] = s::db()->query()->from($this->getTable())->setParams($params)->execute()->fetchRow();

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
