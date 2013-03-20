<?php

class Simplify_MenuItem
{

  /**
   *
   * @var string
   */
  public $name;

  /**
   *
   * @var string
   */
  public $label;

  /**
   *
   * @var string
   */
  public $icon;

  /**
   *
   * @var Simplify_URL
   */
  public $url;

  /**
   *
   * @var Simplify_Menu
   */
  public $submenu;

  /**
   *
   * @var boolean
   */
  public $enabled = true;

  /**
   *
   * @return void
   */
  public function __construct($name, $label = null, $icon = null, Simplify_URL $url = null, Simplify_Menu $submenu = null)
  {
    $this->name = $name;
    $this->label = $label;
    $this->icon = $icon;
    $this->url = $url;
    $this->submenu = $submenu;
  }

}
