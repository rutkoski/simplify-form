<?php

class Simplify_Form_Element_Textarea extends Simplify_Form_Element
{

  /**
   * Truncate data on list actions
   *
   * @var boolean|int
   */
  public $truncate = 80;

  public function getDisplayValue(Simplify_Form_Action $action, $data, $index)
  {
    $value = $this->getValue($data, $index);

    if ($this->truncate) {
      $value = sy_truncate($value, $this->truncate);
    }

    return $value;
  }

}
