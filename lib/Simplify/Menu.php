<?php

class Simplify_Menu extends Simplify_MenuItem
{

  const STYLE_LIST = 'nav-list';

  const STYLE_PILLS = 'nav-pills';

  const STYLE_DROPDOWN = 'dropdown-menu';

  const STYLE_BUTTON_GROUP = 'button-group';

  const STYLE_TOOLBAR = 'toolbar';

  public $style;

  protected $items = array();

  public function __construct($name, array $items = null, $style = null, $label = null, $icon = null)
  {
    parent::__construct($name, $label, $icon);

    if (! empty($items)) {
      foreach ($items as $item) {
        $this->addItem($item);
      }
    }

    if (empty($style)) {
      $style = self::STYLE_LIST;
    }

    $this->style = $style;
  }

  public function addItem(Simplify_MenuItem $item)
  {
    $this->items[] = $item;
    return $this;
  }

  public function addItemAt(Simplify_MenuItem $item, $index)
  {
    array_splice($this->items, $index, 0, array($item));
    return $this;
  }

  public function numItems()
  {
    return count($this->items);
  }

  /**
   *
   * @return Simplify_MenuItem
   */
  public function getItemAt($index)
  {
    return $this->items[$index];
  }

  public function getItemByName($name)
  {
    $index = count($this->items) - 1;

    while ($index >= 0 && $this->items[$index]->name != $name)
      $index --;

    if ($index < 0) {
      throw new Simplify_MenuException("Menu item not found: <b>$name</b>");
    }

    return $this->items[$index];
  }

  public function getItemIndex(Simplify_MenuItem $item)
  {
    $index = count($this->items) - 1;

    while ($index >= 0 && $this->items[$index] === $item)
      $index --;

    if ($index < 0) {
      throw new Simplify_MenuException("Menu item does not exist in menu");
    }

    return $index;
  }

}
