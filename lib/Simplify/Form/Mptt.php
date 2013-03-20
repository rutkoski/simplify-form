<?php

class Simplify_Form_Mptt extends Simplify_Form
{

  const ACTION_SORT = 'sort';

  /**
   *
   * @var string
   */
  public $parent;

  /**
   *
   * @var string
   */
  public $left;

  /**
   *
   * @var string
   */
  public $right;

  /**
   *
   *
   */
  public function execute($action = null)
  {

    $Action = $this->getAction($action);

    $label = $this->getLabel();
    $pk = $this->getPrimaryKey();
    $parent = $this->getParent();
    $left = $this->getLeft();
    $right = $this->getRight();

    /**
     *
     * order
     *
     */

    if ($Action->show(Simplify_Form::ACTION_LIST)) {
      $listAction = s::request()->get('listAction');

      if ($listAction == self::ACTION_SORT) {
        $id = s::request()->get(Simplify_Form::ID);
        $index = s::request()->get('index');

        $this->repository()->moveTo($id, $index);
      }
    }

    /**
     *
     * filter by parent
     *
     */

    $data = $this->repository()->mptt()->query()
      ->select(false)
      ->select("node.{$pk} AS id")
      ->select("CONCAT(REPEAT('&ndash;', (COUNT(parent.{$pk}) - 1)), ' ', node.{$label}) AS label")
      ->select("(COUNT(parent.{$pk}) - 1) AS depth")
      ->execute()
      ->fetchAll();

    $parents = sy_array_to_options($data, Simplify_Form::ID, 'label');

    $filter = new Simplify_Form_FilterSelect($parent, 'Parent');
    $filter->defaultValue = 0;
    $filter->emptyLabel = 'Root';
    $filter->emptyValue = 0;
    $filter->options = $parents;

    $this->addFilter($filter);

    /**
     *
     * edit parent
     *
     */

    if ($Action->show(Simplify_Form::ACTION_EDIT & Simplify_Form::ACTION_CREATE)) {
      $q = $this->repository()->mptt()->query()
        ->select(false)
        ->select("node.{$pk} AS id")
        ->select("CONCAT(REPEAT('&ndash;', (COUNT(parent.{$pk}) - 1)), ' ', node.{$label}) AS label")
        ->select("(COUNT(parent.{$pk}) - 1) AS depth");

      $id = $this->getId();

      if (! empty($id)) {
        $row = $this->repository()->find($id[0]);
        $q->where("node.{$left} NOT BETWEEN {$row[$left]} AND {$row[$right]}");
      }

      $data = $q->execute()->fetchAll();

      $parents = sy_array_to_options($data, Simplify_Form::ID, 'label');

      $parentSelect = new Simplify_Form_ElementSelect($parent, 'Parent');
      $parentSelect->emptyLabel = 'Root';
      $parentSelect->emptyValue = 0;
      $parentSelect->options = $parents;

      $this->addElement($parentSelect, Simplify_Form::ACTION_EDIT | Simplify_Form::ACTION_CREATE, 0);
    }

    return parent::execute($action);
  }

  /**
   *
   * @return string
   */
  public function getParent()
  {
    if (empty($this->parent)) {
      $this->parent = Inflector::singularize($this->getTable()) . '_parent_id';
    }

    return $this->parent;
  }

  /**
   *
   * @return string
   */
  public function getLeft()
  {
    if (empty($this->left)) {
      $this->left = Inflector::singularize($this->getTable()) . '_left';
    }

    return $this->left;
  }

  /**
   *
   * @return string
   */
  public function getRight()
  {
    if (empty($this->right)) {
      $this->right = Inflector::singularize($this->getTable()) . '_right';
    }

    return $this->right;
  }

  public function onCreateItemMenu(Simplify_Menu $menu, Simplify_Form_Action $action, $row)
  {
    if ($action->show(Simplify_Form::ACTION_LIST)) {
      $children = new Simplify_MenuItem('children', 'Children', null, $this->url()->extend(null, array($this->repository()->parent => $row[Simplify_Form::ID])));

      $moveMenu = new Simplify_Menu(null, null, Simplify_Menu::STYLE_DROPDOWN);
      $moveMenu->addItem(new Simplify_MenuItem('move_top', 'First', 'arrow_top', $this->url()->extend(null, array('listAction' => self::ACTION_SORT, 'index' => 'first', Simplify_Form::ID => $row[Simplify_Form::ID]))));
      $moveMenu->addItem(new Simplify_MenuItem('move_up', 'Previous', 'arrow_up', $this->url()->extend(null, array('listAction' => self::ACTION_SORT, 'index' => 'previous', Simplify_Form::ID => $row[Simplify_Form::ID]))));
      $moveMenu->addItem(new Simplify_MenuItem('move_down', 'Next', 'arrow_down', $this->url()->extend(null, array('listAction' => self::ACTION_SORT, 'index' => 'next', Simplify_Form::ID => $row[Simplify_Form::ID]))));
      $moveMenu->addItem(new Simplify_MenuItem('move_bottom', 'Last', 'arrow_bottom', $this->url()->extend(null, array('listAction' => self::ACTION_SORT, 'index' => 'last', Simplify_Form::ID => $row[Simplify_Form::ID]))));

      $moveItem = new Simplify_MenuItem('move', 'Move', null, null, $moveMenu);

      $menu->addItem(new Simplify_Menu('mptt', array($moveItem, $children), Simplify_Menu::STYLE_BUTTON_GROUP));
    }

    parent::onCreateItemMenu($menu, $action, $row);
  }

  /**
   *
   * @return IRepository
   */
  public function repository(IRepository $repository = null)
  {
    if ($repository instanceof IRepository) {
      $this->repository = $repository;
    }

    if (empty($this->repository)) {
      $this->repository = new Simplify_Form_RepositoryMptt($this->getTable(), $this->getPrimaryKey(), $this->getParent(), $this->getLeft(), $this->getRight());
    }

    return $this->repository;
  }

}
