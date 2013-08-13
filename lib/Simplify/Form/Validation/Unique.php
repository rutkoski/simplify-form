<?php

class Simplify_Form_Validation_Unique extends Simplify_Validation_AbstractValidation
{

  /**
   *
   * @var Simplify_Form_Element
   */
  public $element;

  /**
   * Constructor
   *
   * @param string $message validation fail message
   */
  function __construct($message = '', Simplify_Form_Element $element = null)
  {
    parent::__construct($message);

    $this->element = $element;
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_ValidationInterface::validate()
   */
  public function validate($value)
  {
    $field = $this->element->getFieldName();

    $repo = $this->element->form->repository();

    $params = array();
    $params[Simplify_Db_QueryParameters::WHERE] = "{$field} = :{$field}";
    $params[Simplify_Db_QueryParameters::DATA][$field] = $value;

    $found = $repo->findCount($params);

    if (! empty($found)) {
      $this->fail();
    }
  }

}