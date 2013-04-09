<?php

class Simplify_Form_Filter_Datetime extends Simplify_Form_Filter
{

  /**
   *
   * @var string
   */
  public $displayFormat = 'd/m/Y H:i:s';

  /**
   * Filter values from this datetime
   *
   * @var string
   */
  public $since;

  /**
   * Filter values until this datetime
   *
   * @var string|int
   */
  public $until;

  /**
   * Minimum selectable date
   *
   * @var string|int
   */
  public $minDate;

  /**
   * Maximum  selectable date
   *
   * @var string
   */
  public $maxDate;

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Filter::onExecute()
   */
  public function onExecute(Simplify_Form_Action $action)
  {
    parent::onExecute($action);

    $this->loadDatabaseLimits();

    $name = $this->getName();

    $this->since = $this->getSinceValue($this->minDate);
    $this->until = $this->getUntilValue($this->maxDate);
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Filter::onRender()
   */
  public function onRender(Simplify_Form_Action $action)
  {
    $this->set('formatedSince', Simplify_Form_DateTime::datetime($this->since));
    $this->set('formatedUntil', Simplify_Form_DateTime::datetime($this->until));
    $this->set('sinceEnabled', $this->sinceEnabled());
    $this->set('untilEnabled', $this->untilEnabled());

    return parent::onRender($action);
  }

  /**
   * (non-PHPdoc)
   * @see Simplify_Form_Component::onInjectQueryParams()
   */
  public function onInjectQueryParams(Simplify_Form_Action $action, &$params)
  {
    if ($this->sinceEnabled()) {
      $value = $this->getSinceValue();
      $name = "{$this->getName()}_since";

      $params[Simplify_Db_QueryParameters::WHERE][] = "{$this->getFieldName()} >= :{$name}";
      $params[Simplify_Db_QueryParameters::DATA][$name] = Simplify_Form_DateTime::database($value);
    }

    if ($this->untilEnabled()) {
      $value = $this->getUntilValue();
      $name = "{$this->getName()}_until";

      $params[Simplify_Db_QueryParameters::WHERE][] = "{$this->getFieldName()} <= :{$name}";
      $params[Simplify_Db_QueryParameters::DATA][$name] = Simplify_Form_DateTime::database($value);
    }

    parent::onInjectQueryParams($action, $params);
  }

  /**
   *
   * @return boolean
   */
  public function sinceEnabled()
  {
    return $this->getSinceValue() ? true : false;
  }

  /**
   *
   * @return string
   */
  public function getSinceValue($default = null)
  {
    return s::request()->get($this->getName() . '_since', $default);
  }

  /**
   *
   * @return boolean
   */
  public function untilEnabled()
  {
    return $this->getUntilValue() ? true : false;
  }

  /**
   *
   * @return string
   */
  public function getUntilValue($default = null)
  {
    return s::request()->get($this->getName() . '_until', $default);
  }

  /**
   *
   * @return string
   */
  protected function getMinDate()
  {
    return date('Y-m-d H:i:s', $this->minDate);
  }

  /**
   *
   * @return string
   */
  protected function getMaxDate()
  {
    return date('Y-m-d H:i:s', $this->maxDate);
  }

  /**
   * Get minimum and maximum datetime values from database
   *
   * @return string[]
   */
  protected function loadDatabaseLimits()
  {
    $limits = s::db()->query()->from($this->form->getTable())->select(
      "MIN({$this->getFieldName()}) AS min, MAX({$this->getFieldName()}) AS max")->execute()->fetchRow();

    $minDate = Simplify_Form_DateTime::timestamp($limits['min']);
    $maxDate = Simplify_Form_DateTime::timestamp($limits['max']);

    $this->minDate = max(Simplify_Form_DateTime::timestamp($this->minDate), $minDate);
    $this->maxDate = min(Simplify_Form_DateTime::timestamp($this->maxDate), $maxDate);

    if (empty($this->maxDate)) {
      $this->maxDate = $maxDate;
    }
  }

}
