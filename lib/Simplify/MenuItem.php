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
   * @param string $name
   * @param string $label
   * @param string $icon
   * @param Simplify_URL $url
   * @param Simplify_Menu $submenu
   */
  public function __construct($name, $label = null, $icon = null, Simplify_URL $url = null, Simplify_Menu $submenu = null)
  {
    $this->name = $name;
    $this->label = $label;
    $this->icon = $icon;
    $this->url = $url;
    $this->submenu = $submenu;
  }

  /**
   *
   * @param string $name
   * @param string $label
   * @param string $icon
   * @param Simplify_URL $url
   * @param Simplify_Menu $submenu
   * @return Simplify_MenuItem
   */
  public static function factory($name, $label = null, $icon = null, Simplify_URL $url = null, Simplify_Menu $submenu = null)
  {
    return new self($name, $label, $icon, $url, $submenu);
  }

  /**
   *
   * @param string $label
   * @return Simplify_MenuItem
   */
  public function setLabel($label)
  {
    $this->label = $label;
    return $this;
  }

  /**
   *
   * @param string $icon
   * @return Simplify_MenuItem
   */
  public function setIcon($icon)
  {
    $this->icon = $icon;
    return $this;
  }

  /**
   *
   * @param Simplify_URL $url
   * @return Simplify_MenuItem
   */
  public function setUrl(Simplify_URL $url)
  {
    $this->url = $url;
    return $this;
  }

  /**
   *
   * @param Simplify_Menu $submenu
   * @return Simplify_MenuItem
   */
  public function setSubmenu(Simplify_Menu $submenu)
  {
    $this->submenu = $submenu;
    return $this;
  }

}
