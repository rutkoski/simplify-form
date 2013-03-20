<?php

class Simplify_Form_Filter_Datetime extends Simplify_Form_Filter
{

  /**
   *
   * @var string
   */
  public $displaySimplify_Form_at = 'd/m/Y H:i:s';

  public $fromDate;

  public $toDate;

  public $minDate;

  public $maxDate;

  public function onRender(Simplify_Form_Action $action)
  {
    $this->set('formatedFromValue', $this->getSimplify_Form_atedValue($this->getFromValue()));
    $this->set('formatedToValue', $this->getSimplify_Form_atedValue($this->getToValue()));
    $this->set('fromEnabled', $this->getFromEnabled());
    $this->set('toEnabled', $this->getToEnabled());

    return parent::onRender($action);
  }

  public function onInjectQueryParams(&$params)
  {
    $value = $this->getValue();

    if ($this->getFromEnabled()) {
      $from = $this->getName() . '_from';

      $params['where'][] = "{$this->getName()} >= :{$from}";
      $params['data'][$from] = $this->getDatabaseValue($this->getFromValue());
    }

    if ($this->getToEnabled()) {
      $to = $this->getName() . '_to';

      $params['where'][] = "{$this->getName()} <= :{$to}";
      $params['data'][$to] = $this->getDatabaseValue($this->getToValue());
    }
  }

  public function getSimplify_Form_atedValue($value)
  {
    return date($this->displaySimplify_Form_at, strtotime($value));
  }

  public function getFromEnabled()
  {
    return s::request()->get($this->getName() . '_from_enabled');
  }

  public function getFromValue()
  {
    return ! empty($this->fromDate) ? $this->fromDate : ($this->getFromEnabled() ? s::request()->get($this->getName() . '_from') : $this->getMinValue());
  }

  public function getToEnabled()
  {
    return s::request()->get($this->getName() . '_to_enabled');
  }

  public function getToValue()
  {
    return ! empty($this->toDate) ? $this->toDate : ($this->getToEnabled() ? s::request()->get($this->getName() . '_to') : $this->getMaxValue());
  }

  protected function getMinValue()
  {
    $limits = $this->getLimits();
    return sy_get_param($limits, 'min');
  }

  protected function getMaxValue()
  {
    $limits = $this->getLimits();
    return sy_get_param($limits, 'max');
  }

  protected function getLimits()
  {
    static $limits;

    if (empty($limits)) {
      $limits = s::db()->query()->from($this->form->getTable())->select("MIN({$this->getName()}) AS min, MAX({$this->getName()}) AS max")->execute()->fetchRow();

      if (! empty($this->minDate)) {
        $limits['min'] = $this->minDate;
      }

      if (! empty($this->maxDate)) {
        $limits['max'] = $this->maxDate;
      }
    }

    return $limits;
  }

  protected function getDatabaseValue($value)
  {
    $date = $value;

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

    return $value;
  }

}
