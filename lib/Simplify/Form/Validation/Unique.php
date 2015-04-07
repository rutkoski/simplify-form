<?php

namespace Simplify\Form\Validation;

class Unique extends \Simplify\Validation\AbstractValidation
{

  /**
   *
   * @var \Simplify\Form\Element
   */
  public $element;

  /**
   *
   * @var int
   */
  public $id;

  /**
   * Constructor
   *
   * @param string $message validation fail message
   */
  function __construct($message = '', \Simplify\Form\Element $element = null, $id = null)
  {
    parent::__construct($message);

    $this->element = $element;
    $this->id = $id;
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\ValidationInterface::validate()
   */
  public function validate($value)
  {
    $field = $this->element->getFieldName();

    $repo = $this->element->form->repository();

    $pk = $repo->pk;

    $params = array();
    $params[\Simplify\Db\QueryParameters::WHERE][] = "{$field} = :{$field}";
    $params[\Simplify\Db\QueryParameters::DATA][$field] = $value;

    if ($this->id) {
      $params[\Simplify\Db\QueryParameters::WHERE][] = "{$pk} != :{$pk}";
      $params[\Simplify\Db\QueryParameters::DATA][$pk] = $this->id;
    }

    $found = $repo->findCount($params);

    if (! empty($found)) {
      $this->fail();
    }
  }

}
