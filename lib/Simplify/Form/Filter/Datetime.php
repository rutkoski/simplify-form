<?php

namespace Simplify\Form\Filter;

class Datetime extends \Simplify\Form\Filter
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
   * @see \Simplify\Form\Filter::onExecute()
   */
  public function onExecute(\Simplify\Form\Action $action)
  {
    parent::onExecute($action);

    $this->loadDatabaseLimits();

    $name = $this->getName();

    $this->since = $this->getSinceValue($this->minDate);
    $this->until = $this->getUntilValue($this->maxDate);
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Filter::onRender()
   */
  public function onRender(\Simplify\Form\Action $action)
  {
    $this->set('formatedSince', \Simplify\Form\DateTime::datetime($this->since));
    $this->set('formatedUntil', \Simplify\Form\DateTime::datetime($this->until));
    $this->set('sinceEnabled', $this->sinceEnabled());
    $this->set('untilEnabled', $this->untilEnabled());

    return parent::onRender($action);
  }

  /**
   * (non-PHPdoc)
   * @see \Simplify\Form\Component::onInjectQueryParams()
   */
  public function onInjectQueryParams(\Simplify\Form\Action $action, &$params)
  {
    if ($this->sinceEnabled()) {
      $value = $this->getSinceValue();
      $name = "{$this->getName()}_since";

      $params[\Simplify\Db\QueryParameters::WHERE][] = "{$this->getFieldName()} >= :{$name}";
      $params[\Simplify\Db\QueryParameters::DATA][$name] = \Simplify\Form\DateTime::database($value);
    }

    if ($this->untilEnabled()) {
      $value = $this->getUntilValue();
      $name = "{$this->getName()}_until";

      $params[\Simplify\Db\QueryParameters::WHERE][] = "{$this->getFieldName()} <= :{$name}";
      $params[\Simplify\Db\QueryParameters::DATA][$name] = \Simplify\Form\DateTime::database($value);
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
    return \Simplify::request()->get($this->getName() . '_since', $default);
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
    return \Simplify::request()->get($this->getName() . '_until', $default);
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
    $limits = \Simplify::db()->query()->from($this->form->getTable())->select(
      "MIN({$this->getFieldName()}) AS min, MAX({$this->getFieldName()}) AS max")->execute()->fetchRow();

    $minDate = \Simplify\Form\DateTime::timestamp($limits['min']);
    $maxDate = \Simplify\Form\DateTime::timestamp($limits['max']);

    $this->minDate = max(\Simplify\Form\DateTime::timestamp($this->minDate), $minDate);
    $this->maxDate = min(\Simplify\Form\DateTime::timestamp($this->maxDate), $maxDate);

    if (empty($this->maxDate)) {
      $this->maxDate = $maxDate;
    }
  }

}
