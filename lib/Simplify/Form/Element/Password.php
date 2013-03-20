<?php

class Simplify_Form_Element_Password extends Simplify_Form_Element
{

  public $hashFunction = 'md5';

  /**
   *
   * @return void
   */
  public function __construct($name, $label = null)
  {
    parent::__construct($name, $label);

    $this->remove = Simplify_Form::ACTION_VIEW ^ Simplify_Form::ACTION_LIST;
  }

  /**
   *
   * @return mixed
   */
  public function getDisplayValue(Simplify_Form_Action $action, $row, $index)
  {
    return '';
  }

  /**
   *
   */
  public function onRender(Simplify_Form_Action $action, $row, $index)
  {
    return parent::onRender($action, $row, $index);
  }

  /**
   *
   */
  public function onPostData(&$row, $data, $index)
  {
    $a = $this->hash(sy_get_param(sy_get_param($data, $this->getName(), array()), 'a'));
    $b = $this->hash(sy_get_param(sy_get_param($data, $this->getName(), array()), 'b'));

    $empty = $this->hash('');

    if ($row[Simplify_Form::ID]) {
      if ($a != $b) {
        throw new ValidationException('Passwords do not match');
      }
      elseif ($a != $empty) {
        $row[$this->getName()] = $a;
      }
    }
    else {
      if ($a != $b) {
        throw new ValidationException('Passwords do not match');
      }
      elseif ($a == $empty || $b == $empty) {
        throw new ValidationException('Inform your password');
      }

      $row[$this->getName()] = $a;
    }
  }

  /**
   *
   */
  public function onCollectTableData(&$row, $data)
  {
    $row[$this->getFieldName()] = $data[$this->getName()];
  }

  protected function hash($s)
  {
    if ($this->hashFunction) {
      return call_user_func($this->hashFunction, $s);
    }

    return $s;
  }

}
