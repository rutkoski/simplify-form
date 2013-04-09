<?php

class Simplify_Form_Element_Computed extends Simplify_Form_Element
{

  public $callback;

  /**
   *
   */
  public function onLoadData(&$row, $data, $index)
  {
    $row[$this->getName()] = call_user_func($this->callback, $data, $index);
  }

  /**
   *
   */
  public function onRender(Simplify_Form_Action $action, $row, $index)
  {
  }

  /**
   *
   */
  public function onCollectTableData(&$row, $data)
  {
  }

}
