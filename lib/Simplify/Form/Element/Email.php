<?php

class Simplify_Form_Element_Email extends Simplify_Form_Element
{

  /**
   *
   */
  public function onValidate(Simplify_Form_Action $action, $data, $index)
  {
    $rule = new Simplify_Validation_EmailValidator('Invalid email address');
    $rule->validate($this->getValue($data, $index));
  }

  /**
   *
   */
  public function getTemplateFilename()
  {
    if (! empty($this->style)) {
      return $this->style;
    }

    return 'form_element_text';
  }

}
