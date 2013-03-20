<?php

class Simplify_Form_Element_Datetime extends Simplify_Form_Element
{

  /**
   *
   * @var string
   */
  public $displaySimplify_Form_at = 'd/m/Y H:i:s';

  public function onRender(Simplify_Form_Action $action, $data, $index)
  {
    $this->set('formatedValue', $this->getDisplayValue($action, $data, $index));

    return parent::onRender($action, $data, $index);
  }

  public function onPostData(&$row, $data, $index)
  {
    $date = $data[$index][$this->getName()];

    if (function_exists('date_parse_from_format')) {
      $dt = date_parse_from_format($this->displaySimplify_Form_at, $date);
      $dt = mktime($dt['hour'], $dt['minute'], $dt['second'], $dt['month'], $dt['day'], $dt['year']);

      $value = date('Y-m-d H:i:s', $dt);
    }
    elseif (function_exists('strptime')) {
      $dt = strptime($date, $this->displaySimplify_Form_at);

      $value = date('Y-m-d H:i:s', $dt);
    }
    else {
      $parts = explode(' ', $date);

      $date = explode('/', $parts[0]);

      $d = $date[0];
      $m = $date[1];
      $Y = $date[2];

      $value = "$Y-$m-$d $parts[1]";
    }

    $row[$this->getFieldName()] = $value;
  }

  public function getDisplayValue(Simplify_Form_Action $action, $data, $index)
  {
    return date($this->displaySimplify_Form_at, strtotime($this->getValue($data, $index)));
  }

  public function getDefaultValue()
  {
    return date('Y-m-d H:i:s');
  }

}
